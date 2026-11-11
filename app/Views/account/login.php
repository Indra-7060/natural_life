<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php $reg = session('form') === 'register'; ?>
<section class="section--tight">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <div class="mt-4"><?= $this->include('partials/flash') ?></div>
        <div class="auth">
            <div class="auth__pane">
                <span class="eyebrow">Welcome back</span>
                <h2 class="mt-3">Sign in</h2>
                <p class="muted">Track orders, save addresses and keep your wishlist.</p>
                <form action="<?= site_url('my-account/login') ?>" method="post" class="mt-4">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label" for="loginEmail">Email</label><input class="form-control" type="email" id="loginEmail" name="email" value="<?= $reg ? '' : esc(old('email', ''), 'attr') ?>" required autocomplete="email"></div>
                    <div class="mb-2"><label class="form-label" for="loginPassword">Password</label><input class="form-control" type="password" id="loginPassword" name="password" required autocomplete="current-password"></div>
                    <p class="small"><a href="<?= site_url('my-account/forgot-password') ?>">Forgot password?</a></p>
                    <button class="btn btn-forest btn-lg w-100" type="submit">Sign in</button>
                </form>
            </div>
            <div class="auth__pane" id="register">
                <span class="eyebrow">New here?</span>
                <h2 class="mt-3">Create an account</h2>
                <p class="muted">It takes less than a minute.</p>
                <form action="<?= site_url('my-account/register') ?>" method="post" class="mt-4">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label" for="regFirst">First name</label><input class="form-control" id="regFirst" name="first_name" value="<?= esc(old('first_name', ''), 'attr') ?>" required autocomplete="given-name"></div>
                        <div class="col-sm-6"><label class="form-label" for="regLast">Last name</label><input class="form-control" id="regLast" name="last_name" value="<?= esc(old('last_name', ''), 'attr') ?>" required autocomplete="family-name"></div>
                        <div class="col-12"><label class="form-label" for="regEmail">Email</label><input class="form-control" type="email" id="regEmail" name="reg_email" value="<?= esc(old('reg_email', ''), 'attr') ?>" required autocomplete="email"></div>
                        <div class="col-sm-6"><label class="form-label" for="regPass">Password</label><input class="form-control" type="password" id="regPass" name="reg_password" minlength="8" required autocomplete="new-password"><div class="form-text">At least 8 characters.</div></div>
                        <div class="col-sm-6"><label class="form-label" for="regPass2">Confirm password</label><input class="form-control" type="password" id="regPass2" name="password_confirm" minlength="8" required autocomplete="new-password"></div>
                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="accept_terms" value="1" id="regTerms" required><label class="form-check-label small" for="regTerms">I accept the <a href="<?= site_url('terms-and-conditions') ?>" target="_blank">Terms of Service</a> and <a href="<?= site_url('privacy-policy') ?>" target="_blank">Privacy Policy</a></label></div></div>
                    </div>
                    <button class="btn btn-line btn-lg w-100 mt-4" type="submit">Create account</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
