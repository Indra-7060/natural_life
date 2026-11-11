<?php

namespace App\Libraries;

/**
 * Sends transactional e-mail through SMTP when it is configured in .env.
 * Without SMTP details (typical on XAMPP) each message is written to
 * writable/emails/ as an .html file so the content can still be reviewed.
 */
class Mailer
{
    public function isConfigured(): bool
    {
        return trim((string) env('email.SMTPHost', '')) !== '';
    }

    public function send(string $to, string $subject, string $view, array $data = []): bool
    {
        $data['subject'] = $subject;
        $html            = view('emails/' . $view, $data);

        if (! $this->isConfigured()) {
            $dir = WRITEPATH . 'emails/';
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $dir . date('Ymd-His') . '-' . slugify($view) . '-' . substr(md5($to . microtime()), 0, 6) . '.html';
            file_put_contents($file, "<!-- To: {$to} | Subject: {$subject} -->\n" . $html);
            log_message('info', 'E-mail to {to} saved to {file} (SMTP not configured).', ['to' => $to, 'file' => basename($file)]);

            return true;
        }

        try {
            $email = service('email');
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => env('email.SMTPHost'),
                'SMTPUser'   => env('email.SMTPUser'),
                'SMTPPass'   => env('email.SMTPPass'),
                'SMTPPort'   => (int) env('email.SMTPPort', 587),
                'SMTPCrypto' => env('email.SMTPCrypto', 'tls'),
                'mailType'   => 'html',
                'charset'    => 'UTF-8',
                'newline'    => "\r\n",
            ]);
            $email->setFrom(env('email.fromEmail', 'no-reply@natural-life.in'), env('email.fromName', setting('site_name', 'Sugarlif')));
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($html);
            $ok = $email->send(false);
            if (! $ok) {
                log_message('error', 'E-mail to {to} failed: {debug}', ['to' => $to, 'debug' => strip_tags($email->printDebugger(['headers']))]);
            }
            $email->clear(true);

            return $ok;
        } catch (\Throwable $e) {
            log_message('error', 'E-mail to {to} failed: {msg}', ['to' => $to, 'msg' => $e->getMessage()]);

            return false;
        }
    }

    public function orderConfirmation(array $order): bool
    {
        return $this->send($order['email'], 'Your Sugarlif order ' . $order['order_number'], 'order_confirmation', ['order' => $order]);
    }

    public function orderStatus(array $order): bool
    {
        return $this->send($order['email'], 'Update on your order ' . $order['order_number'], 'order_status', ['order' => $order]);
    }

    public function adminNotice(string $subject, string $view, array $data): bool
    {
        $to = setting('notification_email', setting('contact_email_primary', ''));

        return $to ? $this->send($to, $subject, $view, $data) : false;
    }
}
