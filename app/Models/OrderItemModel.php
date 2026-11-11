<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table         = 'order_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['order_id', 'product_id', 'variation_id', 'product_name', 'variation_label', 'sku', 'image', 'unit_price', 'quantity', 'line_total'];
    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = '';
}
