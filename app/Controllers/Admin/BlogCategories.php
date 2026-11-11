<?php

namespace App\Controllers\Admin;

class BlogCategories extends CrudController
{
    protected string $module    = 'blog';
    protected string $modelName = 'BlogCategoryModel';
    protected string $path      = 'admin/blog-categories';
    protected string $plural    = 'Blog categories';
    protected string $singular  = 'Blog category';
    protected string $group     = 'Blog';
    protected string $orderBy   = 'name';
    protected string $orderDir  = 'ASC';
    protected array $columns    = ['name' => 'Name', 'slug' => 'URL slug'];
    protected array $fields     = [
        'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|max_length[120]', 'col' => 6],
        'slug' => ['label' => 'URL slug', 'type' => 'slug', 'from' => 'name', 'rules' => 'permit_empty|max_length[140]|is_unique[blog_categories.slug,id,{id}]', 'col' => 6],
    ];
}
