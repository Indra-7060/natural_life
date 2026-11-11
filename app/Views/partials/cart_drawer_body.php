<?php /** Mini-cart drawer contents; re-rendered by every cart AJAX call. Expects $lines, $totals. */ ?>
<?php if (! $lines): ?>
    <div class="empty-state">
        <i class="bi bi-bag"></i>
        <h2>Your cart is empty</h2>
        <p>Start with our Low GI staples.</p>
        <a class="btn btn-forest" href="<?= site_url('shop') ?>">Shop all products</a>
    </div>
<?php else: ?>
    <?php if ($totals['free_threshold'] > 0): $pct = min(100, (int) round($totals['subtotal'] / $totals['free_threshold'] * 100)); ?>
        <div class="ship-meter">
            <?php if ($totals['free_remaining'] > 0): ?>
                Add <strong><?= money($totals['free_remaining']) ?></strong> more for free shipping.
            <?php else: ?>
                <i class="bi bi-check2-circle me-1"></i> You have unlocked <strong>free shipping</strong>.
            <?php endif ?>
            <div class="ship-meter__bar"><i style="width: <?= $pct ?>%"></i></div>
        </div>
    <?php endif ?>
    <div class="drawer-lines">
        <?php foreach ($lines as $l): ?>
            <div class="line <?= $l['available'] ? '' : 'line--off' ?>">
                <a class="line__img" href="<?= site_url('product/' . $l['slug']) ?>"><img src="<?= media_url($l['image'], 'sm') ?>" alt="" width="78" height="78"></a>
                <div>
                    <a class="line__name" href="<?= site_url('product/' . $l['slug']) ?>"><?= esc($l['name']) ?></a>
                    <div class="line__meta"><?= $l['variation_label'] ? esc($l['variation_label']) . ' · ' : '' ?><?= money($l['unit_price']) ?></div>
                    <?php if (! $l['available']): ?><div class="field-error">No longer available</div><?php endif ?>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="qty" data-qty data-item="<?= $l['id'] ?>">
                            <button type="button" data-qty-step="-1" aria-label="Decrease quantity"><i class="bi bi-dash"></i></button>
                            <input type="number" value="<?= $l['quantity'] ?>" min="1" max="20" aria-label="Quantity for <?= esc($l['name'], 'attr') ?>" inputmode="numeric">
                            <button type="button" data-qty-step="1" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
                        </div>
                        <button class="line__remove" type="button" data-cart-remove="<?= $l['id'] ?>">Remove</button>
                    </div>
                </div>
                <div class="fw-semibold"><?= money($l['line_total']) ?></div>
            </div>
        <?php endforeach ?>
    </div>
    <div class="drawer-foot">
        <dl class="totals mb-3">
            <div><dt>Subtotal</dt><dd><?= money($totals['subtotal']) ?></dd></div>
            <?php if ($totals['discount'] > 0): ?><div><dt>Discount (<?= esc($totals['coupon_code']) ?>)</dt><dd>−<?= money($totals['discount']) ?></dd></div><?php endif ?>
            <div><dt>Shipping</dt><dd><?= $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' ?></dd></div>
            <div class="totals__grand"><dt>Total</dt><dd><?= money($totals['total']) ?></dd></div>
        </dl>
        <div class="d-grid gap-2">
            <a class="btn btn-forest btn-lg" href="<?= site_url('checkout') ?>">Checkout <i class="bi bi-arrow-right"></i></a>
            <a class="btn btn-line" href="<?= site_url('cart') ?>">View cart</a>
        </div>
    </div>
<?php endif ?>
