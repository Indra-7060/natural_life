<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · Sugarlif Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&family=Fraunces:opsz,wght@9..144,500&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset_url('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login">
    <div class="login__art">
        <img src="<?= media_url(setting('logo_light'), 'original') ?>" alt="Sugarlif" style="height:44px;width:auto;align-self:flex-start">
        <div><h1>Manage the Sugarlif website</h1><p class="mt-3" style="max-width:40ch;opacity:.8">Products, orders, pages, blog and settings — all in one place.</p></div>
        <small style="opacity:.6">© <?= date('Y') ?> <?= esc(setting('company_name', 'Natural Life Speciality Pvt. Ltd.')) ?></small>
    </div>
    <div class="login__form">
        <div>
            <h2 class="mb-1">Sign in</h2>
            <p class="text-secondary mb-4">Administrator access only.</p>
            <?= view('admin/partials/flash') ?>
            <form method="post" action="<?= site_url('admin/login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control form-control-lg" type="email" id="email" name="email" value="<?= esc(old('email', ''), 'attr') ?>" required autofocus autocomplete="username"></div>
                <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control form-control-lg" type="password" id="password" name="password" required autocomplete="current-password"></div>
                <button class="btn btn-primary btn-lg w-100" type="submit">Sign in</button>
            </form>
            <p class="small text-secondary mt-4 mb-0"><a href="<?= site_url('/') ?>">← Back to the website</a></p>
        </div>
    </div>
</div>
</body>
</html>
