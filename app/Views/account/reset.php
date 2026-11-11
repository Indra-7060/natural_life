<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="shell" style="max-width:560px">
        <?= $this->include('partials/flash') ?>
        <div class="form-card">
            <span class="eyebrow">My account</span>
            <h1 class="h2 mt-3">Choose a new password</h1>
            <form action="<?= site_url('my-account/reset-password/' . $token) ?>" method="post" class="mt-4">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label" for="password">New password</label><input class="form-control" type="password" id="password" name="password" minlength="8" required autocomplete="new-password"><div class="form-text">At least 8 characters.</div></div>
                <div class="mb-3"><label class="form-label" for="password_confirm">Confirm new password</label><input class="form-control" type="password" id="password_confirm" name="password_confirm" minlength="8" required autocomplete="new-password"></div>
                <button class="btn btn-forest btn-lg w-100" type="submit">Save new password</button>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
