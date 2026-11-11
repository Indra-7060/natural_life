<?php

namespace App\Controllers;

use App\Libraries\CcavenueGateway;
use App\Libraries\Mailer;
use App\Libraries\OrderService;
use App\Models\OrderModel;

/**
 * CCAvenue callbacks. These endpoints are exempt from CSRF (the gateway posts to them)
 * and trust nothing except a payload that decrypts with the merchant working key.
 */
class Payment extends BaseController
{
    public function ccavenueResponse()
    {
        return $this->handle(false);
    }

    public function ccavenueCancel()
    {
        return $this->handle(true);
    }

    private function handle(bool $cancelled)
    {
        $gateway = new CcavenueGateway();
        $enc     = (string) $this->request->getPost('encResp');
        if (! $gateway->isConfigured() || $enc === '') {
            return redirect()->to(site_url('/'))->with('error', 'We could not read the payment response.');
        }

        $data = $gateway->parseResponse($enc);
        if ($data === null) {
            log_message('error', 'CCAvenue response could not be decrypted.');

            return redirect()->to(site_url('/'))->with('error', 'We could not verify the payment response.');
        }

        $orders = model(OrderModel::class);
        $order  = $orders->findByNumber((string) $data['order_id']);
        if (! $order) {
            log_message('error', 'CCAvenue response for unknown order {id}.', ['id' => $data['order_id']]);

            return redirect()->to(site_url('/'))->with('error', 'We could not match the payment to an order.');
        }

        $verified = ! $cancelled && $gateway->isSuccessFor($data, $order);
        $safe     = array_intersect_key($data, array_flip(['order_id', 'tracking_id', 'bank_ref_no', 'order_status', 'failure_message', 'payment_mode', 'card_name', 'status_code', 'status_message', 'currency', 'amount', 'trans_date']));

        model('PaymentModel')->insert([
            'order_id'          => $order['id'],
            'gateway'           => 'ccavenue',
            'status'            => (string) ($data['order_status'] ?? 'Unknown'),
            'amount'            => (float) ($data['amount'] ?? 0),
            'gateway_reference' => $data['tracking_id'] ?? null,
            'bank_reference'    => $data['bank_ref_no'] ?? null,
            'payment_mode'      => $data['payment_mode'] ?? null,
            'message'           => mb_substr((string) ($data['status_message'] ?? $data['failure_message'] ?? ''), 0, 250),
            'raw_response'      => json_encode($safe),
            'verified'          => $verified ? 1 : 0,
        ]);

        $service = new OrderService();
        $link    = site_url('order/' . $order['order_number']) . '?key=' . $order['access_token'];

        if ($verified) {
            $service->markPaid($order, 'CCAvenue', 'Payment confirmed by CCAvenue (ref ' . ($data['tracking_id'] ?? '-') . ').');
            (new Mailer())->orderStatus($orders->withDetails($orders->find($order['id'])));

            return redirect()->to($link)->with('success', 'Payment received. Thank you for your order!');
        }

        $service->markPaymentFailed($order, 'CCAvenue status: ' . ($data['order_status'] ?? 'Unknown') . '.');

        return redirect()->to($link)->with('error', 'Your payment was not completed. You can try again from this page.');
    }
}
