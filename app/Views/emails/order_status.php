<?php
$link   = site_url('order/' . $order['order_number']) . '?key=' . $order['access_token'];
$labels = \App\Models\OrderModel::STATUSES;
ob_start(); ?>
<h1 style="font-family:Georgia,serif;font-weight:normal;font-size:24px;margin:0 0 12px;color:#142a1e;">Order <?= esc($order['order_number']) ?>: <?= esc($labels[$order['status']] ?? ucfirst($order['status'])) ?></h1>
<p>Hello <?= esc($order['shipping_name']) ?>,</p>
<p>The status of your order is now <strong><?= esc($labels[$order['status']] ?? $order['status']) ?></strong> (payment: <?= esc(ucfirst($order['payment_status'])) ?>).</p>
<?php if ($order['tracking_number']): ?>
    <p><strong>Shipment<?= $order['courier_name'] ? ' with ' . esc($order['courier_name']) : '' ?></strong><br>Tracking number: <?= esc($order['tracking_number']) ?>
        <?php if ($order['tracking_url']): ?><br><a href="<?= esc($order['tracking_url'], 'attr') ?>">Track your shipment</a><?php endif ?></p>
<?php endif ?>
<?= view('emails/_order_table', ['order' => $order]) ?>
<p style="margin-top:24px;"><a href="<?= esc($link, 'attr') ?>" style="background:#1f3a2b;color:#fbf8f1;text-decoration:none;padding:12px 22px;border-radius:999px;display:inline-block;">View your order</a></p>
<?php $content = ob_get_clean();
echo view('emails/_layout', ['subject' => $subject, 'content' => $content]);
