<?php
/**
 * Price, pack-size selector, quantity and add-to-cart. Shared by the product page and quick view.
 * Expects $product (with variations) and $inWishlist. Selecting a pack updates price, stock and
 * the hidden variation_id (see initBuyBoxes in site.js); the server re-validates everything.
 */
$variations = array_values(array_filter($product['variations'] ?? [], static fn ($v) => $v['is_active']));
$isVariable = $product['type'] === 'variable' && $variations;
$default    = null;
foreach ($variations as $v) {
    if ($v['stock_status'] === 'in_stock') { $default = $v; break; }
}
$default ??= $variations[0] ?? null;

if ($isVariable) {
    $regular = (float) $default['regular_price'];
    $price   = (float) ($default['sale_price'] ?? $default['regular_price']);
    $inStock = $default['stock_status'] === 'in_stock';
} else {
    $regular = (float) $product['regular_price'];
    $price   = ($product['sale_price'] !== null && (float) $product['sale_price'] < $regular) ? (float) $product['sale_price'] : $regular;
    $inStock = $product['stock_status'] === 'in_stock';
}
$uid = 'bb' . $product['id'] . (isset($compact) ? 'q' : '');
?>
<form action="<?= site_url('cart/add') ?>" method="post" data-add-to-cart data-buybox>
    <?= csrf_field() ?>
    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
    <?php if ($isVariable): ?><input type="hidden" name="variation_id" value="<?= (int) $default['id'] ?>"><?php endif ?>

    <div class="buybox__price">
        <span class="price__now" data-price><?= money($price) ?></span>
        <del class="price__was" data-price-was <?= $regular > $price ? '' : 'hidden' ?>><?= money($regular) ?></del>
        <span class="badge-tag badge-tag--sale" data-price-save <?= $regular > $price ? '' : 'hidden' ?>>Save <?= discount_percent($regular, $price) ?>%</span>
        <small class="ms-auto text-end"><span data-unit-note></span><br>Inclusive of all taxes</small>
    </div>

    <?php if ($isVariable): ?>
        <div class="opt-label"><?= esc($product['variation_label'] ?: 'Pack size') ?> <span data-selected-label><?= esc($default['label']) ?></span></div>
        <div class="opts" role="radiogroup" aria-label="<?= esc($product['variation_label'] ?: 'Pack size', 'attr') ?>">
            <?php foreach ($variations as $v): $vp = (float) ($v['sale_price'] ?? $v['regular_price']); ?>
                <div class="opt">
                    <input type="radio" name="pack_<?= $uid ?>" id="<?= $uid ?>v<?= $v['id'] ?>" <?= $v['id'] === $default['id'] ? 'checked' : '' ?>
                           data-variation="<?= (int) $v['id'] ?>" data-label="<?= esc($v['label'], 'attr') ?>" data-price="<?= $vp ?>" data-regular="<?= (float) $v['regular_price'] ?>" data-stock="<?= $v['stock_status'] ?>">
                    <label for="<?= $uid ?>v<?= $v['id'] ?>"><b><?= esc($v['label']) ?></b><small><?= $v['stock_status'] === 'in_stock' ? money($vp) : 'Sold out' ?></small></label>
                </div>
            <?php endforeach ?>
        </div>
    <?php elseif ($product['pack_label']): ?>
        <div class="opt-label">Pack <span><?= esc($product['pack_label']) ?></span></div>
    <?php endif ?>

    <p class="mb-3"><span class="stock <?= $inStock ? '' : 'stock--out' ?>" data-stock><?= $inStock ? 'In stock' : 'Out of stock' ?></span></p>

    <div class="buy-row">
        <div class="qty qty--lg" data-qty>
            <button type="button" data-qty-step="-1" aria-label="Decrease quantity"><i class="bi bi-dash"></i></button>
            <input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity" inputmode="numeric">
            <button type="button" data-qty-step="1" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
        </div>
        <button class="btn btn-forest btn-lg" type="submit" data-buy-submit <?= $inStock ? '' : 'disabled' ?>><i class="bi bi-bag-plus"></i> <span data-buy-label><?= $inStock ? 'Add to cart' : 'Out of stock' ?></span></button>
        <button class="wish-btn <?= ! empty($inWishlist) ? 'is-on' : '' ?>" type="button" data-wishlist="<?= (int) $product['id'] ?>" aria-pressed="<?= ! empty($inWishlist) ? 'true' : 'false' ?>" aria-label="Save to wishlist"><i class="bi bi-heart"></i></button>
    </div>
</form>
