<?php

namespace App\Controllers\Admin;

class Categories extends CrudController
{
    protected string $module      = 'catalogue';
    protected string $modelName   = 'ProductCategoryModel';
    protected string $path        = 'admin/categories';
    protected string $plural      = 'Categories';
    protected string $singular    = 'Category';
    protected string $group       = 'Catalogue';
    protected string $orderBy     = 'sort_order';
    protected string $orderDir    = 'ASC';
    protected string $mediaFolder = 'products';
    protected array $searchable   = ['name'];
    protected array $columns      = ['image' => ['Image', 'image'], 'name' => 'Name', 'slug' => 'URL slug', 'sort_order' => 'Order', 'is_active' => ['Visible', 'toggle']];
    protected array $fields       = [
        'name'             => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|max_length[120]', 'col' => 6],
        'slug'             => ['label' => 'URL slug', 'type' => 'slug', 'from' => 'name', 'rules' => 'permit_empty|max_length[140]|is_unique[product_categories.slug,id,{id}]', 'col' => 6, 'help' => 'Leave blank to create it from the name. Used in /product-category/your-slug.'],
        'description'      => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[1000]'],
        'image'            => ['label' => 'Image', 'type' => 'image', 'col' => 6, 'help' => 'Optional. The first product image is used when empty.'],
        'sort_order'       => ['label' => 'Display order', 'type' => 'number', 'rules' => 'permit_empty|integer', 'col' => 3],
        'is_active'        => ['label' => 'Visible on the website', 'type' => 'checkbox', 'col' => 3, 'default' => 1],
        'meta_title'       => ['label' => 'SEO title', 'type' => 'text', 'rules' => 'permit_empty|max_length[190]', 'section' => 'Search engines'],
        'meta_description' => ['label' => 'SEO description', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[320]'],
    ];

    protected function blockDelete(array $row): ?string
    {
        $n = model('ProductModel')->where('category_id', $row['id'])->countAllResults();

        return $n ? "Move or delete the {$n} product(s) in this category first." : null;
    }
}
