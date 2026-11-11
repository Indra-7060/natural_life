<?php

namespace App\Controllers\Admin;

class Team extends CrudController
{
    protected string $module      = 'content';
    protected string $modelName   = 'TeamMemberModel';
    protected string $path        = 'admin/team';
    protected string $plural      = 'Team members';
    protected string $singular    = 'Team member';
    protected string $group       = 'Website content';
    protected string $orderBy     = 'sort_order';
    protected string $orderDir    = 'ASC';
    protected string $mediaFolder = 'team';
    protected array $searchable   = ['name', 'designation'];
    protected array $columns      = ['image' => ['Photo', 'image'], 'name' => 'Name', 'designation' => 'Designation', 'group' => 'Group', 'sort_order' => 'Order', 'is_active' => ['Active', 'toggle']];
    protected array $fields       = [
        'name'        => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|max_length[120]', 'col' => 6],
        'designation' => ['label' => 'Designation', 'type' => 'text', 'rules' => 'permit_empty|max_length[160]', 'col' => 6],
        'group'       => ['label' => 'Group heading', 'type' => 'text', 'rules' => 'required|max_length[60]', 'col' => 6, 'default' => 'Key People', 'help' => 'People with the same group heading are shown together. The first group is shown largest.'],
        'image'       => ['label' => 'Photo', 'type' => 'image', 'col' => 6, 'help' => 'Portrait, at least 500 px wide.'],
        'bio'         => ['label' => 'Short bio', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[1000]'],
        'sort_order'  => ['label' => 'Display order', 'type' => 'number', 'rules' => 'permit_empty|integer', 'col' => 4],
        'is_active'   => ['label' => 'Active', 'type' => 'checkbox', 'col' => 4, 'default' => 1],
    ];
}
