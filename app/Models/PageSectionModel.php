<?php

namespace App\Models;

use CodeIgniter\Model;

class PageSectionModel extends Model
{
    protected $table         = 'page_sections';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['page_id', 'section_key', 'admin_label', 'eyebrow', 'heading', 'body', 'image', 'image_alt', 'cta_label', 'cta_url', 'items', 'is_active', 'sort_order'];
    protected $useTimestamps = true;
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';
}
