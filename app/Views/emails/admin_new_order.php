<?php ob_start(); ?>
<h1 style="font-family:Georgia,serif;font-weight:normal;font-size:24px;margin:0 0 12px;color:#142a1e;">New order <?= esc($order['order_number']) ?></h1>
<p><?= esc($order['shipping_name']) ?> · <?= esc($order['email']) ?> · <?= esc($order['phone']) ?><br>Payment status: <strong><?= esc(ucfirst($order['payment_status'])) ?></strong></p>
<?= view('emails/_order_table', ['order' => $order]) ?>
<?php if ($order['customer_note']): ?><p><strong>Customer note:</strong> <?= esc($order['customer_note']) ?></p><?php endif ?>
<p style="margin-top:24px;"><a href="<?= site_url('admin/orders/' . $order['id']) ?>" style="background:#1f3a2b;color:#fbf8f1;text-decoration:none;padding:12px 22px;border-radius:999px;display:inline-block;">Open in admin</a></p>
<?php $content = ob_get_clean();
echo view('emails/_layout', ['subject' => $subject, 'content' => $content]);
