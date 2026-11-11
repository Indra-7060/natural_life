<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?= $this->include('account/_open') ?>
<div class="form-card">
    <form action="<?= site_url('my-account/profile') ?>" method="post">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="first_name">First name *</label><input class="form-control" id="first_name" name="first_name" value="<?= esc(old('first_name', $customer['first_name']), 'attr') ?>" required></div>
            <div class="col-md-6"><label class="form-label" for="last_name">Last name *</label><input class="form-control" id="last_name" name="last_name" value="<?= esc(old('last_name', $customer['last_name']), 'attr') ?>" required></div>
            <div class="col-md-6"><label class="form-label" for="email">Email *</label><input class="form-control" type="email" id="email" name="email" value="<?= esc(old('email', $customer['email']), 'attr') ?>" required></div>
            <div class="col-md-6"><label class="form-label" for="phone">Mobile number</label><input class="form-control" type="tel" id="phone" name="phone" value="<?= esc(old('phone', (string) $customer['phone']), 'attr') ?>"></div>
        </div>
        <h2 class="h4 mt-5 mb-1">Change password</h2>
        <p class="muted small">Leave these blank to keep your current password.</p>
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="current_password">Current password</label><input class="form-control" type="password" id="current_password" name="current_password" autocomplete="current-password"></div>
            <div class="col-md-4"><label class="form-label" for="new_password">New password</label><input class="form-control" type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password"></div>
            <div class="col-md-4"><label class="form-label" for="new_password_confirm">Confirm new password</label><input class="form-control" type="password" id="new_password_confirm" name="new_password_confirm" minlength="8" autocomplete="new-password"></div>
        </div>
        <button class="btn btn-forest mt-4" type="submit">Save changes</button>
    </form>
</div>
<?= $this->include('account/_close') ?>
<?= $this->endSection() ?>
