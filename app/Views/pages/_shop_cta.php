<?php if ($shop): ?>
<section class="section--tight bg-cream">
    <div class="shell">
        <?php if (! empty($product)): ?>
            <div class="cta-product">
                <img src="<?= media_url($product['image'], 'sm') ?>" alt="<?= esc($product['name'], 'attr') ?>" loading="lazy" width="160" height="160">
                <div><span class="eyebrow"><?= esc($shop['eyebrow']) ?></span><h2 class="mt-2 mb-1"><?= esc($shop['heading']) ?></h2><p class="mb-0"><?= esc($product['name']) ?> · <?= strip_tags(product_price_html($product)) ?></p></div>
                <a class="btn btn-forest btn-lg" href="<?= menu_href($shop['cta_url']) ?>"><?= esc($shop['cta_label']) ?> <i class="bi bi-arrow-right"></i></a>
            </div>
        <?php else: ?>
            <div class="cta-band">
                <div><span class="eyebrow"><?= esc($shop['eyebrow']) ?></span><h2 class="mt-2 mb-1"><?= esc($shop['heading']) ?></h2><?php if ($shop['body']): ?><div class="muted"><?= rich($shop['body']) ?></div><?php endif ?></div>
                <?php if ($shop['cta_label']): ?><a class="btn btn-forest btn-lg" href="<?= menu_href($shop['cta_url']) ?>"><?= esc($shop['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
            </div>
        <?php endif ?>
    </div>
</section>
<?php endif ?>
