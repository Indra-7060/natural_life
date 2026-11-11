<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$images    = $product['images'] ?: [['path' => null, 'alt_text' => $product['name']]];
$out       = $product['stock_status'] !== 'in_stock';
$onSale    = $product['type'] === 'simple' && $product['sale_price'] !== null && (float) $product['sale_price'] < (float) $product['regular_price'];
$threshold = (float) setting('free_shipping_threshold', '500');
$flat      = (float) setting('shipping_flat_rate', '40');
$panels    = [];
if ($product['description'])  { $panels['Description'] = '<div class="prose">' . rich($product['description']) . '</div>'; }
if ($product['ingredients'])  { $panels['Ingredients'] = '<div class="prose"><p>' . esc($product['ingredients']) . '</p></div>'; }
if ($product['nutrition']) {
    $rows = '';
    $lines = preg_split('/\r?\n/', trim($product['nutrition']));
    $cap   = (count($lines) > 1 && ! str_contains($lines[0], ':')) ? array_shift($lines) : null;
    foreach ($lines as $line) {
        [$k, $v] = array_pad(explode(':', $line, 2), 2, '');
        $rows .= '<tr><th scope="row">' . esc(trim($k)) . '</th><td>' . esc(trim($v)) . '</td></tr>';
    }
    $panels['Nutritional facts'] = ($cap ? '<p class="muted small mb-2">' . esc($cap) . '</p>' : '') . '<table class="spec">' . $rows . '</table>';
}
$spec = '';
if ($product['variations']) { $spec .= '<tr><th scope="row">' . esc($product['variation_label'] ?: 'Pack size') . '</th><td>' . esc(implode(', ', array_column(array_filter($product['variations'], static fn ($v) => $v['is_active']), 'label'))) . '</td></tr>'; }
elseif ($product['pack_label']) { $spec .= '<tr><th scope="row">Pack</th><td>' . esc($product['pack_label']) . '</td></tr>'; }
foreach ($product['attributes'] as $a) { $spec .= '<tr><th scope="row">' . esc($a['name']) . '</th><td>' . nl2br(esc($a['value'])) . '</td></tr>'; }
if ($product['category_name']) { $spec .= '<tr><th scope="row">Category</th><td>' . esc($product['category_name']) . '</td></tr>'; }
if ($product['sku']) { $spec .= '<tr><th scope="row">SKU</th><td>' . esc($product['sku']) . '</td></tr>'; }
if ($spec) { $panels['Specifications'] = '<table class="spec">' . $spec . '</table>'; }
$panels['Shipping & returns'] = '<div class="prose"><ul><li>Standard shipping ' . money($flat) . ($threshold > 0 ? '; free on orders of ' . money($threshold) . ' or more' : '') . '.</li><li>Orders are processed in ' . esc(setting('order_processing_time', '1 business day')) . '. ' . esc(setting('shipping_region_note')) . '</li><li>A tracking number is shared once your order ships.</li></ul><p><a href="' . site_url('refund_returns') . '">Read the full refund, returns and shipping policy</a>.</p></div>';
?>
<section class="section--tight pb-0">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <div class="pdp mt-4">
            <div class="gallery" data-gallery>
                <div class="gallery__thumbs" <?= count($images) < 2 ? 'hidden' : '' ?>>
                    <?php foreach ($images as $i => $img): ?>
                        <button class="gallery__thumb <?= $i === 0 ? 'is-active' : '' ?>" type="button" data-gallery-thumb data-full="<?= media_url($img['path'], 'lg') ?>" data-alt="<?= esc($img['alt_text'] ?: $product['name'], 'attr') ?>" aria-label="Show image <?= $i + 1 ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>">
                            <img src="<?= media_url($img['path'], 'sm') ?>" alt="" loading="lazy" width="78" height="78">
                        </button>
                    <?php endforeach ?>
                </div>
                <div class="gallery__main">
                    <div class="pcard__badges">
                        <?php if ($out): ?><span class="badge-tag badge-tag--out">Out of stock</span><?php elseif ($onSale): ?><span class="badge-tag badge-tag--sale">Sale</span><?php endif ?>
                    </div>
                    <img data-gallery-main src="<?= media_url($images[0]['path'], 'lg') ?>" alt="<?= esc($images[0]['alt_text'] ?: $product['name'], 'attr') ?>" width="1254" height="1254" fetchpriority="high">
                </div>
            </div>

            <div class="buybox">
                <?php if ($product['category_name']): ?><a class="eyebrow text-decoration-none" href="<?= site_url('product-category/' . $product['category_slug']) ?>"><?= esc($product['category_name']) ?></a><?php endif ?>
                <h1><?= esc($product['name']) ?></h1>
                <?php if ($product['tagline']): ?><p class="buybox__tag"><?= esc($product['tagline']) ?></p><?php endif ?>

                <?= view('partials/buybox', ['product' => $product, 'inWishlist' => $inWishlist]) ?>

                <?php if ($product['short_description']): ?><div class="prose mt-4"><?= rich($product['short_description']) ?></div><?php endif ?>

                <ul class="assure">
                    <li><i class="bi bi-truck" aria-hidden="true"></i><div><b><?= $threshold > 0 ? 'Free shipping from ' . money($threshold) : 'Shipping ' . money($flat) ?></b><span><?= $threshold > 0 ? 'Standard shipping ' . money($flat) . ' below that.' : '' ?> <?= esc(setting('shipping_region_note')) ?></span></div></li>
                    <li><i class="bi bi-box-seam" aria-hidden="true"></i><div><b>Processed in <?= esc(setting('order_processing_time', '1 business day')) ?></b><span>Tracking number shared when your order ships.</span></div></li>
                    <li><i class="bi bi-shield-lock" aria-hidden="true"></i><div><b>Secure payment</b><span>Online payment through CCAvenue.</span></div></li>
                </ul>

                <div class="details" id="productDetails">
                    <?php $n = 0; foreach ($panels as $label => $html): ?>
                        <div class="faq__item">
                            <h2 class="m-0"><button class="faq__q" type="button" data-bs-toggle="collapse" data-bs-target="#pd<?= $n ?>" aria-expanded="<?= $n === 0 ? 'true' : 'false' ?>" aria-controls="pd<?= $n ?>"><span><?= esc($label) ?></span><i class="bi bi-plus-lg" aria-hidden="true"></i></button></h2>
                            <div id="pd<?= $n ?>" class="collapse <?= $n === 0 ? 'show' : '' ?>"><div class="faq__a"><?= $html ?></div></div>
                        </div>
                    <?php $n++; endforeach ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="section">
    <div class="shell">
        <div class="section-head"><div><span class="eyebrow">You may also like</span><h2>More from the range</h2></div><a class="link-arrow" href="<?= site_url('shop') ?>">View all products <i class="bi bi-arrow-right"></i></a></div>
        <div class="product-grid product-grid--4">
            <?php foreach ($related as $p): ?><?= view('partials/product_card', ['p' => $p, 'wishlistIds' => $wishlistIds]) ?><?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>
<?= $this->endSection() ?>
