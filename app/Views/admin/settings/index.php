<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Settings</h1></div>
<div class="a-tabs" role="tablist">
    <?php foreach ($groups as $key => [$label]): ?><a class="<?= $group === $key ? 'is-active' : '' ?>" href="<?= site_url('admin/settings/' . $key) ?>" <?= $group === $key ? 'aria-current="page"' : '' ?>><?= esc($label) ?></a><?php endforeach ?>
</div>
<p class="a-intro"><?= esc($groups[$group][1]) ?>.</p>

<form class="panel" method="post" action="<?= site_url('admin/settings/' . $group) ?>" enctype="multipart/form-data" data-guard style="max-width:920px">
    <?= csrf_field() ?>
    <div class="panel__body"><div class="row g-3">
        <?php foreach ($rows as $r): $val = old($r['key']) ?? $r['value']; ?>
            <div class="col-md-<?= in_array($r['type'], ['textarea'], true) ? 12 : ($r['type'] === 'number' ? 4 : 6) ?>">
                <?php if ($r['type'] === 'image'): ?>
                    <?= view('admin/partials/image_field', ['name' => $r['key'], 'value' => $val, 'label' => $r['label'], 'help' => $r['help']]) ?>
                <?php elseif ($r['type'] === 'textarea'): ?>
                    <label class="form-label" for="s_<?= $r['key'] ?>"><?= esc($r['label']) ?></label>
                    <textarea class="form-control" id="s_<?= $r['key'] ?>" name="<?= $r['key'] ?>" rows="3"><?= esc((string) $val) ?></textarea>
                <?php else: ?>
                    <label class="form-label" for="s_<?= $r['key'] ?>"><?= esc($r['label']) ?></label>
                    <input class="form-control" type="<?= $r['type'] === 'number' ? 'number' : 'text' ?>" <?= $r['type'] === 'number' ? 'min="0" step="0.01"' : '' ?> id="s_<?= $r['key'] ?>" name="<?= $r['key'] ?>" value="<?= esc((string) $val, 'attr') ?>">
                <?php endif ?>
                <?php if ($r['help'] && $r['type'] !== 'image'): ?><div class="form-text"><?= esc($r['help']) ?></div><?php endif ?>
            </div>
        <?php endforeach ?>
    </div></div>
    <div class="form-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Save settings</button></div>
</form>

<?php if ($group === 'shop'): ?>
<div class="panel mt-4" style="max-width:920px">
    <div class="panel__head"><h2>Payment gateway &amp; e-mail</h2></div>
    <div class="panel__body">
        <dl class="dl" style="grid-template-columns:200px 1fr">
            <dt>CCAvenue</dt><dd><?= $gateway['ready'] ? '<span class="badge-status badge-status--success">Configured</span> · ' . esc(ucfirst($gateway['mode'])) . ' mode' : '<span class="badge-status badge-status--warning">Not configured</span> — orders are saved as “pending payment” and are never marked paid automatically.' ?></dd>
            <dt>Outgoing e-mail (SMTP)</dt><dd><?= $smtpReady ? '<span class="badge-status badge-status--success">Configured</span>' : '<span class="badge-status badge-status--warning">Not configured</span> — e-mails are written to <code>writable/emails/</code> instead of being sent.' ?></dd>
        </dl>
        <p class="form-text mb-0 mt-3">For security, gateway and mail credentials are kept in the server’s <code>.env</code> file and cannot be viewed or changed here. See <code>docs/SETUP.md</code>.</p>
    </div>
</div>
<?php endif ?>
<?= $this->endSection() ?>
