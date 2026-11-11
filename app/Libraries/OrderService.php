<?php

namespace App\Libraries;

use App\Models\OrderModel;
use RuntimeException;

/** Creates orders from carts and applies status / payment transitions in one place. */
class OrderService
{
    /**
     * Creates an order (status "pending", payment "unpaid") from the current cart.
     * Totals are recalculated server-side inside a transaction.
     */
    public function createFromCart(CartService $cart, array $data, ?int $customerId): array
    {
        $lines = array_values(array_filter($cart->lines(), static fn ($l) => $l['available']));
        if ($lines === []) {
            throw new RuntimeException('Your cart is empty.');
        }
        $totals = $cart->totals();
        $db     = db_connect();
        $orders = model(OrderModel::class);

        $db->transException(true)->transStart();

        $orderId = $orders->insert([
            'order_number'      => $this->newOrderNumber(),
            'access_token'      => bin2hex(random_bytes(20)),
            'customer_id'       => $customerId,
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'status'            => 'pending',
            'payment_status'    => 'unpaid',
            'payment_method'    => 'ccavenue',
            'subtotal'          => $totals['subtotal'],
            'discount_total'    => $totals['discount'],
            'shipping_total'    => $totals['shipping'],
            'grand_total'       => $totals['total'],
            'coupon_code'       => $totals['coupon_code'],
            'shipping_method'   => $totals['shipping_label'],
            'billing_name'      => $data['billing_name'],
            'billing_line1'     => $data['billing_line1'],
            'billing_line2'     => $data['billing_line2'] ?? null,
            'billing_city'      => $data['billing_city'],
            'billing_state'     => $data['billing_state'],
            'billing_postcode'  => $data['billing_postcode'],
            'billing_country'   => 'India',
            'shipping_name'     => $data['shipping_name'],
            'shipping_phone'    => $data['shipping_phone'],
            'shipping_line1'    => $data['shipping_line1'],
            'shipping_line2'    => $data['shipping_line2'] ?? null,
            'shipping_city'     => $data['shipping_city'],
            'shipping_state'    => $data['shipping_state'],
            'shipping_postcode' => $data['shipping_postcode'],
            'shipping_country'  => 'India',
            'customer_note'     => $data['customer_note'] ?? null,
        ], true);

        foreach ($lines as $l) {
            $db->table('order_items')->insert([
                'order_id'        => $orderId,
                'product_id'      => $l['product_id'],
                'variation_id'    => $l['variation_id'],
                'product_name'    => $l['name'],
                'variation_label' => $l['variation_label'],
                'sku'             => $l['sku'],
                'image'           => $l['image'],
                'unit_price'      => $l['unit_price'],
                'quantity'        => $l['quantity'],
                'line_total'      => $l['line_total'],
            ]);
        }
        $this->log($orderId, 'pending', 'Order placed. Awaiting payment.', 'Customer');
        if ($totals['coupon_code']) {
            $db->table('coupons')->where('code', $totals['coupon_code'])->set('used_count', 'used_count + 1', false)->update();
        }

        $db->transComplete();

        return $orders->find($orderId);
    }

    /**
     * Marks an order paid. Called ONLY with a verified gateway response, or by an
     * administrator recording an offline payment.
     */
    public function markPaid(array $order, string $by, string $note = 'Payment received.'): void
    {
        if ($order['payment_status'] === 'paid') {
            return;
        }
        $db = db_connect();
        $db->transStart();
        model(OrderModel::class)->update($order['id'], [
            'payment_status' => 'paid',
            'paid_at'        => date('Y-m-d H:i:s'),
            'status'         => $order['status'] === 'pending' ? 'processing' : $order['status'],
        ]);
        $this->log($order['id'], 'processing', $note, $by);
        $this->adjustStock($order['id'], -1, 'Order ' . $order['order_number']);
        $db->transComplete();
    }

    public function markPaymentFailed(array $order, string $note): void
    {
        if ($order['payment_status'] === 'paid') {
            return;
        }
        model(OrderModel::class)->update($order['id'], ['payment_status' => 'failed']);
        $this->log($order['id'], 'payment failed', $note, 'Payment gateway');
    }

    public function setStatus(array $order, string $status, string $by, ?string $note = null): void
    {
        if (! isset(OrderModel::STATUSES[$status]) || $status === $order['status']) {
            return;
        }
        $update = ['status' => $status];
        if ($status === 'shipped' && ! $order['shipped_at']) {
            $update['shipped_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'completed' && ! $order['completed_at']) {
            $update['completed_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'refunded') {
            $update['payment_status'] = 'refunded';
        }
        model(OrderModel::class)->update($order['id'], $update);
        $this->log($order['id'], $status, $note ?: 'Status changed to ' . OrderModel::STATUSES[$status] . '.', $by);

        // Return tracked stock when a paid order is cancelled or refunded.
        if (in_array($status, ['cancelled', 'refunded'], true) && $order['payment_status'] === 'paid'
            && ! in_array($order['status'], ['cancelled', 'refunded'], true)) {
            $this->adjustStock($order['id'], +1, 'Reversal of order ' . $order['order_number']);
        }
    }

    public function log(int $orderId, string $status, ?string $note, string $by): void
    {
        db_connect()->table('order_status_history')->insert([
            'order_id' => $orderId, 'status' => $status, 'note' => $note, 'changed_by' => $by, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Adjusts stock for products that track quantities; $direction -1 = sold, +1 = returned. */
    private function adjustStock(int $orderId, int $direction, string $reason): void
    {
        $db = db_connect();
        foreach ($db->table('order_items')->where('order_id', $orderId)->get()->getResultArray() as $item) {
            if (! $item['product_id']) {
                continue;
            }
            $product = $db->table('products')->where('id', $item['product_id'])->get()->getRowArray();
            if (! $product || ! $product['manage_stock']) {
                continue;
            }
            $change = $direction * (int) $item['quantity'];
            if ($item['variation_id']) {
                $v = $db->table('product_variations')->where('id', $item['variation_id'])->get()->getRowArray();
                if ($v && $v['stock_qty'] !== null) {
                    $qty = max(0, (int) $v['stock_qty'] + $change);
                    $db->table('product_variations')->where('id', $v['id'])->update(['stock_qty' => $qty, 'stock_status' => $qty > 0 ? 'in_stock' : 'out_of_stock']);
                }
            } elseif ($product['stock_qty'] !== null) {
                $qty = max(0, (int) $product['stock_qty'] + $change);
                $db->table('products')->where('id', $product['id'])->update(['stock_qty' => $qty, 'stock_status' => $qty > 0 ? 'in_stock' : 'out_of_stock']);
            }
            $db->table('stock_movements')->insert([
                'product_id' => $item['product_id'], 'variation_id' => $item['variation_id'], 'change_qty' => $change,
                'reason' => $reason, 'reference' => (string) $orderId, 'created_at' => date('Y-m-d H:i:s'),
            ]);
            model('ProductModel')->syncDerived((int) $item['product_id']);
        }
    }

    private function newOrderNumber(): string
    {
        $orders = model(OrderModel::class);
        do {
            $number = 'SL' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while ($orders->where('order_number', $number)->countAllResults() > 0);

        return $number;
    }
}
