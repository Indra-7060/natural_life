<?php

namespace App\Controllers\Admin;

class Banners extends CrudController
{
    protected string $module      = 'content';
    protected string $modelName   = 'BannerModel';
    protected string $path        = 'admin/banners';
    protected string $plural      = 'Home banners';
    protected string $singular    = 'Banner';
    protected string $group       = 'Website content';
    protected string $orderBy     = 'sort_order';
    protected string $orderDir    = 'ASC';
    protected string $mediaFolder = 'banners';
    protected string $intro       = 'Slides shown in the homepage hero. They rotate in display order.';
    protected array $columns      = ['image' => ['Image', 'image'], 'heading' => 'Headline', 'cta_url' => 'Button link', 'sort_order' => 'Order', 'is_active' => ['Active', 'toggle']];
    protected array $fields       = [
        'eyebrow'    => ['label' => 'Small label above the headline', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'col' => 6],
        'heading'    => ['label' => 'Headline', 'type' => 'text', 'rules' => 'required|max_length[255]'],
        'subheading' => ['label' => 'Supporting text', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[500]'],
        'image'      => ['label' => 'Image', 'type' => 'image', 'col' => 6, 'help' => 'Landscape image, at least 1600 px wide. The right-hand side stays visible.'],
        'image_alt'  => ['label' => 'Image description (alt text)', 'type' => 'text', 'rules' => 'permit_empty|max_length[255]', 'col' => 6],
        'cta_label'  => ['label' => 'Button label', 'type' => 'text', 'rules' => 'permit_empty|max_length[80]', 'col' => 4],
        'cta_url'    => ['label' => 'Button link', 'type' => 'text', 'rules' => 'permit_empty|max_length[255]', 'col' => 4, 'help' => 'e.g. /shop or /product/your-product'],
        'sort_order' => ['label' => 'Display order', 'type' => 'number', 'rules' => 'permit_empty|integer', 'col' => 2],
        'is_active'  => ['label' => 'Active', 'type' => 'checkbox', 'col' => 2, 'default' => 1],
    ];
}
