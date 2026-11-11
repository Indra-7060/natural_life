<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Newsletter subscribers</h1><a class="btn btn-primary" href="<?= site_url('admin/subscribers/export') ?>"><i class="bi bi-download"></i> Export active (CSV)</a></div>
<p class="a-intro"><?= (int) $activeCount ?> active subscriber(s). Export the list to use it with your e-mail marketing service.</p>
<div class="panel">
    <form class="filters-bar" method="get" action="<?= site_url('admin/subscribers') ?>">
        <input class="form-control grow" type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Search by email…" aria-label="Search subscribers">
        <button class="btn btn-light" type="submit"><i class="bi bi-search"></i> Search</button>
        <?php if ($q !== ''): ?><a class="btn btn-light" href="<?= site_url('admin/subscribers') ?>">Clear</a><?php endif ?>
    </form>
    <?php if (! $rows): ?><div class="empty"><i class="bi bi-send"></i>No subscribers yet.</div><?php else: ?>
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th>Email</th><th>Subscribed</th><th>Source</th><th>Active</th><th class="actions"></th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?><tr>
            <td class="fw-semibold"><?= esc($r['email']) ?></td><td><?= date('j M Y', strtotime((string) $r['created_at'])) ?></td><td><?= esc((string) $r['source']) ?: '—' ?></td>
            <td><form method="post" action="<?= site_url('admin/subscribers/' . $r['id'] . '/toggle') ?>"><?= csrf_field() ?><button class="switch-btn" type="submit" aria-label="Toggle subscription" aria-pressed="<?= $r['is_active'] ? 'true' : 'false' ?>"><span class="switch <?= $r['is_active'] ? 'on' : '' ?>"></span></button></form></td>
            <td class="actions"><form method="post" action="<?= site_url('admin/subscribers/' . $r['id'] . '/delete') ?>" data-confirm="Remove <?= esc($r['email'], 'attr') ?> from the list?" data-confirm-label="Remove"><?= csrf_field() ?><button class="btn-icon danger" type="submit" aria-label="Remove"><i class="bi bi-trash"></i></button></form></td>
        </tr><?php endforeach ?></tbody>
    </table></div>
    <?= $pager->links('admin', 'admin') ?>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
