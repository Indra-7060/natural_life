<?php
$link = site_url('order/' . $order['order_number']) . '?key=' . $order['access_token'];
ob_start(); ?>
<h1 style="font-family:Georgia,serif;font-weight:normal;font-size:24px;margin:0 0 12px;color:#142a1e;">Thank you — we have received your order</h1>
<p>Hello <?= esc($order['shipping_name']) ?>,</p>
<p>Your order <strong><?= esc($order['order_number']) ?></strong> has been placed.
    <?= $order['payment_status'] === 'paid' ? 'Payment has been received.' : 'It will be processed as soon as payment is confirmed.' ?></p>
<?= view('emails/_order_table', ['order' => $order]) ?>
<p style="margin-top:24px;"><a href="<?= esc($link, 'attr') ?>" style="background:#1f3a2b;color:#fbf8f1;text-decoration:none;padding:12px 22px;border-radius:999px;display:inline-block;">View your order</a></p>
<?php $content = ob_get_clean();
echo view('emails/_layout', ['subject' => $subject, 'content' => $content]);
