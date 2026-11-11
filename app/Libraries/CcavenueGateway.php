<?php

namespace App\Libraries;

/**
 * CCAvenue (the gateway used by the original natural-life.in store) — non-seamless
 * "billing page" integration.
 *
 * Flow: we post an AES-128-CBC encrypted request to CCAvenue; the customer pays on
 * CCAvenue's page; CCAvenue posts an encrypted response to our redirect URL, which we
 * decrypt with the merchant working key. Only a decrypted response whose order id,
 * amount and "Success" status match is treated as proof of payment.
 *
 * Credentials come from .env (ccavenue.merchantId / accessCode / workingKey) and are
 * never exposed to the browser.
 */
class CcavenueGateway
{
    private string $merchantId;
    private string $accessCode;
    private string $workingKey;
    private string $mode;

    public function __construct()
    {
        $this->merchantId = trim((string) env('ccavenue.merchantId', ''));
        $this->accessCode = trim((string) env('ccavenue.accessCode', ''));
        $this->workingKey = trim((string) env('ccavenue.workingKey', ''));
        $this->mode       = strtolower(trim((string) env('ccavenue.mode', 'test'))) === 'live' ? 'live' : 'test';
    }

    public function isConfigured(): bool
    {
        return $this->merchantId !== '' && $this->accessCode !== '' && $this->workingKey !== '';
    }

    public function mode(): string
    {
        return $this->mode;
    }

    public function endpoint(): string
    {
        $host = $this->mode === 'live' ? 'secure.ccavenue.com' : 'test.ccavenue.com';

        return "https://{$host}/transaction/transaction.do?command=initiateTransaction";
    }

    /** Fields for the auto-submitting form that sends the customer to CCAvenue. */
    public function buildRequest(array $order): array
    {
        $params = [
            'merchant_id'      => $this->merchantId,
            'order_id'         => $order['order_number'],
            'currency'         => 'INR',
            'amount'           => number_format((float) $order['grand_total'], 2, '.', ''),
            'redirect_url'     => site_url('payment/ccavenue/response'),
            'cancel_url'       => site_url('payment/ccavenue/cancel'),
            'language'         => 'EN',
            'billing_name'     => $order['billing_name'],
            'billing_address'  => trim($order['billing_line1'] . ' ' . $order['billing_line2']),
            'billing_city'     => $order['billing_city'],
            'billing_state'    => $order['billing_state'],
            'billing_zip'      => $order['billing_postcode'],
            'billing_country'  => 'India',
            'billing_tel'      => $order['phone'],
            'billing_email'    => $order['email'],
            'delivery_name'    => $order['shipping_name'],
            'delivery_address' => trim($order['shipping_line1'] . ' ' . $order['shipping_line2']),
            'delivery_city'    => $order['shipping_city'],
            'delivery_state'   => $order['shipping_state'],
            'delivery_zip'     => $order['shipping_postcode'],
            'delivery_country' => 'India',
            'delivery_tel'     => $order['shipping_phone'],
            'merchant_param1'  => $order['access_token'],
        ];

        return [
            'action'      => $this->endpoint(),
            'encRequest'  => $this->encrypt(http_build_query($params)),
            'access_code' => $this->accessCode,
        ];
    }

    /** Decrypts the gateway callback into an associative array; null when it cannot be decrypted. */
    public function parseResponse(string $encResponse): ?array
    {
        $plain = $this->decrypt($encResponse);
        if ($plain === null || $plain === '') {
            return null;
        }
        parse_str($plain, $data);

        return (is_array($data) && isset($data['order_id'], $data['order_status'])) ? $data : null;
    }

    /** True only for a successful payment whose order number and amount match our record. */
    public function isSuccessFor(array $response, array $order): bool
    {
        return ($response['order_status'] ?? '') === 'Success'
            && hash_equals($order['order_number'], (string) ($response['order_id'] ?? ''))
            && abs((float) ($response['amount'] ?? 0) - (float) $order['grand_total']) < 0.01
            && strtoupper((string) ($response['currency'] ?? 'INR')) === 'INR';
    }

    // CCAvenue's documented scheme: key = MD5(working key) as raw bytes, fixed IV 0x00..0x0f, hex output.
    public function encrypt(string $plain): string
    {
        return bin2hex(openssl_encrypt($plain, 'AES-128-CBC', $this->key(), OPENSSL_RAW_DATA, $this->iv()));
    }

    public function decrypt(string $hex): ?string
    {
        $hex = trim($hex);
        if ($hex === '' || ! ctype_xdigit($hex) || strlen($hex) % 2 !== 0) {
            return null;
        }
        $out = openssl_decrypt(hex2bin($hex), 'AES-128-CBC', $this->key(), OPENSSL_RAW_DATA, $this->iv());

        return $out === false ? null : $out;
    }

    private function key(): string
    {
        return hex2bin(md5($this->workingKey));
    }

    private function iv(): string
    {
        return pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    }
}
