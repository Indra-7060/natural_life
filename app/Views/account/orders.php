<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?= $this->include('account/_open') ?>
<?php if ($orders): ?>
    <?= view('account/_orders_table', ['orders' => $orders]) ?>
    <?= $pager->links('orders', 'site') ?>
<?php else: ?>
    <div class="empty-state"><i class="bi bi-box-seam"></i><h2>No orders yet</h2><p>When you place an order it will appear here.</p><a class="btn btn-forest" href="<?= site_url('shop') ?>">Shop all products</a></div>
<?php endif ?>
<?= $this->include('account/_close') ?>
<?= $this->endSection() ?>
