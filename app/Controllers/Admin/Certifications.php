<?php

namespace App\Controllers\Admin;

class Certifications extends CrudController
{
    protected string $module      = 'content';
    protected string $modelName   = 'CertificationModel';
    protected string $path        = 'admin/certifications';
    protected string $plural      = 'Certifications';
    protected string $singular    = 'Certification';
    protected string $group       = 'Website content';
    protected string $orderBy     = 'sort_order';
    protected string $orderDir    = 'ASC';
    protected string $mediaFolder = 'certifications';
    protected string $intro       = 'Listed on the Certifications page, the home page and Our Profile. Add a certificate image or document link when available.';
    protected array $columns      = ['image' => ['Certificate', 'image'], 'name' => 'Name', 'issuer_location' => 'Location', 'sort_order' => 'Order', 'is_active' => ['Active', 'toggle']];
    protected array $fields       = [
        'name'            => ['label' => 'Certifying body / standard', 'type' => 'text', 'rules' => 'required|max_length[190]', 'col' => 6],
        'issuer_location' => ['label' => 'Location or scope', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'col' => 6],
        'description'     => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'permit_empty|max_length[2000]'],
        'image'           => ['label' => 'Certificate image', 'type' => 'image', 'col' => 6],
        'document'        => ['label' => 'Document link (PDF URL)', 'type' => 'url', 'rules' => 'permit_empty|valid_url_strict|max_length[255]', 'col' => 6],
        'sort_order'      => ['label' => 'Display order', 'type' => 'number', 'rules' => 'permit_empty|integer', 'col' => 4],
        'is_active'       => ['label' => 'Active', 'type' => 'checkbox', 'col' => 4, 'default' => 1],
    ];
}
