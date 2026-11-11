<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$isHome = $page && $page['slug'] === 'home';
$v      = static fn (string $f, $d = '') => old($f) ?? ($page[$f] ?? $d);
$simple = ! $page || in_array($page['template'], ['default', 'legal', 'contact'], true);
?>
<div class="a-head">
    <h1><?= $isHome ? 'Home page' : ($page ? esc($page['title']) : 'Add page') ?></h1>
    <?php if ($page): ?><a class="btn btn-light" href="<?= site_url($isHome ? '/' : $page['slug']) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i> View page</a><?php endif ?>
</div>

<?php if ($page && $sections): ?>
<div class="panel mb-4" id="sections">
    <div class="panel__head"><h2>Content sections</h2><span class="text-secondary small">Shown on the page in this order. Hidden sections are skipped.</span></div>
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th>#</th><th>Section</th><th>Heading</th><th>Contains</th><th>Shown</th><th class="actions"></th></tr></thead>
        <tbody><?php foreach ($sections as $i => $s): $n = count(section_items($s)); ?><tr>
            <td class="text-secondary"><?= $i + 1 ?></td>
            <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/pages/sections/' . $s['id'] . '/edit') ?>"><?= esc($s['admin_label']) ?></a></td>
            <td><?= esc(plain((string) $s['heading'], 60)) ?: '<span class="text-secondary">—</span>' ?></td>
            <td class="text-secondary small"><?= implode(' · ', array_filter([$s['body'] ? 'Text' : '', $s['image'] ? 'Image' : '', $n ? $n . ' list rows' : '', $s['cta_label'] ? 'Button' : ''])) ?: '—' ?></td>
            <td><form method="post" action="<?= site_url('admin/pages/sections/' . $s['id'] . '/toggle') ?>"><?= csrf_field() ?><button class="switch-btn" type="submit" aria-label="Show or hide section" aria-pressed="<?= $s['is_active'] ? 'true' : 'false' ?>"><span class="switch <?= $s['is_active'] ? 'on' : '' ?>"></span></button></form></td>
            <td class="actions"><a class="btn btn-light btn-sm" href="<?= site_url('admin/pages/sections/' . $s['id'] . '/edit') ?>"><i class="bi bi-pencil"></i> Edit</a></td>
        </tr><?php endforeach ?></tbody>
    </table></div>
    <?php if ($isHome): ?><div class="panel__body pt-0"><p class="form-text mb-0">Also on the home page: <a href="<?= site_url('admin/banners') ?>">hero banners</a>, <a href="<?= site_url('admin/products') ?>">featured products</a>, <a href="<?= site_url('admin/testimonials') ?>">testimonials</a>, <a href="<?= site_url('admin/faqs') ?>">FAQs</a>, <a href="<?= site_url('admin/certifications') ?>">certifications</a> and the latest <a href="<?= site_url('admin/posts') ?>">blog posts</a>.</p></div><?php endif ?>
</div>
<?php endif ?>

<form class="panel" method="post" action="<?= site_url('admin/pages' . ($page ? '/' . $page['id'] : '')) ?>" enctype="multipart/form-data" data-guard novalidate>
    <?= csrf_field() ?>
    <div class="panel__head"><h2>Page details</h2></div>
    <div class="panel__body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="title">Page title *</label><input class="form-control" id="title" name="title" value="<?= esc((string) $v('title'), 'attr') ?>" required maxlength="190"></div>
        <?php if (! $page): ?>
            <div class="col-md-3"><label class="form-label" for="slug">URL slug</label><input class="form-control" id="slug" name="slug" value="<?= esc((string) old('slug', ''), 'attr') ?>"><div class="form-text">Leave blank to use the title.</div></div>
            <div class="col-md-3"><label class="form-label" for="template">Layout</label><select class="form-select" id="template" name="template"><?php foreach ($templates as $k => $l): ?><option value="<?= $k ?>"><?= esc($l) ?></option><?php endforeach ?></select></div>
        <?php else: ?>
            <div class="col-md-3"><label class="form-label">Address</label><input class="form-control" value="/<?= esc($isHome ? '' : $page['slug'], 'attr') ?>" disabled><div class="form-text">Kept fixed so existing links keep working.</div></div>
            <div class="col-md-3"><label class="form-label">Layout</label><input class="form-control" value="<?= esc($templates[$page['template']] ?? ucfirst($page['template']), 'attr') ?>" disabled></div>
        <?php endif ?>
        <?php if (! $isHome): ?>
            <div class="col-md-6"><label class="form-label" for="eyebrow">Small label above the heading</label><input class="form-control" id="eyebrow" name="eyebrow" value="<?= esc((string) $v('eyebrow'), 'attr') ?>" maxlength="120"></div>
            <div class="col-md-6"><label class="form-label" for="intro">Main heading / introduction</label><input class="form-control" id="intro" name="intro" value="<?= esc((string) $v('intro'), 'attr') ?>"><div class="form-text">Shown as the large heading at the top of the page.</div></div>
            <?php if ($simple): ?><div class="col-12"><label class="form-label">Page text</label><?= view('admin/partials/rte', ['name' => 'body', 'value' => $v('body'), 'label' => 'Page text']) ?></div>
            <?php else: ?><textarea name="body" hidden><?= esc((string) $v('body')) ?></textarea><?php endif ?>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="is_published" name="is_published" value="1" <?= (old('title') !== null ? old('is_published') : ($page['is_published'] ?? 1)) ? 'checked' : '' ?>><label class="form-check-label" for="is_published">Published (visible on the website)</label></div></div>
        <?php endif ?>
        <div class="col-12"><div class="form-section">Search engines (SEO)</div></div>
        <div class="col-12"><label class="form-label" for="meta_title">SEO title</label><input class="form-control" id="meta_title" name="meta_title" value="<?= esc((string) $v('meta_title'), 'attr') ?>" maxlength="190"><div class="form-text"><span data-count-for="meta_title"></span> · aim for 50–60</div></div>
        <div class="col-12"><label class="form-label" for="meta_description">SEO description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="320"><?= esc((string) $v('meta_description')) ?></textarea><div class="form-text"><span data-count-for="meta_description"></span> · aim for 140–160</div></div>
        <div class="col-md-6"><?= view('admin/partials/image_field', ['name' => 'og_image', 'value' => $v('og_image'), 'label' => 'Social sharing image', 'help' => 'Optional. Choose from the media library.']) ?></div>
    </div></div>
    <div class="form-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Save page</button><?php if (! $isHome): ?><a class="btn btn-light" href="<?= site_url('admin/pages') ?>">Back to pages</a><?php endif ?></div>
</form>
<?= $this->endSection() ?>
