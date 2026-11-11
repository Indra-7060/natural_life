<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table         = 'orders';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'order_number', 'access_token', 'customer_id', 'email', 'phone', 'status', 'payment_status', 'payment_method',
        'subtotal', 'discount_total', 'shipping_total', 'grand_total', 'coupon_code', 'shipping_method',
        'billing_name', 'billing_line1', 'billing_line2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_country',
        'shipping_name', 'shipping_phone', 'shipping_line1', 'shipping_line2', 'shipping_city', 'shipping_state',
        'shipping_postcode', 'shipping_country', 'customer_note', 'admin_note', 'courier_name', 'tracking_number',
        'tracking_url', 'paid_at', 'shipped_at', 'completed_at',
    ];
    protected $useTimestamps = true;

    public const STATUSES = [
        'pending'    => 'Pending payment',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
        'refunded'   => 'Refunded',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid'   => 'Unpaid',
        'paid'     => 'Paid',
        'failed'   => 'Failed',
        'refunded' => 'Refunded',
    ];

    public function findByNumber(string $number): ?array
    {
        return $this->where('order_number', $number)->first();
    }

    public function withDetails(array $order): array
    {
        $order['items']    = $this->db->table('order_items')->where('order_id', $order['id'])->get()->getResultArray();
        $order['history']  = $this->db->table('order_status_history')->where('order_id', $order['id'])->orderBy('id', 'DESC')->get()->getResultArray();
        $order['payments'] = $this->db->table('payments')->where('order_id', $order['id'])->orderBy('id', 'DESC')->get()->getResultArray();

        return $order;
    }
}
