<?php

namespace App\Controllers\Admin;

use CodeIgniter\Exceptions\PageNotFoundException;

class Customers extends AdminController
{
    protected string $module = 'customers';

    public function index(): string
    {
        $model = model('CustomerModel');
        $q     = trim((string) $this->request->getGet('q'));
        $model->select('customers.*, (SELECT COUNT(*) FROM orders o WHERE o.customer_id = customers.id) AS order_count,
            (SELECT COALESCE(SUM(o.grand_total),0) FROM orders o WHERE o.customer_id = customers.id AND o.payment_status = "paid") AS spent', false);
        if ($q !== '') {
            $model->groupStart()->like('email', $q)->orLike('first_name', $q)->orLike('last_name', $q)->orLike('phone', $q)->groupEnd();
        }

        return $this->render('admin/customers/index', [
            'title' => 'Customers', 'breadcrumbs' => [['Sales', null], ['Customers', null]],
            'rows' => $model->orderBy('id', 'DESC')->paginate(20, 'admin'), 'pager' => $model->pager, 'q' => $q,
        ]);
    }

    public function show(int $id): string
    {
        $customer = model('CustomerModel')->find($id);
        if (! $customer) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/customers/show', [
            'title'       => trim($customer['first_name'] . ' ' . $customer['last_name']),
            'breadcrumbs' => [['Sales', null], ['Customers', site_url('admin/customers')], [$customer['email'], null]],
            'customer'    => $customer,
            'orders'      => model('OrderModel')->where('customer_id', $id)->orderBy('id', 'DESC')->findAll(50),
            'addresses'   => model('CustomerAddressModel')->where('customer_id', $id)->orderBy('is_default', 'DESC')->findAll(),
        ]);
    }

    /** Enable or disable a customer's ability to sign in. */
    public function toggle(int $id)
    {
        $model    = model('CustomerModel');
        $customer = $model->find($id);
        if (! $customer) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->update($id, ['is_active' => $customer['is_active'] ? 0 : 1]);

        return redirect()->back()->with('success', $customer['is_active'] ? 'Customer account disabled.' : 'Customer account enabled.');
    }
}
