<?php

namespace App\Controllers\Admin;

class Testimonials extends CrudController
{
    protected string $module      = 'content';
    protected string $modelName   = 'TestimonialModel';
    protected string $path        = 'admin/testimonials';
    protected string $plural      = 'Testimonials';
    protected string $singular    = 'Testimonial';
    protected string $group       = 'Website content';
    protected string $orderBy     = 'sort_order';
    protected string $orderDir    = 'ASC';
    protected string $mediaFolder = 'general';
    protected string $intro       = 'Only publish genuine customer feedback.';
    protected array $searchable   = ['name', 'content'];
    protected array $columns      = ['name' => 'Customer', 'title' => 'Headline', 'rating' => 'Rating', 'sort_order' => 'Order', 'is_active' => ['Active', 'toggle']];
    protected array $fields       = [
        'name'       => ['label' => 'Customer name', 'type' => 'text', 'rules' => 'required|max_length[120]', 'col' => 6],
        'meta'       => ['label' => 'Detail line', 'type' => 'text', 'rules' => 'permit_empty|max_length[160]', 'col' => 6, 'help' => 'e.g. "Sugarlif user since 2 years · Amazon Customer"'],
        'title'      => ['label' => 'Headline', 'type' => 'text', 'rules' => 'permit_empty|max_length[190]'],
        'content'    => ['label' => 'Testimonial', 'type' => 'textarea', 'rows' => 6, 'rules' => 'required|max_length[3000]'],
        'rating'     => ['label' => 'Rating', 'type' => 'select', 'options' => [5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star'], 'rules' => 'required|in_list[1,2,3,4,5]', 'col' => 4],
        'sort_order' => ['label' => 'Display order', 'type' => 'number', 'rules' => 'permit_empty|integer', 'col' => 4],
        'is_active'  => ['label' => 'Active', 'type' => 'checkbox', 'col' => 4, 'default' => 1],
    ];
}
