<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?= $this->include('account/_open') ?>
<p class="lede mb-4">Hello <?= esc($customer['first_name']) ?> — from here you can view your orders, manage addresses and update your details.</p>
<div class="row g-3 mb-5">
    <div class="col-sm-4"><div class="kpi"><b><?= (int) $orderCount ?></b><span class="muted">Orders placed</span></div></div>
    <div class="col-sm-4"><div class="kpi"><b><?= (int) $wishlistCount ?></b><span class="muted">Wishlist items</span></div></div>
    <div class="col-sm-4"><div class="kpi"><b><?= (int) $cartCount ?></b><span class="muted">Items in cart</span></div></div>
</div>
<div class="d-flex justify-content-between align-items-end mb-3"><h2 class="h3 mb-0">Recent orders</h2><?php if ($orders): ?><a class="link-arrow" href="<?= site_url('my-account/orders') ?>">All orders <i class="bi bi-arrow-right"></i></a><?php endif ?></div>
<?php if ($orders): ?>
    <?= view('account/_orders_table', ['orders' => $orders]) ?>
<?php else: ?>
    <p class="muted">You have not placed an order yet. <a href="<?= site_url('shop') ?>">Browse the shop</a>.</p>
<?php endif ?>
<h2 class="h3 mt-5 mb-3">Default address</h2>
<?php if ($address): ?>
    <div class="address-card is-default" style="max-width:420px"><strong><?= esc($address['label']) ?></strong><br><?= esc($address['full_name']) ?><br><?= esc($address['line1']) ?><?= $address['line2'] ? ', ' . esc($address['line2']) : '' ?><br><?= esc($address['city']) ?>, <?= esc($address['state']) ?> <?= esc($address['postcode']) ?><br><?= esc($address['phone']) ?></div>
<?php else: ?>
    <p class="muted">No address saved yet. <a href="<?= site_url('my-account/addresses') ?>">Add an address</a>.</p>
<?php endif ?>
<?= $this->include('account/_close') ?>
<?= $this->endSection() ?>
