<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$toggle = static function (array $r, string $field, string $label) {
    return '<form method="post" action="' . site_url('admin/products/' . $r['id'] . '/toggle/' . $field) . '">' . csrf_field()
        . '<button class="switch-btn" type="submit" aria-label="Toggle ' . $label . '" aria-pressed="' . ($r[$field] ? 'true' : 'false') . '"><span class="switch ' . ($r[$field] ? 'on' : '') . '"></span></button></form>';
};
?>
<div class="a-head"><h1>Products</h1><a class="btn btn-primary" href="<?= site_url('admin/products/new') ?>"><i class="bi bi-plus-lg"></i> Add product</a></div>
<div class="panel">
    <form class="filters-bar" method="get" action="<?= site_url('admin/products') ?>">
        <input class="form-control grow" type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Search by name or SKU…" aria-label="Search products">
        <select class="form-select" name="category" aria-label="Category" data-autosubmit><option value="">All categories</option>
            <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $cat === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php endforeach ?></select>
        <select class="form-select" name="stock" aria-label="Stock" data-autosubmit><option value="">Any stock status</option>
            <option value="in_stock" <?= $stock === 'in_stock' ? 'selected' : '' ?>>In stock</option><option value="out_of_stock" <?= $stock === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option></select>
        <button class="btn btn-light" type="submit"><i class="bi bi-search"></i> Filter</button>
        <?php if ($q !== '' || $cat || $stock !== ''): ?><a class="btn btn-light" href="<?= site_url('admin/products') ?>">Clear</a><?php endif ?>
    </form>
    <?php if (! $rows): ?><div class="empty"><i class="bi bi-box-seam"></i>No products found.</div><?php else: ?>
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th></th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Visible</th><th>Featured</th><th class="actions">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td style="width:60px"><img class="thumb" src="<?= media_url($r['image'], 'sm') ?>" alt="" loading="lazy"></td>
                <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/products/' . $r['id'] . '/edit') ?>"><?= esc($r['name']) ?></a><br>
                    <small class="text-secondary"><?= $r['type'] === 'variable' ? (int) $r['variation_count'] . ' pack sizes' : 'Single pack' . ($r['pack_label'] ? ' · ' . esc($r['pack_label']) : '') ?></small></td>
                <td><?= esc($r['category_name'] ?? '—') ?></td>
                <td><?= $r['type'] === 'variable' ? money($r['price_min']) . ' – ' . money($r['price_max']) : ($r['sale_price'] !== null ? '<strong>' . money($r['sale_price']) . '</strong> <del class="text-secondary">' . money($r['regular_price']) . '</del>' : money($r['regular_price'])) ?></td>
                <td><?= status_badge($r['stock_status']) ?><?= $r['manage_stock'] && $r['stock_qty'] !== null ? ' <small class="text-secondary">' . (int) $r['stock_qty'] . '</small>' : '' ?></td>
                <td><?= $toggle($r, 'is_visible', 'visibility') ?></td>
                <td><?= $toggle($r, 'is_featured', 'featured') ?></td>
                <td class="actions">
                    <a class="btn-icon" href="<?= site_url('product/' . $r['slug']) ?>" target="_blank" rel="noopener" title="View on website" aria-label="View on website"><i class="bi bi-eye"></i></a>
                    <a class="btn-icon" href="<?= site_url('admin/products/' . $r['id'] . '/edit') ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                    <form class="d-inline" method="post" action="<?= site_url('admin/products/' . $r['id'] . '/delete') ?>" data-confirm="Delete “<?= esc($r['name'], 'attr') ?>”? It will be removed from the shop. Past orders keep their records."><?= csrf_field() ?>
                        <button class="btn-icon danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table></div>
    <?= $pager->links('admin', 'admin') ?>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
