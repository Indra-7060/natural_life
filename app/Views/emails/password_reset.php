<?php ob_start(); ?>
<h1 style="font-family:Georgia,serif;font-weight:normal;font-size:24px;margin:0 0 12px;color:#142a1e;">Reset your password</h1>
<p>Hello <?= esc($customer['first_name'] ?? $customer['name'] ?? '') ?>,</p>
<p>We received a request to reset the password for your account. This link is valid for one hour.</p>
<p style="margin:24px 0;"><a href="<?= esc($link, 'attr') ?>" style="background:#1f3a2b;color:#fbf8f1;text-decoration:none;padding:12px 22px;border-radius:999px;display:inline-block;">Choose a new password</a></p>
<p style="color:#5e6a60;font-size:13px;">If you did not request this, you can ignore this e-mail — your password will not change.</p>
<?php $content = ob_get_clean();
echo view('emails/_layout', ['subject' => $subject, 'content' => $content]);
