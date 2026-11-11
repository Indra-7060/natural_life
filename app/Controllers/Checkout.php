<?php

namespace App\Controllers;

use App\Libraries\CcavenueGateway;
use App\Libraries\Mailer;
use App\Libraries\OrderService;
use App\Models\OrderModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class Checkout extends BaseController
{
    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->to(site_url('cart'))->with('info', 'Your cart is empty.');
        }
        if ($this->cart->hasUnavailable()) {
            return redirect()->to(site_url('cart'))->with('error', 'Some items in your cart are no longer available. Please remove them to continue.');
        }

        $addresses = $this->customer
            ? model('CustomerAddressModel')->where('customer_id', $this->customer['id'])->orderBy('is_default', 'DESC')->orderBy('id', 'DESC')->findAll()
            : [];

        return $this->render('checkout/index', [
            'lines'     => $this->cart->lines(),
            'totals'    => $this->cart->totals(),
            'addresses' => $addresses,
            'gatewayReady' => (new CcavenueGateway())->isConfigured(),
            'breadcrumbs'  => [['Home', site_url('/')], ['Cart', site_url('cart')], ['Checkout', null]],
        ], $this->seo(['title' => 'Checkout', 'noindex' => true]));
    }

    public function place()
    {
        if ($this->cart->isEmpty() || $this->cart->hasUnavailable()) {
            return redirect()->to(site_url('cart'));
        }

        $sameAsShipping = (bool) $this->request->getPost('billing_same');
        $rules = [
            'email'             => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[190]'],
            'phone'             => ['label' => 'Phone', 'rules' => 'required|regex_match[/^[0-9+\-\s]{10,15}$/]'],
            'shipping_name'     => ['label' => 'Full name', 'rules' => 'required|min_length[2]|max_length[160]'],
            'shipping_line1'    => ['label' => 'Address', 'rules' => 'required|min_length[5]|max_length[190]'],
            'shipping_line2'    => ['label' => 'Address line 2', 'rules' => 'permit_empty|max_length[190]'],
            'shipping_city'     => ['label' => 'City', 'rules' => 'required|max_length[90]'],
            'shipping_state'    => ['label' => 'State', 'rules' => 'required|in_list[' . implode(',', indian_states()) . ']'],
            'shipping_postcode' => ['label' => 'PIN code', 'rules' => 'required|regex_match[/^[1-9][0-9]{5}$/]'],
            'customer_note'     => ['label' => 'Order note', 'rules' => 'permit_empty|max_length[500]'],
            'accept_terms'      => ['label' => 'Terms', 'rules' => 'required', 'errors' => ['required' => 'Please accept the terms and policies to place your order.']],
        ];
        if (! $sameAsShipping) {
            $rules += [
                'billing_name'     => ['label' => 'Billing name', 'rules' => 'required|min_length[2]|max_length[160]'],
                'billing_line1'    => ['label' => 'Billing address', 'rules' => 'required|min_length[5]|max_length[190]'],
                'billing_city'     => ['label' => 'Billing city', 'rules' => 'required|max_length[90]'],
                'billing_state'    => ['label' => 'Billing state', 'rules' => 'required|in_list[' . implode(',', indian_states()) . ']'],
                'billing_postcode' => ['label' => 'Billing PIN code', 'rules' => 'required|regex_match[/^[1-9][0-9]{5}$/]'],
            ];
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $data = [
            'email' => strtolower(trim($post['email'])), 'phone' => trim($post['phone']), 'customer_note' => trim($post['customer_note'] ?? ''),
            'shipping_name' => trim($post['shipping_name']), 'shipping_phone' => trim($post['phone']),
            'shipping_line1' => trim($post['shipping_line1']), 'shipping_line2' => trim($post['shipping_line2'] ?? ''),
            'shipping_city' => trim($post['shipping_city']), 'shipping_state' => $post['shipping_state'], 'shipping_postcode' => trim($post['shipping_postcode']),
        ];
        foreach (['name', 'line1', 'line2', 'city', 'state', 'postcode'] as $f) {
            $data['billing_' . $f] = $sameAsShipping ? $data['shipping_' . $f] : trim($post['billing_' . $f] ?? '');
        }

        try {
            $order = (new OrderService())->createFromCart($this->cart, $data, $this->customer['id'] ?? null);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('cart'))->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Order creation failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'We could not place your order. Please try again.');
        }

        // Save the address for signed-in customers when asked.
        if ($this->customer && $this->request->getPost('save_address')) {
            $addr = model('CustomerAddressModel');
            $dupe = $addr->where(['customer_id' => $this->customer['id'], 'line1' => $data['shipping_line1'], 'postcode' => $data['shipping_postcode']])->first();
            if (! $dupe) {
                $addr->insert([
                    'customer_id' => $this->customer['id'], 'label' => 'Home', 'full_name' => $data['shipping_name'], 'phone' => $data['phone'],
                    'line1' => $data['shipping_line1'], 'line2' => $data['shipping_line2'], 'city' => $data['shipping_city'],
                    'state' => $data['shipping_state'], 'postcode' => $data['shipping_postcode'],
                    'is_default' => $addr->where('customer_id', $this->customer['id'])->countAllResults() === 0 ? 1 : 0,
                ]);
            }
        }

        $this->cart->clear();
        $orders = model(OrderModel::class);
        $full   = $orders->withDetails($order);
        $mailer = new Mailer();
        $mailer->orderConfirmation($full);
        $mailer->adminNotice('New order ' . $order['order_number'], 'admin_new_order', ['order' => $full]);

        return redirect()->to(site_url('checkout/payment/' . $order['order_number']) . '?key=' . $order['access_token']);
    }

    /** Hands the customer to CCAvenue, or explains that online payment is not configured yet. */
    public function payment(string $number)
    {
        $order = $this->authorisedOrder($number);
        if ($order['payment_status'] === 'paid') {
            return redirect()->to(site_url('order/' . $order['order_number']) . '?key=' . $order['access_token']);
        }
        if ($order['status'] === 'cancelled') {
            return redirect()->to(site_url('order/' . $order['order_number']) . '?key=' . $order['access_token'])->with('error', 'This order was cancelled.');
        }

        $gateway = new CcavenueGateway();

        return $this->render('checkout/payment', [
            'order'   => $order,
            'gateway' => $gateway->isConfigured() ? $gateway->buildRequest($order) : null,
        ], $this->seo(['title' => 'Payment', 'noindex' => true]));
    }

    /** Order confirmation / tracking page for guests (signed link) and customers. */
    public function order(string $number): string
    {
        $order = model(OrderModel::class)->withDetails($this->authorisedOrder($number));

        return $this->render('checkout/order', [
            'order'       => $order,
            'gatewayReady' => (new CcavenueGateway())->isConfigured(),
            'breadcrumbs' => [['Home', site_url('/')], ['Order ' . $order['order_number'], null]],
        ], $this->seo(['title' => 'Order ' . $order['order_number'], 'noindex' => true]));
    }

    /** An order may be viewed by its owner or with the unguessable key from the confirmation link. */
    private function authorisedOrder(string $number): array
    {
        $order = model(OrderModel::class)->findByNumber($number);
        $key   = (string) $this->request->getGet('key');
        $owns  = $order && $this->customer && (int) $order['customer_id'] === (int) $this->customer['id'];
        if (! $order || (! $owns && ! ($key !== '' && hash_equals($order['access_token'], $key)))) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $order;
    }
}
