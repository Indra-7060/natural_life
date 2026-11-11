<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base for every admin screen. The `adminauth` route filter guarantees a signed-in
 * administrator; this class adds the per-module permission check and the shared layout data.
 */
abstract class AdminController extends Controller
{
    protected $helpers = ['url', 'form', 'text', 'site'];

    /** Permission module this controller belongs to (see AccessSeeder::MODULES). */
    protected string $module = 'dashboard';

    protected ?array $admin = null;

    /** Sidebar structure: group => [label, path, icon, module]. */
    public const NAV = [
        'Overview' => [['Dashboard', 'admin', 'speedometer2', 'dashboard']],
        'Catalogue' => [
            ['Products', 'admin/products', 'box-seam', 'catalogue'],
            ['Categories', 'admin/categories', 'tags', 'catalogue'],
            ['Coupons', 'admin/coupons', 'ticket-perforated', 'catalogue'],
        ],
        'Sales' => [
            ['Orders', 'admin/orders', 'receipt', 'orders'],
            ['Customers', 'admin/customers', 'people', 'customers'],
        ],
        'Website content' => [
            ['Home page', 'admin/home', 'house', 'content'],
            ['Home banners', 'admin/banners', 'images', 'content'],
            ['Pages', 'admin/pages', 'file-earmark-text', 'content'],
            ['Navigation', 'admin/menus', 'menu-button-wide', 'content'],
            ['FAQs', 'admin/faqs', 'question-circle', 'content'],
            ['Testimonials', 'admin/testimonials', 'chat-quote', 'content'],
            ['Team', 'admin/team', 'person-badge', 'content'],
            ['Certifications', 'admin/certifications', 'patch-check', 'content'],
        ],
        'Blog' => [
            ['Posts', 'admin/posts', 'journal-text', 'blog'],
            ['Blog categories', 'admin/blog-categories', 'bookmark', 'blog'],
        ],
        'Inbox' => [
            ['Enquiries', 'admin/enquiries', 'envelope', 'inbox'],
            ['Newsletter', 'admin/subscribers', 'send', 'inbox'],
        ],
        'Library' => [['Media library', 'admin/media', 'image', 'media']],
        'Configuration' => [
            ['Settings', 'admin/settings', 'gear', 'settings'],
            ['SEO', 'admin/seo', 'search', 'settings'],
            ['Redirects', 'admin/redirects', 'signpost-split', 'settings'],
            ['Admin users', 'admin/users', 'shield-lock', 'users'],
        ],
    ];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->admin = model('AdminUserModel')->find((int) session('admin_id'));
        if ($this->admin && ! admin_can($this->module)) {
            session()->setFlashdata('error', 'Your role does not have access to that section.');

            throw new RedirectException(site_url('admin'));
        }
    }

    protected function render(string $view, array $data = []): string
    {
        $data['admin']       = $this->admin;
        $data['title']       = $data['title'] ?? 'Admin';
        $data['breadcrumbs'] = $data['breadcrumbs'] ?? [];
        $data['newOrders']   = admin_can('orders') ? model('OrderModel')->where('status', 'pending')->countAllResults() : 0;
        $data['newEnquiries'] = admin_can('inbox') ? model('ContactEnquiryModel')->where('status', 'new')->countAllResults() : 0;

        return view($view, $data);
    }

    /** Clears cached settings, menus, redirect map and header data after any content change. */
    protected function flushCache(): void
    {
        cache()->clean();
        \App\Models\SettingModel::flush();
    }

    protected function back(string $path, string $message, string $type = 'success')
    {
        return redirect()->to(site_url($path))->with($type, $message);
    }
}
