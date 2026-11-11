<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductAttributeModel extends Model
{
    protected $table         = 'product_attributes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['product_id', 'name', 'value', 'sort_order'];
    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = '';
}
