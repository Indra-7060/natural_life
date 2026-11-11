<?php $img = $product['images'][0]['path'] ?? null; ?>
<div class="quick">
    <div class="quick__media"><img src="<?= media_url($img, 'lg') ?>" alt="<?= esc($product['name'], 'attr') ?>" width="900" height="900"></div>
    <div class="quick__body buybox">
        <?php if ($product['category_name']): ?><span class="eyebrow"><?= esc($product['category_name']) ?></span><?php endif ?>
        <h2 class="mt-2"><?= esc($product['name']) ?></h2>
        <?php if ($product['tagline']): ?><p class="buybox__tag"><?= esc($product['tagline']) ?></p><?php endif ?>
        <?= view('partials/buybox', ['product' => $product, 'inWishlist' => in_array((int) $product['id'], $wishlistIds, true), 'compact' => true]) ?>
        <a class="link-arrow mt-4" href="<?= site_url('product/' . $product['slug']) ?>">View full details <i class="bi bi-arrow-right"></i></a>
    </div>
</div>
