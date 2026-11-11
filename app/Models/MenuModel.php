<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table         = 'menus';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['name', 'location'];
    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = '';
}
