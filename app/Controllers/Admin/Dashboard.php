<?php

namespace App\Controllers\Admin;

class Dashboard extends AdminController
{
    protected string $module = 'dashboard';

    public function index(): string
    {
        $db = db_connect();

        // Sales for the last 30 days (paid orders only), zero-filled per day.
        $rows = $db->table('orders')->select('DATE(created_at) AS d, SUM(grand_total) AS total, COUNT(*) AS n')
            ->where('payment_status', 'paid')->where('created_at >=', date('Y-m-d 00:00:00', strtotime('-29 days')))
            ->groupBy('DATE(created_at)')->get()->getResultArray();
        $byDay = array_column($rows, null, 'd');
        $chart = ['labels' => [], 'sales' => [], 'orders' => []];
        for ($i = 29; $i >= 0; $i--) {
            $day               = date('Y-m-d', strtotime("-{$i} days"));
            $chart['labels'][] = date('j M', strtotime($day));
            $chart['sales'][]  = (float) ($byDay[$day]['total'] ?? 0);
            $chart['orders'][] = (int) ($byDay[$day]['n'] ?? 0);
        }

        $paid   = $db->table('orders')->select('COALESCE(SUM(grand_total),0) AS total, COUNT(*) AS n')->where('payment_status', 'paid')->get()->getRowArray();
        $month  = $db->table('orders')->select('COALESCE(SUM(grand_total),0) AS total')->where('payment_status', 'paid')->where('created_at >=', date('Y-m-01 00:00:00'))->get()->getRowArray();
        $status = array_column($db->table('orders')->select('status, COUNT(*) AS n')->groupBy('status')->get()->getResultArray(), 'n', 'status');

        return $this->render('admin/dashboard', [
            'title' => 'Dashboard',
            'stats' => [
                'products'   => $db->table('products')->where('deleted_at', null)->countAllResults(),
                'categories' => $db->table('product_categories')->countAllResults(),
                'customers'  => $db->table('customers')->countAllResults(),
                'orders'     => $db->table('orders')->countAllResults(),
                'pending'    => (int) ($status['pending'] ?? 0) + (int) ($status['processing'] ?? 0),
                'completed'  => (int) ($status['completed'] ?? 0),
                'enquiries'  => $db->table('contact_enquiries')->countAllResults(),
                'subscribers' => $db->table('newsletter_subscribers')->where('is_active', 1)->countAllResults(),
                'revenue'    => (float) $paid['total'],
                'revenueMonth' => (float) $month['total'],
                'paidOrders' => (int) $paid['n'],
                'outOfStock' => $db->table('products')->where(['deleted_at' => null, 'stock_status' => 'out_of_stock'])->countAllResults(),
            ],
            'status'    => $status,
            'chart'     => $chart,
            'orders'    => admin_can('orders') ? model('OrderModel')->orderBy('id', 'DESC')->findAll(6) : [],
            'enquiries' => admin_can('inbox') ? model('ContactEnquiryModel')->orderBy('id', 'DESC')->findAll(5) : [],
            'top'       => $db->table('order_items oi')->select('oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.line_total) AS total')
                ->join('orders o', 'o.id = oi.order_id')->where('o.payment_status', 'paid')->groupBy('oi.product_name')->orderBy('total', 'DESC')->limit(5)->get()->getResultArray(),
        ]);
    }

    /** Global search across products, orders, customers and pages. */
    public function search(): string
    {
        $q       = mb_substr(trim((string) $this->request->getGet('q')), 0, 80);
        $results = [];
        if (mb_strlen($q) >= 2) {
            $db = db_connect();
            if (admin_can('catalogue')) {
                foreach ($db->table('products')->where('deleted_at', null)->like('name', $q)->limit(8)->get()->getResultArray() as $r) {
                    $results['Products'][] = [$r['name'], site_url('admin/products/' . $r['id'] . '/edit'), money($r['price_min'])];
                }
            }
            if (admin_can('orders')) {
                foreach ($db->table('orders')->groupStart()->like('order_number', $q)->orLike('email', $q)->orLike('shipping_name', $q)->orLike('phone', $q)->groupEnd()->orderBy('id', 'DESC')->limit(8)->get()->getResultArray() as $r) {
                    $results['Orders'][] = [$r['order_number'] . ' — ' . $r['shipping_name'], site_url('admin/orders/' . $r['id']), money($r['grand_total'])];
                }
            }
            if (admin_can('customers')) {
                foreach ($db->table('customers')->groupStart()->like('email', $q)->orLike('first_name', $q)->orLike('last_name', $q)->groupEnd()->limit(8)->get()->getResultArray() as $r) {
                    $results['Customers'][] = [trim($r['first_name'] . ' ' . $r['last_name']), site_url('admin/customers/' . $r['id']), $r['email']];
                }
            }
            if (admin_can('content')) {
                foreach ($db->table('pages')->like('title', $q)->limit(8)->get()->getResultArray() as $r) {
                    $results['Pages'][] = [$r['title'], site_url('admin/pages/' . $r['id'] . '/edit'), '/' . $r['slug']];
                }
            }
            if (admin_can('blog')) {
                foreach ($db->table('blog_posts')->like('title', $q)->limit(8)->get()->getResultArray() as $r) {
                    $results['Blog posts'][] = [$r['title'], site_url('admin/posts/' . $r['id'] . '/edit'), ''];
                }
            }
        }

        return $this->render('admin/search', ['title' => 'Search', 'breadcrumbs' => [['Search', null]], 'q' => $q, 'results' => $results]);
    }
}
