<?php ob_start(); ?>
<h1 style="font-family:Georgia,serif;font-weight:normal;font-size:24px;margin:0 0 12px;color:#142a1e;">Welcome to Sugarlif, <?= esc($customer['first_name']) ?></h1>
<p>Your account has been created. You can now track orders, save addresses and keep a wishlist.</p>
<p style="margin-top:24px;"><a href="<?= site_url('my-account') ?>" style="background:#1f3a2b;color:#fbf8f1;text-decoration:none;padding:12px 22px;border-radius:999px;display:inline-block;">Go to my account</a></p>
<?php $content = ob_get_clean();
echo view('emails/_layout', ['subject' => $subject, 'content' => $content]);
