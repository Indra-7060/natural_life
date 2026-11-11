<?php

namespace App\Models;

use CodeIgniter\Model;

class SeoMetaModel extends Model
{
    protected $table         = 'seo_meta';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['route_key', 'label', 'meta_title', 'meta_description', 'og_image', 'noindex'];
    protected $useTimestamps = true;
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';
}
