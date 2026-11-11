<?php

namespace App\Controllers\Admin;

class Faqs extends CrudController
{
    protected string $module    = 'content';
    protected string $modelName = 'FaqModel';
    protected string $path      = 'admin/faqs';
    protected string $plural    = 'FAQs';
    protected string $singular  = 'FAQ';
    protected string $group     = 'Website content';
    protected string $orderBy   = 'sort_order';
    protected string $orderDir  = 'ASC';
    protected array $searchable = ['question', 'answer'];
    protected array $columns    = ['question' => 'Question', 'sort_order' => 'Order', 'show_on_home' => ['On home page', 'toggle'], 'is_active' => ['Active', 'toggle']];
    protected array $fields     = [
        'question'     => ['label' => 'Question', 'type' => 'text', 'rules' => 'required|max_length[255]'],
        'answer'       => ['label' => 'Answer', 'type' => 'richtext', 'rules' => 'required'],
        'sort_order'   => ['label' => 'Display order', 'type' => 'number', 'rules' => 'permit_empty|integer', 'col' => 4],
        'show_on_home' => ['label' => 'Show on the home page', 'type' => 'checkbox', 'col' => 4],
        'is_active'    => ['label' => 'Active', 'type' => 'checkbox', 'col' => 4, 'default' => 1],
    ];
}
