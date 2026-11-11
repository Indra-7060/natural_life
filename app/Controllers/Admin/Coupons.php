<?php

namespace App\Controllers\Admin;

class Coupons extends CrudController
{
    protected string $module    = 'catalogue';
    protected string $modelName = 'CouponModel';
    protected string $path      = 'admin/coupons';
    protected string $plural    = 'Coupons';
    protected string $singular  = 'Coupon';
    protected string $group     = 'Catalogue';
    protected string $intro     = 'Discount codes customers can enter in the cart. None existed publicly on the original site.';
    protected array $searchable = ['code', 'description'];
    protected array $columns    = ['code' => 'Code', 'type' => 'Type', 'value' => 'Value', 'min_subtotal' => ['Min. subtotal', 'money'], 'used_count' => 'Used', 'expires_at' => ['Expires', 'date'], 'is_active' => ['Active', 'toggle']];
    protected array $fields     = [
        'code'         => ['label' => 'Code', 'type' => 'text', 'rules' => 'required|alpha_dash|max_length[40]|is_unique[coupons.code,id,{id}]', 'col' => 4, 'help' => 'Letters, numbers, dashes. Stored in capitals.'],
        'type'         => ['label' => 'Discount type', 'type' => 'select', 'options' => ['percent' => 'Percentage (%)', 'fixed' => 'Fixed amount (₹)'], 'rules' => 'required|in_list[percent,fixed]', 'col' => 4],
        'value'        => ['label' => 'Discount value', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|greater_than[0]', 'col' => 4],
        'description'  => ['label' => 'Internal note', 'type' => 'text', 'rules' => 'permit_empty|max_length[190]'],
        'min_subtotal' => ['label' => 'Minimum subtotal (₹)', 'type' => 'number', 'step' => '0.01', 'rules' => 'permit_empty|greater_than_equal_to[0]', 'col' => 4],
        'max_discount' => ['label' => 'Maximum discount (₹)', 'type' => 'number', 'step' => '0.01', 'nullable' => true, 'rules' => 'permit_empty|greater_than[0]', 'col' => 4, 'help' => 'Optional cap for percentage coupons.'],
        'usage_limit'  => ['label' => 'Usage limit', 'type' => 'number', 'nullable' => true, 'rules' => 'permit_empty|is_natural_no_zero', 'col' => 4, 'help' => 'Leave blank for unlimited.'],
        'starts_at'    => ['label' => 'Starts', 'type' => 'datetime', 'col' => 4],
        'expires_at'   => ['label' => 'Expires', 'type' => 'datetime', 'col' => 4],
        'is_active'    => ['label' => 'Active', 'type' => 'checkbox', 'col' => 4, 'default' => 1],
    ];

    protected function beforeSave(array $data, ?int $id): array
    {
        $data['code'] = strtoupper((string) $data['code']);
        if ($data['type'] === 'percent') {
            $data['value'] = min(100, (float) $data['value']);
        }

        return $data;
    }
}
