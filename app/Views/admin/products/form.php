<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$p   = $product;
$v   = static fn (string $f, $d = '') => old($f) ?? ($p[$f] ?? $d);
$chk = static fn (string $f, int $d = 0) => (old('name') !== null ? (bool) old($f) : (bool) ($p[$f] ?? $d)) ? 'checked' : '';
$type = $v('type', 'simple');
$variations = $p['variations'] ?? [];
$attributes = $p['attributes'] ?? [];
$vRow = static function ($i, array $r = []) {
    ob_start(); ?>
    <div class="rep-row" data-rep-row style="grid-template-columns:1.2fr 1fr 1fr 1fr 1fr .8fr auto">
        <input type="hidden" name="v_id[<?= $i ?>]" value="<?= (int) ($r['id'] ?? 0) ?>">
        <input class="form-control" name="v_label[<?= $i ?>]" value="<?= esc($r['label'] ?? '', 'attr') ?>" placeholder="e.g. 1kg" aria-label="Pack size">
        <input class="form-control" type="number" step="0.01" min="0" name="v_regular[<?= $i ?>]" value="<?= esc((string) ($r['regular_price'] ?? ''), 'attr') ?>" placeholder="Regular ₹" aria-label="Regular price">
        <input class="form-control" type="number" step="0.01" min="0" name="v_sale[<?= $i ?>]" value="<?= esc((string) ($r['sale_price'] ?? ''), 'attr') ?>" placeholder="Sale ₹" aria-label="Sale price">
        <select class="form-select" name="v_stock[<?= $i ?>]" aria-label="Stock status"><option value="in_stock">In stock</option><option value="out_of_stock" <?= ($r['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option></select>
        <input class="form-control" name="v_sku[<?= $i ?>]" value="<?= esc((string) ($r['sku'] ?? ''), 'attr') ?>" placeholder="SKU" aria-label="SKU">
        <input class="form-control" type="number" min="0" name="v_qty[<?= $i ?>]" value="<?= esc((string) ($r['stock_qty'] ?? ''), 'attr') ?>" placeholder="Qty" aria-label="Stock quantity">
        <div class="d-flex gap-1"><button class="btn-icon" type="button" data-rep-up title="Move up" aria-label="Move up"><i class="bi bi-arrow-up"></i></button><button class="btn-icon danger" type="button" data-rep-remove title="Remove" aria-label="Remove"><i class="bi bi-x-lg"></i></button></div>
    </div>
    <?php return ob_get_clean();
};
$aRow = static function ($i, array $r = []) {
    ob_start(); ?>
    <div class="rep-row" data-rep-row style="grid-template-columns:1fr 2fr auto">
        <input class="form-control" name="a_name[<?= $i ?>]" value="<?= esc($r['name'] ?? '', 'attr') ?>" placeholder="e.g. Honey Type" aria-label="Specification name">
        <textarea class="form-control" name="a_value[<?= $i ?>]" rows="1" placeholder="e.g. Wildflower" aria-label="Specification value"><?= esc($r['value'] ?? '') ?></textarea>
        <div class="d-flex gap-1"><button class="btn-icon" type="button" data-rep-up title="Move up" aria-label="Move up"><i class="bi bi-arrow-up"></i></button><button class="btn-icon danger" type="button" data-rep-remove title="Remove" aria-label="Remove"><i class="bi bi-x-lg"></i></button></div>
    </div>
    <?php return ob_get_clean();
};
?>
<div class="a-head">
    <h1><?= $p ? esc($p['name']) : 'Add product' ?></h1>
    <?php if ($p): ?><a class="btn btn-light" href="<?= site_url('product/' . $p['slug']) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i> View on website</a><?php endif ?>
</div>

<form method="post" action="<?= site_url('admin/products' . ($p ? '/' . $p['id'] : '')) ?>" data-guard novalidate>
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="panel">
                <div class="panel__head"><h2>Product details</h2></div>
                <div class="panel__body"><div class="row g-3">
                    <div class="col-12"><label class="form-label" for="name">Product name *</label><input class="form-control" id="name" name="name" value="<?= esc((string) $v('name'), 'attr') ?>" required maxlength="190"></div>
                    <div class="col-md-7"><label class="form-label" for="slug">URL slug</label><input class="form-control" id="slug" name="slug" value="<?= esc((string) $v('slug'), 'attr') ?>" maxlength="220"><div class="form-text">Leave blank to create it from the name. Address: /product/your-slug</div></div>
                    <div class="col-md-5"><label class="form-label" for="tagline">Tagline</label><input class="form-control" id="tagline" name="tagline" value="<?= esc((string) $v('tagline'), 'attr') ?>" maxlength="190"><div class="form-text">One line under the product name.</div></div>
                    <div class="col-12"><label class="form-label">Key points (short description)</label><?= view('admin/partials/rte', ['name' => 'short_description', 'value' => $v('short_description'), 'small' => true, 'label' => 'Key points']) ?></div>
                    <div class="col-12"><label class="form-label">Full description</label><?= view('admin/partials/rte', ['name' => 'description', 'value' => $v('description'), 'label' => 'Description']) ?></div>
                    <div class="col-md-6"><label class="form-label" for="ingredients">Ingredients</label><textarea class="form-control" id="ingredients" name="ingredients" rows="4"><?= esc((string) $v('ingredients')) ?></textarea></div>
                    <div class="col-md-6"><label class="form-label" for="nutrition">Nutritional facts</label><textarea class="form-control" id="nutrition" name="nutrition" rows="4" placeholder="Per serving 5gm&#10;Energy: 19.6kcal&#10;Fat: NIL"><?= esc((string) $v('nutrition')) ?></textarea><div class="form-text">One “Name: value” per line. An optional first line without a colon becomes the caption.</div></div>
                </div></div>
            </div>

            <div class="panel">
                <div class="panel__head"><h2>Pricing &amp; pack sizes</h2></div>
                <div class="panel__body">
                    <div class="row g-3 mb-2">
                        <div class="col-md-5"><label class="form-label" for="type">Product type</label>
                            <select class="form-select" id="type" name="type" data-product-type><option value="simple" <?= $type === 'simple' ? 'selected' : '' ?>>Single pack — one price</option><option value="variable" <?= $type === 'variable' ? 'selected' : '' ?>>Several pack sizes — a price for each</option></select></div>
                    </div>
                    <div class="row g-3" data-show-type="simple">
                        <div class="col-md-3"><label class="form-label" for="regular_price">Regular price (₹) *</label><input class="form-control" type="number" step="0.01" min="0" id="regular_price" name="regular_price" value="<?= esc((string) $v('regular_price'), 'attr') ?>"></div>
                        <div class="col-md-3"><label class="form-label" for="sale_price">Sale price (₹)</label><input class="form-control" type="number" step="0.01" min="0" id="sale_price" name="sale_price" value="<?= esc((string) $v('sale_price'), 'attr') ?>"><div class="form-text">Leave blank for no sale.</div></div>
                        <div class="col-md-3"><label class="form-label" for="pack_label">Pack size label</label><input class="form-control" id="pack_label" name="pack_label" value="<?= esc((string) $v('pack_label'), 'attr') ?>" placeholder="e.g. 500 gm"></div>
                        <div class="col-md-3"><label class="form-label" for="stock_status">Availability</label><select class="form-select" id="stock_status" name="stock_status"><option value="in_stock">In stock</option><option value="out_of_stock" <?= $v('stock_status') === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option></select></div>
                    </div>
                    <div data-show-type="variable" data-repeater>
                        <div class="row g-3 mb-3"><div class="col-md-5"><label class="form-label" for="variation_label">Option name</label><input class="form-control" id="variation_label" name="variation_label" value="<?= esc((string) $v('variation_label', 'Pack Weight'), 'attr') ?>"><div class="form-text">Shown above the pack-size buttons, e.g. “Pack Weight”.</div></div></div>
                        <div class="rep-head" style="grid-template-columns:1.2fr 1fr 1fr 1fr 1fr .8fr 68px"><span>Pack size</span><span>Regular ₹</span><span>Sale ₹</span><span>Availability</span><span>SKU</span><span>Qty</span><span></span></div>
                        <div data-rep-list><?php foreach ($variations as $i => $r): ?><?= $vRow($i, $r) ?><?php endforeach ?></div>
                        <template><?= $vRow('__i__') ?></template>
                        <button class="btn btn-light btn-sm" type="button" data-rep-add><i class="bi bi-plus-lg"></i> Add pack size</button>
                        <p class="form-text mb-0 mt-2">The storefront shows the lowest price as “From ₹…”. Quantity is optional and only used when stock tracking is on.</p>
                    </div>
                </div>
            </div>

            <div class="panel" data-repeater>
                <div class="panel__head"><h2>Specifications</h2><button class="btn btn-light btn-sm" type="button" data-rep-add><i class="bi bi-plus-lg"></i> Add row</button></div>
                <div class="panel__body">
                    <div data-rep-list><?php foreach ($attributes as $i => $r): ?><?= $aRow($i, $r) ?><?php endforeach ?></div>
                    <template><?= $aRow('__i__') ?></template>
                    <p class="form-text mb-0">Shown in the “Specifications” panel on the product page, e.g. Item Weight, Diet Type.</p>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head"><h2>Search engines (SEO)</h2></div>
                <div class="panel__body"><div class="row g-3">
                    <div class="col-12"><label class="form-label" for="meta_title">SEO title</label><input class="form-control" id="meta_title" name="meta_title" value="<?= esc((string) $v('meta_title'), 'attr') ?>" maxlength="190"><div class="form-text"><span data-count-for="meta_title"></span> · aim for 50–60. Defaults to the product name.</div></div>
                    <div class="col-12"><label class="form-label" for="meta_description">SEO description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="320"><?= esc((string) $v('meta_description')) ?></textarea><div class="form-text"><span data-count-for="meta_description"></span> · aim for 140–160.</div></div>
                </div></div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="panel">
                <div class="panel__head"><h2>Publishing</h2></div>
                <div class="panel__body">
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="is_visible" name="is_visible" value="1" <?= $chk('is_visible', 1) ?>><label class="form-check-label" for="is_visible">Visible in the shop</label></div>
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" <?= $chk('is_featured') ?>><label class="form-check-label" for="is_featured">Featured (home page collection and menu)</label></div>
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" role="switch" id="is_bestseller" name="is_bestseller" value="1" <?= $chk('is_bestseller') ?>><label class="form-check-label" for="is_bestseller">Show in the second home-page row</label></div>
                    <label class="form-label" for="category_id">Category</label>
                    <select class="form-select mb-3" id="category_id" name="category_id"><option value="">— None —</option>
                        <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (string) $v('category_id') === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php endforeach ?></select>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label" for="sku">SKU</label><input class="form-control" id="sku" name="sku" value="<?= esc((string) $v('sku'), 'attr') ?>"></div>
                        <div class="col-6"><label class="form-label" for="sort_order">Display order</label><input class="form-control" type="number" id="sort_order" name="sort_order" value="<?= esc((string) $v('sort_order', 0), 'attr') ?>"></div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel__head"><h2>Stock</h2></div>
                <div class="panel__body">
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" role="switch" id="manage_stock" name="manage_stock" value="1" <?= $chk('manage_stock') ?>><label class="form-check-label" for="manage_stock">Track stock quantities</label></div>
                    <div data-show-type="simple"><label class="form-label" for="stock_qty">Quantity in stock</label><input class="form-control" type="number" min="0" id="stock_qty" name="stock_qty" value="<?= esc((string) $v('stock_qty'), 'attr') ?>"></div>
                    <p class="form-text mb-0 mt-2">When tracking is on, quantities fall as paid orders come in and the product switches to “Out of stock” at zero. When off, availability is set by hand.</p>
                </div>
            </div>
            <div class="panel"><div class="panel__body d-grid gap-2">
                <button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-check-lg"></i> Save product</button>
                <a class="btn btn-light" href="<?= site_url('admin/products') ?>">Back to products</a>
            </div></div>
        </div>
    </div>
</form>

<?php if ($p): ?>
<div class="panel mt-4" id="gallery">
    <div class="panel__head"><h2>Image gallery</h2>
        <div class="d-flex gap-2">
            <form id="attachForm" method="post" action="<?= site_url('admin/products/' . $p['id'] . '/images/attach') ?>"><?= csrf_field() ?><input type="hidden" name="path"></form>
            <button class="btn btn-light btn-sm" type="button" data-media-pick data-media-pick-form="#attachForm"><i class="bi bi-images"></i> Add from library</button>
        </div>
    </div>
    <div class="panel__body">
        <?php if ($p['images']): ?>
            <div class="gallery-admin mb-4">
                <?php foreach ($p['images'] as $img): ?>
                    <div class="gallery-admin__item <?= $img['is_primary'] ? 'is-primary' : '' ?>">
                        <img src="<?= media_url($img['path'], 'sm') ?>" alt="<?= esc((string) $img['alt_text'], 'attr') ?>" loading="lazy">
                        <div class="gallery-admin__bar">
                            <?php if ($img['is_primary']): ?><span class="badge-status badge-status--primary">Featured</span>
                            <?php else: ?><form method="post" action="<?= site_url('admin/products/images/' . $img['id'] . '/primary') ?>"><?= csrf_field() ?><button class="btn btn-light btn-sm" type="submit">Set featured</button></form><?php endif ?>
                            <form method="post" action="<?= site_url('admin/products/images/' . $img['id'] . '/delete') ?>" data-confirm="Remove this image from the product? The file stays in the media library." data-confirm-label="Remove"><?= csrf_field() ?><button class="btn-icon danger" type="submit" aria-label="Remove image"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php else: ?><p class="text-secondary">No images yet. The first image you add becomes the featured image.</p><?php endif ?>
        <form method="post" action="<?= site_url('admin/products/' . $p['id'] . '/images') ?>" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
            <?= csrf_field() ?>
            <input class="form-control" style="max-width:420px" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" aria-label="Upload product images" required>
            <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i> Upload images</button>
            <span class="form-text">JPG, PNG or WebP · up to <?= (int) (\App\Libraries\MediaLibrary::maxBytes() / 1048576) ?> MB each · square images at least 1200 px work best. Originals are kept; optimised copies are created automatically.</span>
        </form>
    </div>
</div>
<?php else: ?>
<p class="text-secondary mt-3"><i class="bi bi-info-circle"></i> Save the product first, then add its images.</p>
<?php endif ?>
<?= $this->endSection() ?>
