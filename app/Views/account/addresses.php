<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?= $this->include('account/_open') ?>
<?php $e = $editing ?? []; $v = static fn (string $f, string $d = '') => esc(old($f, $e[$f] ?? $d), 'attr'); ?>
<?php if ($addresses): ?>
    <div class="row g-3 mb-5">
        <?php foreach ($addresses as $a): ?>
            <div class="col-md-6">
                <div class="address-card <?= $a['is_default'] ? 'is-default' : '' ?>">
                    <strong><?= esc($a['label']) ?></strong> <?= $a['is_default'] ? '<span class="badge-status badge-status--primary ms-1">Default</span>' : '' ?><br>
                    <?= esc($a['full_name']) ?><br><?= esc($a['line1']) ?><?= $a['line2'] ? ', ' . esc($a['line2']) : '' ?><br><?= esc($a['city']) ?>, <?= esc($a['state']) ?> <?= esc($a['postcode']) ?><br><?= esc($a['phone']) ?>
                    <div class="d-flex gap-3 mt-3">
                        <a href="<?= site_url('my-account/addresses') ?>?edit=<?= $a['id'] ?>#addressForm">Edit</a>
                        <form action="<?= site_url('my-account/addresses/delete/' . $a['id']) ?>" method="post" onsubmit="return confirm('Remove this address?')"><?= csrf_field() ?><button class="line__remove" type="submit">Remove</button></form>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
<div class="form-card" id="addressForm">
    <h2 class="h3 mb-4"><?= $e ? 'Edit address' : 'Add a new address' ?></h2>
    <form action="<?= site_url('my-account/addresses') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($e['id'] ?? 0) ?>">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="label">Label *</label><input class="form-control" id="label" name="label" value="<?= $v('label', 'Home') ?>" required maxlength="40"></div>
            <div class="col-md-4"><label class="form-label" for="full_name">Full name *</label><input class="form-control" id="full_name" name="full_name" value="<?= $v('full_name', trim($customer['first_name'] . ' ' . $customer['last_name'])) ?>" required></div>
            <div class="col-md-4"><label class="form-label" for="phone">Mobile number *</label><input class="form-control" id="phone" name="phone" type="tel" value="<?= $v('phone', (string) $customer['phone']) ?>" required></div>
            <div class="col-12"><label class="form-label" for="line1">Address *</label><input class="form-control" id="line1" name="line1" value="<?= $v('line1') ?>" required></div>
            <div class="col-12"><label class="form-label" for="line2">Area / landmark</label><input class="form-control" id="line2" name="line2" value="<?= $v('line2') ?>"></div>
            <div class="col-md-4"><label class="form-label" for="city">City *</label><input class="form-control" id="city" name="city" value="<?= $v('city') ?>" required></div>
            <div class="col-md-4"><label class="form-label" for="state">State *</label><select class="form-select" id="state" name="state" required><option value="">Select state</option>
                <?php $cur = old('state', $e['state'] ?? ''); foreach (indian_states() as $st): ?><option <?= $cur === $st ? 'selected' : '' ?>><?= esc($st) ?></option><?php endforeach ?></select></div>
            <div class="col-md-4"><label class="form-label" for="postcode">PIN code *</label><input class="form-control" id="postcode" name="postcode" value="<?= $v('postcode') ?>" inputmode="numeric" maxlength="6" required></div>
            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" <?= ! empty($e['is_default']) ? 'checked' : '' ?>><label class="form-check-label" for="is_default">Make this my default address</label></div></div>
        </div>
        <div class="d-flex gap-2 mt-4"><button class="btn btn-forest" type="submit">Save address</button><?php if ($e): ?><a class="btn btn-line" href="<?= site_url('my-account/addresses') ?>">Cancel</a><?php endif ?></div>
    </form>
</div>
<?= $this->include('account/_close') ?>
<?= $this->endSection() ?>
