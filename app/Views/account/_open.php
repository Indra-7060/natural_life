<?php
/** Opens the account area: hero, side navigation, flash. Closed by account/_close. */
$links = ['dashboard' => ['my-account', 'Overview', 'grid'], 'orders' => ['my-account/orders', 'Orders', 'box-seam'], 'addresses' => ['my-account/addresses', 'Addresses', 'geo-alt'], 'profile' => ['my-account/profile', 'Account details', 'person']];
$current = $accountView === 'order' ? 'orders' : $accountView;
?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <h1><?= esc($accountTitle) ?></h1>
    </div>
</section>
<section class="section--tight">
    <div class="shell account-layout">
        <ul class="account-nav" aria-label="Account">
            <?php foreach ($links as $key => [$path, $label, $icon]): ?>
                <li><a class="<?= $current === $key ? 'is-active' : '' ?>" href="<?= site_url($path) ?>" <?= $current === $key ? 'aria-current="page"' : '' ?>><i class="bi bi-<?= $icon ?>"></i> <?= $label ?></a></li>
            <?php endforeach ?>
            <li><a href="<?= site_url('wishlist') ?>"><i class="bi bi-heart"></i> Wishlist</a></li>
            <li><a href="<?= site_url('my-account/logout') ?>"><i class="bi bi-box-arrow-right"></i> Sign out</a></li>
        </ul>
        <div>
            <?= $this->include('partials/flash') ?>
