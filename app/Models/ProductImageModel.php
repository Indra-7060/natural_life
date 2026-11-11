<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImageModel extends Model
{
    protected $table         = 'product_images';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['product_id', 'path', 'alt_text', 'is_primary', 'sort_order'];
    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = '';
}
