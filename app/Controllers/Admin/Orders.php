<?php

namespace App\Controllers\Admin;

use App\Libraries\Mailer;
use App\Libraries\OrderService;
use App\Models\OrderModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Orders extends AdminController
{
    protected string $module = 'orders';

    public function index(): string
    {
        $model   = model(OrderModel::class);
        $q       = trim((string) $this->request->getGet('q'));
        $status  = (string) $this->request->getGet('status');
        $payment = (string) $this->request->getGet('payment');
        $from    = (string) $this->request->getGet('from');
        $to      = (string) $this->request->getGet('to');

        if ($q !== '') {
            $model->groupStart()->like('order_number', $q)->orLike('email', $q)->orLike('shipping_name', $q)->orLike('phone', $q)->groupEnd();
        }
        if (isset(OrderModel::STATUSES[$status])) {
            $model->where('status', $status);
        }
        if (isset(OrderModel::PAYMENT_STATUSES[$payment])) {
            $model->where('payment_status', $payment);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $model->where('created_at >=', $from . ' 00:00:00');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $model->where('created_at <=', $to . ' 23:59:59');
        }

        return $this->render('admin/orders/index', [
            'title'       => 'Orders',
            'breadcrumbs' => [['Sales', null], ['Orders', null]],
            'rows'        => $model->orderBy('id', 'DESC')->paginate(20, 'admin'),
            'pager'       => $model->pager,
            'q'           => $q, 'status' => $status, 'payment' => $payment, 'from' => $from, 'to' => $to,
        ]);
    }

    public function show(int $id): string
    {
        $model = model(OrderModel::class);
        $order = $model->withDetails($this->find($id));

        return $this->render('admin/orders/show', [
            'title'       => 'Order ' . $order['order_number'],
            'breadcrumbs' => [['Sales', null], ['Orders', site_url('admin/orders')], [$order['order_number'], null]],
            'order'       => $order,
            'customer'    => $order['customer_id'] ? model('CustomerModel')->find($order['customer_id']) : null,
            'orderCount'  => $model->where('email', $order['email'])->countAllResults(),
        ]);
    }

    public function updateStatus(int $id)
    {
        $order  = $this->find($id);
        $status = (string) $this->request->getPost('status');
        if (! isset(OrderModel::STATUSES[$status])) {
            return redirect()->back()->with('error', 'Choose a valid status.');
        }
        if ($status === $order['status']) {
            return redirect()->back()->with('info', 'The order already has that status.');
        }
        // An unpaid order cannot move into fulfilment.
        if (in_array($status, ['processing', 'shipped', 'completed'], true) && $order['payment_status'] !== 'paid') {
            return redirect()->back()->with('error', 'Record the payment before moving this order to “' . OrderModel::STATUSES[$status] . '”.');
        }

        (new OrderService())->setStatus($order, $status, $this->admin['name'], trim((string) $this->request->getPost('note')) ?: null);
        if ($this->request->getPost('notify')) {
            $model = model(OrderModel::class);
            (new Mailer())->orderStatus($model->withDetails($model->find($id)));
        }

        return redirect()->back()->with('success', 'Order status updated.');
    }

    public function updateShipping(int $id)
    {
        $order = $this->find($id);
        if (! $this->validate(['courier_name' => 'permit_empty|max_length[80]', 'tracking_number' => 'permit_empty|max_length[80]', 'tracking_url' => 'permit_empty|valid_url_strict|max_length[255]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = [];
        foreach (['courier_name', 'tracking_number', 'tracking_url'] as $f) {
            $data[$f] = trim((string) $this->request->getPost($f)) ?: null;
        }
        model(OrderModel::class)->update($id, $data);
        (new OrderService())->log($id, $order['status'], 'Shipping details updated' . ($data['tracking_number'] ? ' (tracking ' . $data['tracking_number'] . ')' : '') . '.', $this->admin['name']);

        return redirect()->back()->with('success', 'Shipping details saved.');
    }

    /**
     * Records a payment received outside the gateway (e.g. bank transfer confirmed by accounts).
     * Gateway payments are recorded automatically and only from a verified CCAvenue response.
     */
    public function recordPayment(int $id)
    {
        $order = $this->find($id);
        if ($order['payment_status'] === 'paid') {
            return redirect()->back()->with('info', 'This order is already marked as paid.');
        }
        if (! $this->validate(['reference' => ['label' => 'Payment reference', 'rules' => 'required|min_length[3]|max_length[120]'], 'confirm' => ['label' => 'Confirmation', 'rules' => 'required']])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $ref = trim((string) $this->request->getPost('reference'));
        model('PaymentModel')->insert([
            'order_id' => $id, 'gateway' => 'manual', 'status' => 'Success', 'amount' => $order['grand_total'], 'gateway_reference' => $ref,
            'payment_mode' => trim((string) $this->request->getPost('mode')) ?: 'Offline', 'message' => 'Recorded by ' . $this->admin['name'], 'verified' => 1,
        ]);
        (new OrderService())->markPaid($order, $this->admin['name'], 'Payment recorded manually (ref ' . $ref . ').');

        return redirect()->back()->with('success', 'Payment recorded and order moved to Processing.');
    }

    public function saveNote(int $id)
    {
        $this->find($id);
        model(OrderModel::class)->update($id, ['admin_note' => mb_substr(trim((string) $this->request->getPost('admin_note')), 0, 2000) ?: null]);

        return redirect()->back()->with('success', 'Note saved.');
    }

    private function find(int $id): array
    {
        $order = model(OrderModel::class)->find($id);
        if (! $order) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $order;
    }
}
