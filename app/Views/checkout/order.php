<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <h1><?= $order['payment_status'] === 'paid' ? 'Thank you for your order' : 'Order ' . esc($order['order_number']) ?></h1>
        <p class="lede">Order <strong><?= esc($order['order_number']) ?></strong> — a confirmation has been sent to <?= esc($order['email']) ?>.</p>
    </div>
</section>
<section class="section--tight">
    <div class="shell shell--narrow">
        <?php if ($order['payment_status'] !== 'paid' && ! in_array($order['status'], ['cancelled', 'refunded'], true)): ?>
            <div class="flash flash--info"><i class="bi bi-info-circle"></i><div>
                <?php if ($gatewayReady): ?>Payment for this order has not been completed. <a href="<?= site_url('checkout/payment/' . $order['order_number']) ?>?key=<?= esc($order['access_token'], 'attr') ?>"><strong>Pay <?= money($order['grand_total']) ?> now</strong></a>.
                <?php else: ?>This order is awaiting payment. Nothing has been charged; our team will contact you to complete it.<?php endif ?>
            </div></div>
        <?php endif ?>
        <?= view('partials/order_detail', ['order' => $order]) ?>
        <div class="mt-5"><a class="btn btn-line" href="<?= site_url('shop') ?>">Continue shopping</a></div>
    </div>
</section>
<?= $this->endSection() ?>
