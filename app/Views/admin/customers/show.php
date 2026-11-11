<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head">
    <h1><?= esc(trim($customer['first_name'] . ' ' . $customer['last_name'])) ?></h1>
    <form method="post" action="<?= site_url('admin/customers/' . $customer['id'] . '/toggle') ?>" <?= $customer['is_active'] ? 'data-confirm="Disable this account? The customer will not be able to sign in." data-confirm-label="Disable"' : '' ?>><?= csrf_field() ?>
        <button class="btn btn-light" type="submit"><?= $customer['is_active'] ? '<i class="bi bi-slash-circle"></i> Disable account' : '<i class="bi bi-check-circle"></i> Enable account' ?></button></form>
</div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel"><div class="panel__head"><h2>Details</h2></div><div class="panel__body">
            <dl class="dl" style="grid-template-columns:100px 1fr">
                <dt>Email</dt><dd><a href="mailto:<?= esc($customer['email'], 'attr') ?>"><?= esc($customer['email']) ?></a></dd>
                <dt>Phone</dt><dd><?= esc((string) $customer['phone']) ?: '—' ?></dd>
                <dt>Registered</dt><dd><?= date('j M Y', strtotime((string) $customer['created_at'])) ?></dd>
                <dt>Last sign-in</dt><dd><?= $customer['last_login_at'] ? date('j M Y, g:i a', strtotime((string) $customer['last_login_at'])) : '—' ?></dd>
                <dt>Status</dt><dd><?= $customer['is_active'] ? 'Active' : 'Disabled' ?></dd>
            </dl></div></div>
        <div class="panel"><div class="panel__head"><h2>Addresses</h2></div><div class="panel__body">
            <?php if (! $addresses): ?><p class="text-secondary mb-0">No saved addresses.</p><?php endif ?>
            <?php foreach ($addresses as $a): ?><address class="mb-3"><strong><?= esc($a['label']) ?></strong><?= $a['is_default'] ? ' <span class="badge-status badge-status--primary">Default</span>' : '' ?><br><?= esc($a['full_name']) ?><br><?= esc($a['line1']) ?><?= $a['line2'] ? ', ' . esc($a['line2']) : '' ?><br><?= esc($a['city']) ?>, <?= esc($a['state']) ?> <?= esc($a['postcode']) ?><br><?= esc($a['phone']) ?></address><?php endforeach ?>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="panel"><div class="panel__head"><h2>Order history</h2><span class="text-secondary small"><?= count($orders) ?> order(s)</span></div>
            <?php if (! $orders): ?><div class="empty"><i class="bi bi-receipt"></i>No orders yet.</div><?php else: ?>
            <div class="table-scroll"><table class="a-table"><thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Payment</th><th class="text-end">Total</th></tr></thead><tbody>
                <?php foreach ($orders as $o): ?><tr><td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/orders/' . $o['id']) ?>"><?= esc($o['order_number']) ?></a></td><td><?= date('j M Y', strtotime((string) $o['created_at'])) ?></td><td><?= status_badge($o['status']) ?></td><td><?= status_badge($o['payment_status']) ?></td><td class="text-end"><?= money($o['grand_total']) ?></td></tr><?php endforeach ?>
            </tbody></table></div><?php endif ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
