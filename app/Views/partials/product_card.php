<?php
/**
 * Product card. Expects $p (catalogue row) and $wishlistIds.
 * Simple in-stock products add straight to the cart; variable products open the product page to choose a pack.
 */
$out     = $p['stock_status'] !== 'in_stock';
$onSale  = $p['type'] === 'simple' && $p['sale_price'] !== null && (float) $p['sale_price'] < (float) $p['regular_price'];
$url     = site_url('product/' . $p['slug']);
$wished  = in_array((int) $p['id'], $wishlistIds ?? [], true);
?>
<article class="pcard <?= $out ? 'pcard--out' : '' ?>">
    <a class="pcard__media" href="<?= $url ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= media_url($p['image'], 'md') ?>" alt="" loading="lazy" width="600" height="600">
        <?php if (! empty($p['image_alt'])): ?><img class="pcard__alt" src="<?= media_url($p['image_alt'], 'md') ?>" alt="" loading="lazy" width="600" height="600"><?php endif ?>
    </a>
    <div class="pcard__badges">
        <?php if ($out): ?><span class="badge-tag badge-tag--out">Out of stock</span>
        <?php elseif ($onSale): ?><span class="badge-tag badge-tag--sale">Sale −<?= discount_percent($p['regular_price'], $p['sale_price']) ?>%</span><?php endif ?>
    </div>
    <button class="pcard__wish <?= $wished ? 'is-on' : '' ?>" type="button" data-wishlist="<?= (int) $p['id'] ?>" aria-pressed="<?= $wished ? 'true' : 'false' ?>" aria-label="<?= $wished ? 'Remove from wishlist' : 'Add to wishlist' ?>: <?= esc($p['name'], 'attr') ?>"><i class="bi bi-heart"></i></button>
    <div class="pcard__quick">
        <button class="btn btn-ivory" type="button" data-quick-view="<?= esc($p['slug'], 'attr') ?>"><i class="bi bi-eye"></i> Quick view</button>
    </div>
    <div class="pcard__body">
        <?php if (! empty($p['category_name'])): ?><div class="pcard__cat"><?= esc($p['category_name']) ?></div><?php endif ?>
        <h3 class="pcard__name"><a href="<?= $url ?>"><?= esc($p['name']) ?></a></h3>
        <?php if (! empty($p['tagline'])): ?><p class="pcard__tag"><?= esc($p['tagline']) ?></p><?php endif ?>
        <div class="pcard__foot">
            <div class="price"><?= product_price_html($p) ?></div>
            <?php if ($out): ?>
                <a class="pcard__add" href="<?= $url ?>">View details</a>
            <?php elseif ($p['type'] === 'variable'): ?>
                <a class="pcard__add" href="<?= $url ?>">Choose pack <i class="bi bi-arrow-right"></i></a>
            <?php else: ?>
                <form action="<?= site_url('cart/add') ?>" method="post" data-add-to-cart>
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button class="pcard__add" type="submit"><i class="bi bi-bag-plus"></i> Add to cart</button>
                </form>
            <?php endif ?>
        </div>
    </div>
</article>
