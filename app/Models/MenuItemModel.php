<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table         = 'menu_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['menu_id', 'parent_id', 'label', 'url', 'group_label', 'description', 'open_new_tab', 'is_active', 'sort_order'];
    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = '';
}
