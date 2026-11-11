<?php

namespace App\Controllers\Admin;

use CodeIgniter\Model;

class Posts extends CrudController
{
    protected string $module      = 'blog';
    protected string $modelName   = 'BlogPostModel';
    protected string $path        = 'admin/posts';
    protected string $plural      = 'Blog posts';
    protected string $singular    = 'Post';
    protected string $group       = 'Blog';
    protected string $orderBy     = 'published_at';
    protected string $mediaFolder = 'blog';
    protected array $searchable   = ['title', 'excerpt'];
    protected array $columns      = ['image' => ['Image', 'image'], 'title' => 'Title', 'published_at' => ['Published', 'date'], 'is_published' => ['Live', 'toggle']];

    protected function fields(?array $row = null): array
    {
        $cats = array_column(model('BlogCategoryModel')->orderBy('name')->findAll(), 'name', 'id');

        return [
            'title'            => ['label' => 'Title', 'type' => 'text', 'rules' => 'required|max_length[220]'],
            'slug'             => ['label' => 'URL slug', 'type' => 'slug', 'from' => 'title', 'rules' => 'permit_empty|max_length[240]|is_unique[blog_posts.slug,id,{id}]', 'col' => 6, 'help' => 'Leave blank to create it from the title. Changing it breaks existing links unless you add a redirect.'],
            'category_id'      => ['label' => 'Category', 'type' => 'select', 'options' => ['' => '— None —'] + $cats, 'rules' => 'permit_empty|is_natural_no_zero', 'col' => 3],
            'author_name'      => ['label' => 'Author', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'col' => 3],
            'excerpt'          => ['label' => 'Summary', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[600]', 'help' => 'Shown on the blog listing and the homepage.'],
            'content'          => ['label' => 'Article', 'type' => 'richtext', 'rules' => 'required'],
            'image'            => ['label' => 'Cover image', 'type' => 'image', 'col' => 6],
            'published_at'     => ['label' => 'Publish date', 'type' => 'datetime', 'col' => 3],
            'is_published'     => ['label' => 'Published', 'type' => 'checkbox', 'col' => 3, 'default' => 1],
            'meta_title'       => ['label' => 'SEO title', 'type' => 'text', 'rules' => 'permit_empty|max_length[190]', 'section' => 'Search engines'],
            'meta_description' => ['label' => 'SEO description', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[320]'],
        ];
    }

    protected function beforeSave(array $data, ?int $id): array
    {
        $data['published_at'] = $data['published_at'] ?: date('Y-m-d H:i:s');

        return $data;
    }
}
