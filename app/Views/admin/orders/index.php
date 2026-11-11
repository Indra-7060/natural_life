<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php use App\Models\OrderModel; ?>
<div class="a-head"><h1>Orders</h1></div>
<div class="panel">
    <form class="filters-bar" method="get" action="<?= site_url('admin/orders') ?>">
        <input class="form-control grow" type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Order number, name, email or phone…" aria-label="Search orders">
        <select class="form-select" name="status" aria-label="Order status"><option value="">Any status</option><?php foreach (OrderModel::STATUSES as $k => $l): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select>
        <select class="form-select" name="payment" aria-label="Payment status"><option value="">Any payment</option><?php foreach (OrderModel::PAYMENT_STATUSES as $k => $l): ?><option value="<?= $k ?>" <?= $payment === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select>
        <input class="form-control" type="date" name="from" value="<?= esc($from, 'attr') ?>" aria-label="From date">
        <input class="form-control" type="date" name="to" value="<?= esc($to, 'attr') ?>" aria-label="To date">
        <button class="btn btn-light" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        <?php if ($q !== '' || $status || $payment || $from || $to): ?><a class="btn btn-light" href="<?= site_url('admin/orders') ?>">Clear</a><?php endif ?>
    </form>
    <?php if (! $rows): ?><div class="empty"><i class="bi bi-receipt"></i>No orders found.</div><?php else: ?>
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Status</th><th>Payment</th><th class="text-end">Total</th><th class="actions"></th></tr></thead>
        <tbody><?php foreach ($rows as $o): ?><tr>
            <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/orders/' . $o['id']) ?>"><?= esc($o['order_number']) ?></a></td>
            <td><?= date('j M Y, g:i a', strtotime((string) $o['created_at'])) ?></td>
            <td><?= esc($o['shipping_name']) ?><br><small class="text-secondary"><?= esc($o['email']) ?> · <?= esc($o['shipping_city']) ?></small></td>
            <td><?= status_badge($o['status']) ?></td><td><?= status_badge($o['payment_status']) ?></td>
            <td class="text-end fw-semibold"><?= money($o['grand_total']) ?></td>
            <td class="actions"><a class="btn btn-light btn-sm" href="<?= site_url('admin/orders/' . $o['id']) ?>">Open</a></td>
        </tr><?php endforeach ?></tbody>
    </table></div>
    <?= $pager->links('admin', 'admin') ?>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
