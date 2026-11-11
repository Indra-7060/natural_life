<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/** Roles, permissions and the first administrator (credentials taken from .env). */
class AccessSeeder extends Seeder
{
    public const MODULES = [
        'dashboard' => 'Dashboard',
        'catalogue' => 'Catalogue (products, categories, coupons)',
        'orders'    => 'Orders',
        'customers' => 'Customers',
        'content'   => 'Website content (pages, home, navigation, FAQs, testimonials, team, certifications)',
        'blog'      => 'Blog',
        'inbox'     => 'Enquiries and newsletter',
        'media'     => 'Media library',
        'settings'  => 'Settings, SEO and redirects',
        'users'     => 'Admin users and roles',
    ];

    public function run(): void
    {
        $db  = $this->db;
        $now = date('Y-m-d H:i:s');

        foreach (self::MODULES as $module => $label) {
            if (! $db->table('permissions')->where('module', $module)->countAllResults()) {
                $db->table('permissions')->insert(['module' => $module, 'label' => $label]);
            }
        }
        $perm = array_column($db->table('permissions')->get()->getResultArray(), 'id', 'module');

        $roles = [
            'super-admin'    => ['Super Admin', 'Full access to every module.', array_keys(self::MODULES)],
            'store-manager'  => ['Store Manager', 'Runs the shop: products, orders, customers and enquiries.', ['dashboard', 'catalogue', 'orders', 'customers', 'inbox', 'media']],
            'content-editor' => ['Content Editor', 'Edits pages, blog and media.', ['dashboard', 'content', 'blog', 'media']],
        ];
        foreach ($roles as $slug => [$name, $desc, $modules]) {
            $role = $db->table('roles')->where('slug', $slug)->get()->getRowArray();
            if (! $role) {
                $db->table('roles')->insert(['name' => $name, 'slug' => $slug, 'description' => $desc, 'created_at' => $now, 'updated_at' => $now]);
                $roleId = $db->insertID();
                foreach ($modules as $m) {
                    $db->table('role_permissions')->insert(['role_id' => $roleId, 'permission_id' => $perm[$m]]);
                }
            }
        }

        if ($db->table('admin_users')->countAllResults() === 0) {
            $email    = strtolower(trim((string) env('admin.defaultEmail', 'admin@natural-life.in')));
            $password = (string) env('admin.defaultPassword', '');
            if ($password === '' || $password === 'change-me-before-seeding') {
                CLI::error('Set admin.defaultPassword in .env before seeding. No administrator was created.');

                return;
            }
            $roleId = $db->table('roles')->where('slug', 'super-admin')->get()->getRow()->id;
            $db->table('admin_users')->insert([
                'role_id' => $roleId, 'name' => env('admin.defaultName', 'Site Administrator'), 'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'is_active' => 1, 'must_change_password' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            CLI::write("Administrator created: {$email} (password from .env; you will be asked to change it at first login).", 'green');
        }
    }
}
