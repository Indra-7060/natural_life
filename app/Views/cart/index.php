<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <h1>Your cart</h1>
    </div>
</section>
<section class="section--tight" data-cart-page>
    <div class="shell">
        <?php if (! $lines): ?>
            <div class="empty-state">
                <i class="bi bi-bag"></i>
                <h2>Your cart is empty</h2>
                <p>Start with our Low GI staples.</p>
                <a class="btn btn-forest" href="<?= site_url('shop') ?>">Shop all products</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-table">
                    <?php foreach ($lines as $l): ?>
                        <div class="line <?= $l['available'] ? '' : 'line--off' ?>">
                            <a class="line__img" href="<?= site_url('product/' . $l['slug']) ?>"><img src="<?= media_url($l['image'], 'sm') ?>" alt="" width="96" height="96"></a>
                            <div>
                                <a class="line__name" href="<?= site_url('product/' . $l['slug']) ?>"><?= esc($l['name']) ?></a>
                                <div class="line__meta"><?= $l['variation_label'] ? esc($l['variation_label']) . ' · ' : '' ?><?= money($l['unit_price']) ?> each</div>
                                <?php if (! $l['available']): ?><div class="field-error">No longer available — please remove this item.</div><?php endif ?>
                                <button class="line__remove mt-2" type="button" data-cart-remove="<?= $l['id'] ?>">Remove</button>
                            </div>
                            <div class="qty" data-qty data-item="<?= $l['id'] ?>">
                                <button type="button" data-qty-step="-1" aria-label="Decrease quantity"><i class="bi bi-dash"></i></button>
                                <input type="number" value="<?= $l['quantity'] ?>" min="1" max="20" aria-label="Quantity for <?= esc($l['name'], 'attr') ?>" inputmode="numeric">
                                <button type="button" data-qty-step="1" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
                            </div>
                            <div class="line__total"><?= money($l['line_total']) ?></div>
                        </div>
                    <?php endforeach ?>
                    <a class="link-arrow mt-4" href="<?= site_url('shop') ?>"><i class="bi bi-arrow-left"></i> Continue shopping</a>
                </div>

                <aside class="summary" aria-label="Order summary">
                    <h2>Order summary</h2>
                    <?php if ($totals['free_threshold'] > 0): ?>
                        <div class="ship-meter mb-3" style="border-radius:var(--radius)">
                            <?php if ($totals['free_remaining'] > 0): ?>Add <strong><?= money($totals['free_remaining']) ?></strong> more for free shipping.<?php else: ?><i class="bi bi-check2-circle me-1"></i> You have unlocked <strong>free shipping</strong>.<?php endif ?>
                            <div class="ship-meter__bar"><i style="width: <?= min(100, (int) round($totals['subtotal'] / $totals['free_threshold'] * 100)) ?>%"></i></div>
                        </div>
                    <?php endif ?>
                    <?= view('partials/totals', ['totals' => $totals]) ?>
                    <?php if ($totals['coupon_code']): ?>
                        <form action="<?= site_url('cart/coupon/remove') ?>" method="post" class="my-3"><?= csrf_field() ?>
                            <span class="chip">Coupon <?= esc($totals['coupon_code']) ?> applied</span> <button class="line__remove ms-2" type="submit">Remove</button>
                        </form>
                    <?php else: ?>
                        <form class="coupon-form" action="<?= site_url('cart/coupon') ?>" method="post"><?= csrf_field() ?>
                            <label class="visually-hidden" for="couponCode">Coupon code</label>
                            <input class="form-control" id="couponCode" name="code" placeholder="Coupon code" maxlength="40" required>
                            <button class="btn btn-line btn-sm" type="submit">Apply</button>
                        </form>
                    <?php endif ?>
                    <div class="d-grid mt-3"><a class="btn btn-forest btn-lg" href="<?= site_url('checkout') ?>">Proceed to checkout <i class="bi bi-arrow-right"></i></a></div>
                    <p class="muted small mt-3 mb-0"><i class="bi bi-shield-lock me-1"></i> Secure online payment via CCAvenue.</p>
                </aside>
            </div>
        <?php endif ?>
    </div>
</section>
<?= $this->endSection() ?>
