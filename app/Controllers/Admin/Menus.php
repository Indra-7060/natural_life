<?php

namespace App\Controllers\Admin;

class Menus extends AdminController
{
    protected string $module = 'content';

    public function index(): string
    {
        return $this->render('admin/menus', [
            'title' => 'Navigation', 'breadcrumbs' => [['Website content', null], ['Navigation', null]],
            'menus' => db_connect()->table('menus m')->select('m.*, (SELECT COUNT(*) FROM menu_items i WHERE i.menu_id = m.id) AS item_count', false)->orderBy('m.id')->get()->getResultArray(),
        ]);
    }
}
