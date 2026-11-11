<?php

namespace App\Controllers\Admin;

use CodeIgniter\Model;

class Users extends CrudController
{
    protected string $module    = 'users';
    protected string $modelName = 'AdminUserModel';
    protected string $path      = 'admin/users';
    protected string $plural    = 'Admin users';
    protected string $singular  = 'Admin user';
    protected string $group     = 'Configuration';
    protected string $orderBy   = 'admin_users.name';
    protected string $orderDir  = 'ASC';
    protected string $intro     = 'Super Admin: everything. Store Manager: catalogue, orders, customers, enquiries, media. Content Editor: pages, blog, media.';
    protected array $searchable = ['admin_users.name', 'admin_users.email'];
    protected array $columns    = ['name' => 'Name', 'email' => 'Email', 'role_name' => 'Role', 'last_login_at' => ['Last sign-in', 'date'], 'is_active' => ['Active', 'toggle']];

    protected function listQuery(Model $model): void
    {
        $model->select('admin_users.*, roles.name AS role_name')->join('roles', 'roles.id = admin_users.role_id');
    }

    protected function fields(?array $row = null): array
    {
        return [
            'name'      => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|max_length[120]', 'col' => 6],
            'email'     => ['label' => 'Email (used to sign in)', 'type' => 'email', 'rules' => 'required|valid_email|max_length[190]|is_unique[admin_users.email,id,{id}]', 'col' => 6],
            'role_id'   => ['label' => 'Role', 'type' => 'select', 'options' => array_column(model('RoleModel')->orderBy('id')->findAll(), 'name', 'id'), 'rules' => 'required|is_natural_no_zero', 'col' => 6],
            'password'  => ['label' => $row ? 'New password' : 'Password', 'type' => 'password', 'rules' => 'required|min_length[10]|max_length[72]', 'col' => 6, 'help' => ($row ? 'Leave blank to keep the current password. ' : '') . 'At least 10 characters. The user is asked to change it at first sign-in.'],
            'is_active' => ['label' => 'Active', 'type' => 'checkbox', 'col' => 6, 'default' => 1],
        ];
    }

    protected function beforeSave(array $data, ?int $id): array
    {
        $data['email'] = strtolower((string) $data['email']);
        if (isset($data['password_hash']) && $id !== (int) $this->admin['id']) {
            $data['must_change_password'] = 1;
        }
        // Never let the last active super admin be demoted or deactivated.
        if ($id && $this->isLastSuperAdmin($id) && (! $data['is_active'] || (int) $data['role_id'] !== $this->superRoleId())) {
            $data['is_active'] = 1;
            $data['role_id']   = $this->superRoleId();
            session()->setFlashdata('info', 'The last Super Admin must stay active with full access.');
        }

        return $data;
    }

    protected function blockDelete(array $row): ?string
    {
        if ((int) $row['id'] === (int) $this->admin['id']) {
            return 'You cannot delete your own account.';
        }

        return $this->isLastSuperAdmin((int) $row['id']) ? 'The last Super Admin cannot be deleted.' : null;
    }

    public function toggle(int $id, string $field)
    {
        if ($id === (int) $this->admin['id'] || $this->isLastSuperAdmin($id)) {
            return redirect()->back()->with('error', 'This account must stay active.');
        }

        return parent::toggle($id, $field);
    }

    private function superRoleId(): int
    {
        return (int) model('RoleModel')->where('slug', 'super-admin')->first()['id'];
    }

    private function isLastSuperAdmin(int $id): bool
    {
        $supers = model('AdminUserModel')->where(['role_id' => $this->superRoleId(), 'is_active' => 1])->findColumn('id') ?? [];

        return count($supers) === 1 && (int) $supers[0] === $id;
    }
}
