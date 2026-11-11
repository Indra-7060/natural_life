<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>My profile</h1></div>
<form class="panel" method="post" action="<?= site_url('admin/profile') ?>" style="max-width:760px">
    <?= csrf_field() ?>
    <div class="panel__body">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="name">Name *</label><input class="form-control" id="name" name="name" value="<?= esc(old('name', $admin['name']), 'attr') ?>" required></div>
            <div class="col-md-6"><label class="form-label" for="email">Email *</label><input class="form-control" type="email" id="email" name="email" value="<?= esc(old('email', $admin['email']), 'attr') ?>" required></div>
            <div class="col-12"><div class="form-section"><?= $admin['must_change_password'] ? 'Set a new password (required)' : 'Change password' ?></div></div>
            <div class="col-md-4"><label class="form-label" for="current_password">Current password</label><input class="form-control" type="password" id="current_password" name="current_password" autocomplete="current-password"></div>
            <div class="col-md-4"><label class="form-label" for="new_password">New password</label><input class="form-control" type="password" id="new_password" name="new_password" autocomplete="new-password"><div class="form-text">At least 10 characters.</div></div>
            <div class="col-md-4"><label class="form-label" for="new_password_confirm">Confirm new password</label><input class="form-control" type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password"></div>
        </div>
    </div>
    <div class="form-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Save</button></div>
</form>
<?= $this->endSection() ?>
