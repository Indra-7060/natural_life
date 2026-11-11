<?php

namespace App\Libraries;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\CouponModel;
use RuntimeException;

/**
 * Database-backed shopping cart. Guests are identified by a random token kept in the
 * session; when a customer signs in the guest cart is merged into their saved cart.
 * Prices are always re-read from the catalogue, never trusted from the browser.
 */
class CartService
{
    private ?array $cart   = null;
    private ?array $lines  = null;
    private ?array $totals = null;

    public const MAX_QTY = 20;

    public function cart(bool $create = false): ?array
    {
        if ($this->cart !== null) {
            return $this->cart;
        }
        $model      = model(CartModel::class);
        $customerId = session('customer_id');
        $token      = session('cart_token');

        $cart = null;
        if ($customerId) {
            $cart = $model->where('customer_id', $customerId)->orderBy('id', 'DESC')->first();
        }
        if (! $cart && $token) {
            $cart = $model->where('token', $token)->first();
            if ($cart && $customerId && ! $cart['customer_id']) {
                $model->update($cart['id'], ['customer_id' => $customerId]);
                $cart['customer_id'] = $customerId;
            }
        }
        if (! $cart && $create) {
            $token = bin2hex(random_bytes(20));
            $id    = $model->insert(['token' => $token, 'customer_id' => $customerId ?: null], true);
            $cart  = $model->find($id);
        }
        if ($cart) {
            session()->set('cart_token', $cart['token']);
        }

        return $this->cart = $cart;
    }

    /** Cart lines joined with live catalogue data. */
    public function lines(): array
    {
        if ($this->lines !== null) {
            return $this->lines;
        }
        $cart = $this->cart();
        if (! $cart) {
            return $this->lines = [];
        }
        $rows = db_connect()->table('cart_items ci')
            ->select('ci.id, ci.quantity, ci.product_id, ci.variation_id, p.name, p.slug, p.type, p.sku AS product_sku,
                p.regular_price, p.sale_price, p.stock_status AS product_stock, p.is_visible, p.deleted_at, p.manage_stock, p.stock_qty,
                v.label AS variation_label, v.sku AS variation_sku, v.regular_price AS v_regular, v.sale_price AS v_sale,
                v.stock_status AS v_stock, v.is_active AS v_active,
                (SELECT path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) AS image', false)
            ->join('products p', 'p.id = ci.product_id')
            ->join('product_variations v', 'v.id = ci.variation_id', 'left')
            ->where('ci.cart_id', $cart['id'])->orderBy('ci.id')->get()->getResultArray();

        $lines = [];
        foreach ($rows as $r) {
            $isVar   = $r['variation_id'] !== null;
            $regular = (float) ($isVar ? $r['v_regular'] : $r['regular_price']);
            $sale    = $isVar ? $r['v_sale'] : $r['sale_price'];
            $unit    = ($sale !== null && (float) $sale < $regular) ? (float) $sale : $regular;
            $inStock = $r['is_visible'] && $r['deleted_at'] === null
                && ($isVar ? ($r['v_active'] && $r['v_stock'] === 'in_stock') : ($r['type'] === 'simple' && $r['product_stock'] === 'in_stock'));
            if (! $isVar && $r['manage_stock'] && $r['stock_qty'] !== null && (int) $r['stock_qty'] < (int) $r['quantity']) {
                $inStock = (int) $r['stock_qty'] > 0 ? $inStock : false;
            }
            $lines[] = [
                'id'              => (int) $r['id'],
                'product_id'      => (int) $r['product_id'],
                'variation_id'    => $isVar ? (int) $r['variation_id'] : null,
                'name'            => $r['name'],
                'slug'            => $r['slug'],
                'variation_label' => $r['variation_label'],
                'sku'             => $isVar ? $r['variation_sku'] : $r['product_sku'],
                'image'           => $r['image'],
                'regular_price'   => $regular,
                'unit_price'      => $unit,
                'quantity'        => (int) $r['quantity'],
                'line_total'      => round($unit * (int) $r['quantity'], 2),
                'available'       => (bool) $inStock,
            ];
        }

        return $this->lines = $lines;
    }

    public function count(): int
    {
        return array_sum(array_column($this->lines(), 'quantity'));
    }

    public function isEmpty(): bool
    {
        return $this->lines() === [];
    }

    public function hasUnavailable(): bool
    {
        return (bool) array_filter($this->lines(), static fn ($l) => ! $l['available']);
    }

    /** @throws RuntimeException with a customer-facing message */
    public function add(int $productId, ?int $variationId, int $qty = 1): void
    {
        $qty     = max(1, min(self::MAX_QTY, $qty));
        $db      = db_connect();
        $product = $db->table('products')->where(['id' => $productId, 'is_visible' => 1, 'deleted_at' => null])->get()->getRowArray();
        if (! $product) {
            throw new RuntimeException('This product is no longer available.');
        }
        if ($product['type'] === 'variable') {
            if (! $variationId) {
                throw new RuntimeException('Please choose a pack size first.');
            }
            $variation = $db->table('product_variations')->where(['id' => $variationId, 'product_id' => $productId, 'is_active' => 1])->get()->getRowArray();
            if (! $variation) {
                throw new RuntimeException('That pack size is not available.');
            }
            if ($variation['stock_status'] !== 'in_stock') {
                throw new RuntimeException('That pack size is currently out of stock.');
            }
        } else {
            $variationId = null;
            if ($product['stock_status'] !== 'in_stock') {
                throw new RuntimeException($product['name'] . ' is currently out of stock.');
            }
        }

        $cart  = $this->cart(true);
        $items = model(CartItemModel::class);
        $where = ['cart_id' => $cart['id'], 'product_id' => $productId, 'variation_id' => $variationId];
        $line  = $items->where($where)->first();
        if ($line) {
            $items->update($line['id'], ['quantity' => min(self::MAX_QTY, $line['quantity'] + $qty)]);
        } else {
            $items->insert($where + ['quantity' => $qty]);
        }
        $this->touch();
    }

    public function update(int $itemId, int $qty): void
    {
        $cart = $this->cart();
        if (! $cart) {
            return;
        }
        $items = model(CartItemModel::class);
        $line  = $items->where(['id' => $itemId, 'cart_id' => $cart['id']])->first();
        if (! $line) {
            return;
        }
        if ($qty < 1) {
            $items->delete($itemId);
        } else {
            $items->update($itemId, ['quantity' => min(self::MAX_QTY, $qty)]);
        }
        $this->touch();
    }

    public function remove(int $itemId): void
    {
        $this->update($itemId, 0);
    }

    public function clear(): void
    {
        $cart = $this->cart();
        if ($cart) {
            model(CartItemModel::class)->where('cart_id', $cart['id'])->delete();
            model(CartModel::class)->update($cart['id'], ['coupon_code' => null]);
        }
        $this->touch();
    }

    /** Merges the guest cart into the customer's cart after sign-in. */
    public function attachCustomer(int $customerId): void
    {
        $model = model(CartModel::class);
        $token = session('cart_token');
        $guest = $token ? $model->where('token', $token)->first() : null;
        $saved = $model->where('customer_id', $customerId)->orderBy('id', 'DESC')->first();

        if ($guest && $saved && $guest['id'] !== $saved['id']) {
            $items = model(CartItemModel::class);
            foreach ($items->where('cart_id', $guest['id'])->findAll() as $g) {
                $match = $items->where(['cart_id' => $saved['id'], 'product_id' => $g['product_id'], 'variation_id' => $g['variation_id']])->first();
                if ($match) {
                    $items->update($match['id'], ['quantity' => min(self::MAX_QTY, $match['quantity'] + $g['quantity'])]);
                } else {
                    $items->update($g['id'], ['cart_id' => $saved['id']]);
                }
            }
            $model->delete($guest['id']);
            session()->set('cart_token', $saved['token']);
        } elseif ($guest && ! $guest['customer_id']) {
            $model->update($guest['id'], ['customer_id' => $customerId]);
        } elseif ($saved) {
            session()->set('cart_token', $saved['token']);
        }
        $this->touch();
    }

    /** @throws RuntimeException */
    public function applyCoupon(string $code): void
    {
        $code   = strtoupper(trim($code));
        $coupon = $this->validCoupon($code, $this->subtotal());
        if (! $coupon) {
            throw new RuntimeException('That coupon code is not valid for this order.');
        }
        $cart = $this->cart(true);
        model(CartModel::class)->update($cart['id'], ['coupon_code' => $code]);
        $this->touch();
    }

    public function removeCoupon(): void
    {
        if ($cart = $this->cart()) {
            model(CartModel::class)->update($cart['id'], ['coupon_code' => null]);
        }
        $this->touch();
    }

    public function subtotal(): float
    {
        return round(array_sum(array_column(array_filter($this->lines(), static fn ($l) => $l['available']), 'line_total')), 2);
    }

    /** subtotal, discount, shipping, total + free-shipping progress. */
    public function totals(): array
    {
        if ($this->totals !== null) {
            return $this->totals;
        }
        $subtotal = $this->subtotal();
        $discount = 0.0;
        $code     = $this->cart()['coupon_code'] ?? null;
        $coupon   = $code ? $this->validCoupon($code, $subtotal) : null;
        if ($coupon) {
            $discount = $coupon['type'] === 'percent' ? round($subtotal * (float) $coupon['value'] / 100, 2) : (float) $coupon['value'];
            if ($coupon['max_discount'] !== null) {
                $discount = min($discount, (float) $coupon['max_discount']);
            }
            $discount = min($discount, $subtotal);
        } else {
            $code = null;
        }

        $shipping = (new ShippingCalculator())->quote($subtotal);

        return $this->totals = [
            'subtotal'        => $subtotal,
            'discount'        => $discount,
            'coupon_code'     => $code,
            'shipping'        => $shipping['amount'],
            'shipping_label'  => $shipping['label'],
            'free_threshold'  => $shipping['threshold'],
            'free_remaining'  => $shipping['remaining'],
            'total'           => round(max(0, $subtotal - $discount) + $shipping['amount'], 2),
            'count'           => $this->count(),
        ];
    }

    private function validCoupon(string $code, float $subtotal): ?array
    {
        $c = model(CouponModel::class)->where(['code' => $code, 'is_active' => 1])->first();
        if (! $c) {
            return null;
        }
        $now = date('Y-m-d H:i:s');
        if (($c['starts_at'] && $c['starts_at'] > $now) || ($c['expires_at'] && $c['expires_at'] < $now)) {
            return null;
        }
        if ($c['usage_limit'] !== null && (int) $c['used_count'] >= (int) $c['usage_limit']) {
            return null;
        }
        if ($subtotal < (float) $c['min_subtotal']) {
            return null;
        }

        return $c;
    }

    private function touch(): void
    {
        $this->lines = $this->totals = null;
        if ($this->cart) {
            db_connect()->table('carts')->where('id', $this->cart['id'])->update(['updated_at' => date('Y-m-d H:i:s')]);
        }
        $this->cart = null;
    }
}
