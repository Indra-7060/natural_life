<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="shell" style="max-width:560px">
        <?= $this->include('partials/flash') ?>
        <div class="form-card">
            <span class="eyebrow">My account</span>
            <h1 class="h2 mt-3">Reset your password</h1>
            <p class="muted">Enter your email address and we will send you a link to create a new password.</p>
            <form action="<?= site_url('my-account/forgot-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required autocomplete="email"></div>
                <button class="btn btn-forest btn-lg w-100" type="submit">Email reset link</button>
            </form>
            <p class="small mt-3 mb-0"><a href="<?= site_url('my-account/login') ?>">Back to sign in</a></p>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
