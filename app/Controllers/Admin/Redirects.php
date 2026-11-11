<?php

namespace App\Controllers\Admin;

class Redirects extends CrudController
{
    protected string $module    = 'settings';
    protected string $modelName = 'RedirectModel';
    protected string $path      = 'admin/redirects';
    protected string $plural    = 'Redirects';
    protected string $singular  = 'Redirect';
    protected string $group     = 'Configuration';
    protected string $orderBy   = 'from_path';
    protected string $orderDir  = 'ASC';
    protected string $intro     = 'Send visitors and search engines from an old address to a new one. The original website’s changed URLs are already listed.';
    protected array $searchable = ['from_path', 'to_path', 'note'];
    protected array $columns    = ['from_path' => 'Old address', 'to_path' => 'New address', 'status_code' => 'Type', 'hits' => 'Hits', 'is_active' => ['Active', 'toggle']];
    protected array $fields     = [
        'from_path'   => ['label' => 'Old address', 'type' => 'text', 'rules' => 'required|max_length[190]|is_unique[redirects.from_path,id,{id}]', 'col' => 6, 'help' => 'Path only, e.g. /old-page'],
        'to_path'     => ['label' => 'New address', 'type' => 'text', 'rules' => 'required|max_length[255]', 'col' => 6, 'help' => 'Path (e.g. /shop) or a full https:// URL'],
        'status_code' => ['label' => 'Type', 'type' => 'select', 'options' => [301 => '301 — permanent', 302 => '302 — temporary'], 'rules' => 'required|in_list[301,302]', 'col' => 4],
        'note'        => ['label' => 'Note', 'type' => 'text', 'rules' => 'permit_empty|max_length[190]', 'col' => 4],
        'is_active'   => ['label' => 'Active', 'type' => 'checkbox', 'col' => 4, 'default' => 1],
    ];

    protected function beforeSave(array $data, ?int $id): array
    {
        $data['from_path'] = '/' . trim((string) parse_url((string) $data['from_path'], PHP_URL_PATH), '/');

        return $data;
    }
}
