<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Customers</h1></div>
<div class="panel">
    <form class="filters-bar" method="get" action="<?= site_url('admin/customers') ?>">
        <input class="form-control grow" type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Name, email or phone…" aria-label="Search customers">
        <button class="btn btn-light" type="submit"><i class="bi bi-search"></i> Search</button>
        <?php if ($q !== ''): ?><a class="btn btn-light" href="<?= site_url('admin/customers') ?>">Clear</a><?php endif ?>
    </form>
    <?php if (! $rows): ?><div class="empty"><i class="bi bi-people"></i>No customers yet. Accounts appear here when shoppers register.</div><?php else: ?>
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th>Customer</th><th>Phone</th><th>Registered</th><th>Orders</th><th class="text-end">Paid total</th><th>Status</th><th class="actions"></th></tr></thead>
        <tbody><?php foreach ($rows as $c): ?><tr>
            <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/customers/' . $c['id']) ?>"><?= esc(trim($c['first_name'] . ' ' . $c['last_name'])) ?></a><br><small class="text-secondary"><?= esc($c['email']) ?></small></td>
            <td><?= esc((string) $c['phone']) ?: '—' ?></td><td><?= date('j M Y', strtotime((string) $c['created_at'])) ?></td>
            <td><?= (int) $c['order_count'] ?></td><td class="text-end"><?= money($c['spent']) ?></td>
            <td><?= $c['is_active'] ? '<span class="badge-status badge-status--success">Active</span>' : '<span class="badge-status badge-status--secondary">Disabled</span>' ?></td>
            <td class="actions"><a class="btn btn-light btn-sm" href="<?= site_url('admin/customers/' . $c['id']) ?>">Open</a></td>
        </tr><?php endforeach ?></tbody>
    </table></div>
    <?= $pager->links('admin', 'admin') ?>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
