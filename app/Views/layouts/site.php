<?php
/**
 * Storefront layout. Expects $seo (from BaseController::seo()), $customer, $cartCount, $wishlistCount.
 */
$siteName = setting('site_name', 'Sugarlif');
$ogImage  = $seo['og_image'] ?: media_url(setting('logo'), 'original');
?>
<!doctype html>
<html lang="en-IN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($seo['title']) ?></title>
    <meta name="description" content="<?= esc($seo['description'], 'attr') ?>">
    <?php if ($seo['noindex']): ?><meta name="robots" content="noindex, follow"><?php else: ?><meta name="robots" content="index, follow, max-image-preview:large"><?php endif ?>
    <link rel="canonical" href="<?= esc($seo['canonical'], 'attr') ?>">
    <meta property="og:site_name" content="<?= esc($siteName, 'attr') ?>">
    <meta property="og:type" content="<?= esc($seo['og_type'], 'attr') ?>">
    <meta property="og:title" content="<?= esc($seo['title'], 'attr') ?>">
    <meta property="og:description" content="<?= esc($seo['description'], 'attr') ?>">
    <meta property="og:url" content="<?= esc($seo['canonical'], 'attr') ?>">
    <meta property="og:image" content="<?= esc($ogImage, 'attr') ?>">
    <meta property="og:locale" content="en_IN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($seo['title'], 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($seo['description'], 'attr') ?>">
    <meta name="twitter:image" content="<?= esc($ogImage, 'attr') ?>">
    <meta name="theme-color" content="#1f3a2b">
    <link rel="icon" href="<?= media_url(setting('favicon'), 'sm') ?>">
    <link rel="apple-touch-icon" href="<?= media_url(setting('favicon'), 'original') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;1,9..144,400&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= asset_url('css/site-v2.css') ?>" rel="stylesheet">
    <?= csrf_meta() ?>
    <?php foreach ($seo['jsonld'] as $block): ?>
    <script type="application/ld+json"><?= json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
    <?php endforeach ?>
</head>
<body data-base="<?= rtrim(site_url(), '/') ?>" data-csrf-name="<?= csrf_token() ?>">
<a class="skip-link" href="#main">Skip to content</a>
<div class="scroll-progress" aria-hidden="true"></div>

<?= $this->include('partials/header') ?>

<main id="main">
    <?php if (session('success') || session('error') || session('info')): ?>
        <div class="shell pt-4">
            <?= $this->include('partials/flash') ?>
        </div>
    <?php endif ?>
    <?= $this->renderSection('content') ?>
</main>

<?= $this->include('partials/footer') ?>

<!-- Cart drawer -->
<div class="offcanvas offcanvas-end cart-drawer" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerTitle">
    <div class="offcanvas-header">
        <h2 class="h4 mb-0" id="cartDrawerTitle">Your cart</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close cart"></button>
    </div>
    <div class="offcanvas-body" id="cartDrawerBody">
        <div class="empty-state"><div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading…</span></div></div>
    </div>
</div>

<!-- Quick view -->
<div class="modal fade" id="quickView" tabindex="-1" aria-label="Quick view" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute end-0 top-0 m-3" style="z-index:5" data-bs-dismiss="modal" aria-label="Close"></button>
            <div id="quickViewBody"></div>
        </div>
    </div>
</div>

<button class="to-top" type="button" data-to-top aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>
<div class="toast-stack" id="toasts" aria-live="polite" aria-atomic="true"></div>

<script src="<?= base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>" defer></script>
<script src="<?= asset_url('js/site.js') ?>" defer></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
