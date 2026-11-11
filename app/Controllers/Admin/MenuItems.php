<?php

namespace App\Controllers\Admin;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Model;

/** Items of one navigation menu (?menu=ID). Reached from Admin > Navigation. */
class MenuItems extends CrudController
{
    protected string $module    = 'content';
    protected string $modelName = 'MenuItemModel';
    protected string $path      = 'admin/menu-items';
    protected string $plural    = 'Menu items';
    protected string $singular  = 'Menu item';
    protected string $group     = 'Navigation';
    protected string $orderBy   = 'COALESCE(menu_items.parent_id, menu_items.id), menu_items.parent_id IS NOT NULL, menu_items.sort_order';
    protected string $orderDir  = '';
    protected int $perPage      = 100;
    protected array $columns    = ['label_tree' => 'Label', 'url' => 'Link', 'group_label' => 'Column heading', 'sort_order' => 'Order', 'is_active' => ['Active', 'toggle']];

    private function menu(): array
    {
        $id   = (int) ($this->request->getGet('menu') ?: $this->request->getPost('menu_id'));
        $menu = model('MenuModel')->find($id);
        if (! $menu) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $menu;
    }

    protected function contextQuery(): string
    {
        return '?menu=' . $this->menu()['id'];
    }

    public function index(): string
    {
        $menu         = $this->menu();
        $this->plural = $menu['name'];
        $this->intro  = 'Items with a parent appear in that item’s drop-down panel. Items sharing a column heading are grouped together in the panel.';

        return parent::index();
    }

    protected function listQuery(Model $model): void
    {
        $model->select('menu_items.*, IF(menu_items.parent_id IS NULL, menu_items.label, CONCAT("— ", menu_items.label)) AS label_tree', false)
            ->where('menu_items.menu_id', $this->menu()['id']);
    }

    protected function fields(?array $row = null): array
    {
        $menu    = $this->menu();
        $parents = model('MenuItemModel')->where(['menu_id' => $menu['id'], 'parent_id' => null])->orderBy('sort_order')->findAll();
        $options = ['' => '— Top level —'];
        foreach ($parents as $p) {
            if (! $row || (int) $p['id'] !== (int) $row['id']) {
                $options[$p['id']] = $p['label'];
            }
        }

        return [
            'label'        => ['label' => 'Label', 'type' => 'text', 'rules' => 'required|max_length[120]', 'col' => 6],
            'url'          => ['label' => 'Link', 'type' => 'text', 'rules' => 'required|max_length[255]', 'col' => 6, 'help' => 'A path on this site (e.g. /shop) or a full https:// address.'],
            'parent_id'    => ['label' => 'Parent item', 'type' => 'select', 'options' => $options, 'rules' => 'permit_empty|is_natural_no_zero', 'col' => 6],
            'group_label'  => ['label' => 'Column heading in the drop-down', 'type' => 'text', 'rules' => 'permit_empty|max_length[80]', 'col' => 6],
            'description'  => ['label' => 'Short description', 'type' => 'text', 'rules' => 'permit_empty|max_length[160]', 'col' => 6],
            'sort_order'   => ['label' => 'Display order', 'type' => 'number', 'rules' => 'permit_empty|integer', 'col' => 2],
            'open_new_tab' => ['label' => 'Open in a new tab', 'type' => 'checkbox', 'col' => 2],
            'is_active'    => ['label' => 'Active', 'type' => 'checkbox', 'col' => 2, 'default' => 1],
        ];
    }

    protected function beforeSave(array $data, ?int $id): array
    {
        $data['menu_id'] = $this->menu()['id'];

        return $data;
    }
}
