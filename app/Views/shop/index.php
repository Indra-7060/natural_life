<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$base   = $category ? site_url('product-category/' . $category['slug']) : site_url('shop');
$query  = static function (array $override = []) use ($filters, $category) {
    $q = array_filter(array_merge(['q' => $filters['q'], 'min' => $filters['min'], 'max' => $filters['max'], 'stock' => $filters['stock'] ?: null, 'sale' => $filters['sale'] ?: null, 'sort' => $filters['sort'] !== 'featured' ? $filters['sort'] : null], $override), static fn ($v) => $v !== null && $v !== '' && $v !== 0);
    return $q ? '?' . http_build_query($q) : '';
};
$active = ($filters['q'] !== '') || ($filters['min'] !== null && $filters['min'] !== '') || ($filters['max'] !== null && $filters['max'] !== '') || $filters['stock'] || $filters['sale'];
?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <h1><?= esc($title) ?></h1>
        <p class="lede"><?= esc($category['description'] ?? 'Natural sugar, wheat flour, honey and jaggery — organically re-engineered to be Low GI.') ?></p>
    </div>
</section>

<section class="section--tight">
    <div class="shell shop-layout">
        <aside class="filters" aria-label="Filter products">
            <button class="btn btn-line btn-sm d-lg-none mb-3" type="button" data-filter-toggle aria-expanded="false"><i class="bi bi-sliders"></i> Filters</button>
            <div class="filters__body d-lg-block">
                <div class="filter-group">
                    <h6>Category</h6>
                    <ul class="filter-list">
                        <li><a class="<?= $category ? '' : 'is-active' ?>" href="<?= site_url('shop') . $query() ?>">All products</a></li>
                        <?php foreach ($categories as $c): ?>
                            <li><a class="<?= ($category['id'] ?? 0) === $c['id'] ? 'is-active' : '' ?>" href="<?= site_url('product-category/' . $c['slug']) . $query() ?>"><?= esc($c['name']) ?> <span><?= (int) $c['product_count'] ?></span></a></li>
                        <?php endforeach ?>
                    </ul>
                </div>
                <form action="<?= $base ?>" method="get">
                    <?php if ($filters['q'] !== ''): ?><input type="hidden" name="q" value="<?= esc($filters['q'], 'attr') ?>"><?php endif ?>
                    <?php if ($filters['sort'] !== 'featured'): ?><input type="hidden" name="sort" value="<?= esc($filters['sort'], 'attr') ?>"><?php endif ?>
                    <div class="filter-group">
                        <h6>Price (₹)</h6>
                        <div class="price-inputs">
                            <input class="form-control" type="number" name="min" min="0" placeholder="<?= $bounds['lo'] ?>" value="<?= esc((string) $filters['min'], 'attr') ?>" aria-label="Minimum price">
                            <span class="muted">to</span>
                            <input class="form-control" type="number" name="max" min="0" placeholder="<?= $bounds['hi'] ?>" value="<?= esc((string) $filters['max'], 'attr') ?>" aria-label="Maximum price">
                        </div>
                    </div>
                    <div class="filter-group">
                        <h6>Availability</h6>
                        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="stock" value="1" id="fStock" <?= $filters['stock'] ? 'checked' : '' ?>><label class="form-check-label" for="fStock">In stock only</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="sale" value="1" id="fSale" <?= $filters['sale'] ? 'checked' : '' ?>><label class="form-check-label" for="fSale">On sale</label></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-forest btn-sm" type="submit">Apply filters</button>
                        <?php if ($active): ?><a class="btn btn-line btn-sm" href="<?= $base ?>">Clear</a><?php endif ?>
                    </div>
                </form>
            </div>
        </aside>

        <div>
            <div class="shop-bar">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="muted"><?= (int) $total ?> <?= (int) $total === 1 ? 'product' : 'products' ?></span>
                    <?php if ($filters['q'] !== ''): ?><a class="chip" href="<?= $base . $query(['q' => null]) ?>">“<?= esc($filters['q']) ?>” <i class="bi bi-x"></i></a><?php endif ?>
                    <?php if ($filters['stock']): ?><a class="chip" href="<?= $base . $query(['stock' => null]) ?>">In stock <i class="bi bi-x"></i></a><?php endif ?>
                    <?php if ($filters['sale']): ?><a class="chip" href="<?= $base . $query(['sale' => null]) ?>">On sale <i class="bi bi-x"></i></a><?php endif ?>
                    <?php if (($filters['min'] ?? '') !== '' || ($filters['max'] ?? '') !== ''): ?><a class="chip" href="<?= $base . $query(['min' => null, 'max' => null]) ?>">₹<?= esc((string) ($filters['min'] ?: $bounds['lo'])) ?> – ₹<?= esc((string) ($filters['max'] ?: $bounds['hi'])) ?> <i class="bi bi-x"></i></a><?php endif ?>
                </div>
                <form action="<?= $base ?>" method="get" class="d-flex align-items-center gap-2">
                    <?php foreach (['q', 'min', 'max'] as $k): if (($filters[$k] ?? '') !== '' && $filters[$k] !== null): ?><input type="hidden" name="<?= $k ?>" value="<?= esc((string) $filters[$k], 'attr') ?>"><?php endif; endforeach ?>
                    <?php if ($filters['stock']): ?><input type="hidden" name="stock" value="1"><?php endif ?>
                    <?php if ($filters['sale']): ?><input type="hidden" name="sale" value="1"><?php endif ?>
                    <label class="muted small" for="sortBy">Sort by</label>
                    <select class="form-select" id="sortBy" name="sort" data-autosubmit>
                        <?php foreach (['featured' => 'Featured', 'price-asc' => 'Price: low to high', 'price-desc' => 'Price: high to low', 'name' => 'Name A–Z', 'newest' => 'Newest'] as $k => $label): ?>
                            <option value="<?= $k ?>" <?= $filters['sort'] === $k ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach ?>
                    </select>
                </form>
            </div>

            <?php if ($products): ?>
                <div class="product-grid product-grid--3">
                    <?php foreach ($products as $p): ?><?= view('partials/product_card', ['p' => $p, 'wishlistIds' => $wishlistIds]) ?><?php endforeach ?>
                </div>
                <?= $pager->links('shop', 'site') ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h2>No products found</h2>
                    <p>Try a different search term or clear the filters.</p>
                    <a class="btn btn-forest" href="<?= site_url('shop') ?>">View all products</a>
                </div>
            <?php endif ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
