<?php

namespace App\Controllers\Admin;

class SeoMeta extends CrudController
{
    protected string $module      = 'settings';
    protected string $modelName   = 'SeoMetaModel';
    protected string $path        = 'admin/seo';
    protected string $plural      = 'SEO';
    protected string $singular    = 'SEO entry';
    protected string $group       = 'Configuration';
    protected string $orderBy     = 'id';
    protected string $orderDir    = 'ASC';
    protected bool $canCreate     = false;
    protected bool $canDelete     = false;
    protected string $mediaFolder = 'general';
    protected string $intro       = 'Titles and descriptions for the home page, shop and blog listings. Products, categories, pages and posts each have their own SEO fields on their edit screens. The XML sitemap (/sitemap.xml) and robots.txt are generated automatically.';
    protected array $columns      = ['label' => 'Page', 'meta_title' => 'Title', 'noindex' => ['Hidden from search', 'toggle']];
    protected array $fields       = [
        'meta_title'       => ['label' => 'SEO title', 'type' => 'text', 'rules' => 'permit_empty|max_length[190]', 'help' => 'Aim for 50–60 characters.'],
        'meta_description' => ['label' => 'SEO description', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[320]', 'help' => 'Aim for 140–160 characters.'],
        'og_image'         => ['label' => 'Social sharing image', 'type' => 'image', 'col' => 6, 'help' => 'Shown when the page is shared. 1200 × 630 px works best.'],
        'noindex'          => ['label' => 'Hide this page from search engines', 'type' => 'checkbox', 'col' => 6],
    ];
}
