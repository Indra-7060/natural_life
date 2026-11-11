<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $e = $enquiry; $name = trim($e['first_name'] . ' ' . $e['last_name']); ?>
<div class="a-head">
    <h1>Enquiry from <?= esc($name) ?></h1>
    <form method="post" action="<?= site_url('admin/enquiries/' . $e['id'] . '/delete') ?>" data-confirm="Delete this enquiry permanently?"><?= csrf_field() ?><button class="btn btn-light" type="submit"><i class="bi bi-trash"></i> Delete</button></form>
</div>
<div class="row g-4">
    <div class="col-lg-8"><div class="panel">
        <div class="panel__head"><h2><?= esc($e['subject'] ?: 'No subject') ?></h2><span class="text-secondary small"><?= date('j M Y, g:i a', strtotime((string) $e['created_at'])) ?></span></div>
        <div class="panel__body"><p style="white-space:pre-line;font-size:1rem;line-height:1.7" class="mb-4"><?= esc($e['message']) ?></p>
            <a class="btn btn-primary" href="mailto:<?= esc($e['email'], 'attr') ?>?subject=<?= rawurlencode('Re: ' . ($e['subject'] ?: 'Your enquiry to Sugarlif')) ?>"><i class="bi bi-reply"></i> Reply by email</a>
            <?php if ($e['phone']): ?><a class="btn btn-light" href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $e['phone']), 'attr') ?>"><i class="bi bi-telephone"></i> <?= esc($e['phone']) ?></a><?php endif ?>
        </div>
    </div></div>
    <div class="col-lg-4">
        <div class="panel"><div class="panel__head"><h2>Sender</h2></div><div class="panel__body"><dl class="dl" style="grid-template-columns:70px 1fr"><dt>Name</dt><dd><?= esc($name) ?></dd><dt>Email</dt><dd><a href="mailto:<?= esc($e['email'], 'attr') ?>"><?= esc($e['email']) ?></a></dd><dt>Phone</dt><dd><?= esc((string) $e['phone']) ?: '—' ?></dd></dl></div></div>
        <form class="panel" method="post" action="<?= site_url('admin/enquiries/' . $e['id']) ?>"><?= csrf_field() ?>
            <div class="panel__head"><h2>Follow-up</h2></div>
            <div class="panel__body">
                <label class="form-label" for="status">Status</label>
                <select class="form-select mb-3" id="status" name="status"><?php foreach ($statuses as $k => $l): ?><option value="<?= $k ?>" <?= $e['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select>
                <label class="form-label" for="admin_note">Internal note</label>
                <textarea class="form-control mb-3" id="admin_note" name="admin_note" rows="4"><?= esc((string) $e['admin_note']) ?></textarea>
                <button class="btn btn-primary w-100" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
