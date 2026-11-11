<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?= $this->include('account/_open') ?>
<?php if ($order['payment_status'] !== 'paid' && ! in_array($order['status'], ['cancelled', 'refunded'], true)): ?>
    <div class="flash flash--info"><i class="bi bi-info-circle"></i><div>Payment for this order is pending. <a href="<?= site_url('checkout/payment/' . $order['order_number']) ?>"><strong>Complete payment</strong></a></div></div>
<?php endif ?>
<?= view('partials/order_detail', ['order' => $order]) ?>
<div class="mt-5"><a class="link-arrow" href="<?= site_url('my-account/orders') ?>"><i class="bi bi-arrow-left"></i> Back to orders</a></div>
<?= $this->include('account/_close') ?>
<?= $this->endSection() ?>
