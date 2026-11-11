<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Blocks every /admin/* route unless an active administrator is signed in. */
class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $adminId = session('admin_id');
        if (! $adminId) {
            session()->set('admin_intended', current_url());

            return redirect()->to(site_url('admin/login'));
        }

        $admin = model('AdminUserModel')->find($adminId);
        if (! $admin || ! $admin['is_active']) {
            session()->remove(['admin_id', 'admin_permissions']);

            return redirect()->to(site_url('admin/login'))->with('error', 'Your session has ended. Please sign in again.');
        }

        // Force a password change first when the account still has its seeded password.
        $path = trim($request->getUri()->getPath(), '/');
        if ($admin['must_change_password'] && ! preg_match('#admin/(profile|logout)#', $path)) {
            return redirect()->to(site_url('admin/profile'))->with('error', 'Please set a new password before continuing.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Admin screens must never be cached by browsers or proxies.
        $response->setHeader('Cache-Control', 'no-store, max-age=0');
    }
}
