<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <h1>Your wishlist</h1>
        <?php if (! $customer && $products): ?><p class="lede"><a href="<?= site_url('my-account/login') ?>">Sign in</a> to keep your wishlist across devices.</p><?php endif ?>
    </div>
</section>
<section class="section--tight" data-wishlist-page>
    <div class="shell">
        <?php if ($products): ?>
            <div class="product-grid product-grid--4">
                <?php foreach ($products as $p): ?><div data-wishlist-item><?= view('partials/product_card', ['p' => $p, 'wishlistIds' => $wishlistIds]) ?></div><?php endforeach ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-heart"></i><h2>Your wishlist is empty</h2><p>You have not added any products to your wishlist.</p><a class="btn btn-forest" href="<?= site_url('shop') ?>">Go to shop</a></div>
        <?php endif ?>
    </div>
</section>
<?= $this->endSection() ?>
