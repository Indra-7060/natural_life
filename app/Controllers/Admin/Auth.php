<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;

/** Admin sign-in, sign-out and own profile / password. */
class Auth extends Controller
{
    protected $helpers = ['url', 'form', 'site'];

    public function login()
    {
        if (session('admin_id')) {
            return redirect()->to(site_url('admin'));
        }

        return view('admin/auth/login');
    }

    public function attempt()
    {
        if (! $this->validate(['email' => 'required|valid_email', 'password' => 'required'])) {
            return redirect()->back()->withInput()->with('error', 'Enter your email and password.');
        }
        // Throttle: 6 attempts per 10 minutes per IP address.
        if (service('throttler')->check('admin_login_' . md5($this->request->getIPAddress()), 6, 600) === false) {
            return redirect()->back()->with('error', 'Too many attempts. Please wait a few minutes and try again.');
        }

        $users = model('AdminUserModel');
        $user  = $users->where('email', strtolower(trim((string) $this->request->getPost('email'))))->first();
        if (! $user || ! $user['is_active'] || ! password_verify((string) $this->request->getPost('password'), $user['password_hash'])) {
            log_message('notice', 'Failed admin sign-in for {email} from {ip}', ['email' => $this->request->getPost('email'), 'ip' => $this->request->getIPAddress()]);

            return redirect()->back()->withInput()->with('error', 'The email or password is incorrect.');
        }

        $perms = db_connect()->table('role_permissions rp')->select('p.module')->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $user['role_id'])->get()->getResultArray();
        $role = model('RoleModel')->find($user['role_id']);

        $intended = session('admin_intended');
        session()->regenerate(true);
        session()->set([
            'admin_id'          => $user['id'],
            'admin_permissions' => ($role['slug'] ?? '') === 'super-admin' ? ['*'] : array_column($perms, 'module'),
        ]);
        session()->remove('admin_intended');
        $users->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to($intended && str_starts_with($intended, site_url('admin')) ? $intended : site_url('admin'));
    }

    public function logout()
    {
        session()->remove(['admin_id', 'admin_permissions']);
        session()->regenerate(true);

        return redirect()->to(site_url('admin/login'))->with('success', 'You have been signed out.');
    }

    public function profile()
    {
        $admin = model('AdminUserModel')->find((int) session('admin_id'));

        return view('admin/auth/profile', [
            'admin' => $admin, 'title' => 'My profile', 'breadcrumbs' => [['My profile', null]],
            'newOrders' => 0, 'newEnquiries' => 0,
        ]);
    }

    public function updateProfile()
    {
        $users = model('AdminUserModel');
        $admin = $users->find((int) session('admin_id'));
        $rules = [
            'name'  => 'required|max_length[120]',
            'email' => 'required|valid_email|is_unique[admin_users.email,id,' . $admin['id'] . ']',
        ];
        $changing = (string) $this->request->getPost('new_password') !== '' || $admin['must_change_password'];
        if ($changing) {
            $rules += [
                'current_password'     => 'required',
                'new_password'         => ['label' => 'New password', 'rules' => 'required|min_length[10]|max_length[72]'],
                'new_password_confirm' => ['label' => 'Confirm password', 'rules' => 'required|matches[new_password]'],
            ];
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = ['name' => trim((string) $this->request->getPost('name')), 'email' => strtolower(trim((string) $this->request->getPost('email')))];
        if ($changing) {
            if (! password_verify((string) $this->request->getPost('current_password'), $admin['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Your current password is incorrect.');
            }
            if (password_verify((string) $this->request->getPost('new_password'), $admin['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Choose a password different from the current one.');
            }
            $data['password_hash']        = password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT);
            $data['must_change_password'] = 0;
        }
        $users->update($admin['id'], $data);

        return redirect()->to(site_url($changing ? 'admin' : 'admin/profile'))->with('success', 'Your profile has been updated.');
    }
}
