<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Enquiries</h1></div>
<div class="panel">
    <form class="filters-bar" method="get" action="<?= site_url('admin/enquiries') ?>">
        <input class="form-control grow" type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Name, email, subject or message…" aria-label="Search enquiries">
        <select class="form-select" name="status" aria-label="Status" data-autosubmit><option value="">Any status</option><?php foreach ($statuses as $k => $l): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select>
        <button class="btn btn-light" type="submit"><i class="bi bi-search"></i> Search</button>
        <?php if ($q !== '' || $status): ?><a class="btn btn-light" href="<?= site_url('admin/enquiries') ?>">Clear</a><?php endif ?>
    </form>
    <?php if (! $rows): ?><div class="empty"><i class="bi bi-envelope"></i>No enquiries found.</div><?php else: ?>
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th>From</th><th>Subject / message</th><th>Received</th><th>Status</th><th class="actions"></th></tr></thead>
        <tbody><?php foreach ($rows as $e): ?><tr>
            <td><a class="<?= $e['status'] === 'new' ? 'fw-bold' : 'fw-semibold' ?> text-decoration-none" href="<?= site_url('admin/enquiries/' . $e['id']) ?>"><?= esc(trim($e['first_name'] . ' ' . $e['last_name'])) ?></a><br><small class="text-secondary"><?= esc($e['email']) ?></small></td>
            <td><?= $e['subject'] ? '<strong>' . esc($e['subject']) . '</strong><br>' : '' ?><span class="text-secondary"><?= esc(plain($e['message'], 90)) ?></span></td>
            <td><?= date('j M Y, g:i a', strtotime((string) $e['created_at'])) ?></td><td><?= status_badge($e['status']) ?></td>
            <td class="actions"><a class="btn btn-light btn-sm" href="<?= site_url('admin/enquiries/' . $e['id']) ?>">Open</a></td>
        </tr><?php endforeach ?></tbody>
    </table></div>
    <?= $pager->links('admin', 'admin') ?>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
