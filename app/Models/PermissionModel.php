<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table         = 'permissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['module', 'label'];
    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = '';
}
