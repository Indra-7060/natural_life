<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariationModel extends Model
{
    protected $table         = 'product_variations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['product_id', 'label', 'sku', 'regular_price', 'sale_price', 'stock_status', 'stock_qty', 'is_active', 'sort_order', 'source_id'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
