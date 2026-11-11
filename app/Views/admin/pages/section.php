<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$s     = $section;
$v     = static fn (string $f) => old($f) ?? ($s[$f] ?? '');
$keys  = array_keys($items[0] ?? []);
$names = ['title' => 'Title', 'text' => 'Text', 'low' => 'Sugarlif / Low GI', 'high' => 'High GI', 'a' => 'Left column', 'b' => 'Right column', 'measure' => 'Measure', 'keto' => 'Ketogenic diet', 'lowgi' => 'Low-GI diet', 'parameter' => 'Parameter', 'artificial' => 'Artificial sweeteners', 'stevia' => 'Stevia', 'sugarlif' => 'Sugarlif'];
$cols  = 'grid-template-columns:' . str_repeat('1fr ', max(1, count($keys))) . 'auto';
$row   = static function ($i, array $r) use ($keys, $names, $cols) {
    ob_start(); ?>
    <div class="rep-row" data-rep-row style="<?= $cols ?>">
        <?php foreach ($keys as $k): ?><textarea class="form-control" rows="1" name="items[<?= $i ?>][<?= esc($k, 'attr') ?>]" aria-label="<?= esc($names[$k] ?? ucfirst($k), 'attr') ?>"><?= esc((string) ($r[$k] ?? '')) ?></textarea><?php endforeach ?>
        <div class="d-flex gap-1"><button class="btn-icon" type="button" data-rep-up aria-label="Move up"><i class="bi bi-arrow-up"></i></button><button class="btn-icon" type="button" data-rep-down aria-label="Move down"><i class="bi bi-arrow-down"></i></button><button class="btn-icon danger" type="button" data-rep-remove aria-label="Remove row"><i class="bi bi-x-lg"></i></button></div>
    </div>
    <?php return ob_get_clean();
};
$back = site_url($page['slug'] === 'home' ? 'admin/home' : 'admin/pages/' . $page['id'] . '/edit') . '#sections';
?>
<div class="a-head"><h1><?= esc($s['admin_label']) ?></h1><a class="btn btn-light" href="<?= site_url($page['slug'] === 'home' ? '/' : $page['slug']) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i> View page</a></div>
<form class="panel" method="post" action="<?= site_url('admin/pages/sections/' . $s['id']) ?>" enctype="multipart/form-data" data-guard novalidate>
    <?= csrf_field() ?>
    <div class="panel__body"><div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="eyebrow">Small label</label><input class="form-control" id="eyebrow" name="eyebrow" value="<?= esc((string) $v('eyebrow'), 'attr') ?>" maxlength="120"></div>
        <div class="col-md-8"><label class="form-label" for="heading">Heading</label><input class="form-control" id="heading" name="heading" value="<?= esc((string) $v('heading'), 'attr') ?>" maxlength="255"></div>
        <div class="col-12"><label class="form-label">Text</label><?= view('admin/partials/rte', ['name' => 'body', 'value' => $v('body'), 'label' => 'Section text']) ?></div>
        <div class="col-md-6"><?= view('admin/partials/image_field', ['name' => 'image', 'value' => $v('image'), 'label' => 'Image']) ?></div>
        <div class="col-md-6"><label class="form-label" for="image_alt">Image description (alt text)</label><input class="form-control" id="image_alt" name="image_alt" value="<?= esc((string) $v('image_alt'), 'attr') ?>" maxlength="255"><div class="form-text">Describes the image for screen readers and search engines.</div></div>
        <div class="col-md-4"><label class="form-label" for="cta_label">Button label</label><input class="form-control" id="cta_label" name="cta_label" value="<?= esc((string) $v('cta_label'), 'attr') ?>" maxlength="80"></div>
        <div class="col-md-5"><label class="form-label" for="cta_url">Button link</label><input class="form-control" id="cta_url" name="cta_url" value="<?= esc((string) $v('cta_url'), 'attr') ?>" maxlength="255" placeholder="/shop"></div>
        <div class="col-md-3"><div class="form-check form-switch mt-md-4 pt-md-1"><input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= $s['is_active'] ? 'checked' : '' ?>><label class="form-check-label" for="is_active">Show this section</label></div></div>

        <?php if ($keys): ?>
            <div class="col-12" data-repeater>
                <div class="form-section d-flex justify-content-between align-items-center"><span>List rows</span><button class="btn btn-light btn-sm" type="button" data-rep-add><i class="bi bi-plus-lg"></i> Add row</button></div>
                <div class="rep-head mt-3" style="<?= $cols ?>"><?php foreach ($keys as $k): ?><span><?= esc($names[$k] ?? ucfirst($k)) ?></span><?php endforeach ?><span style="width:104px"></span></div>
                <div data-rep-list><?php foreach ($items as $i => $r): ?><?= $row($i, $r) ?><?php endforeach ?></div>
                <template><?= $row('__i__', []) ?></template>
                <?php if (in_array($s['section_key'], ['compare-1', 'compare-2'], true)): ?><p class="form-text mb-0">The first row is used as the table’s column headings.</p><?php endif ?>
            </div>
        <?php endif ?>
    </div></div>
    <div class="form-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Save section</button><a class="btn btn-light" href="<?= $back ?>">Back</a>
        <span class="form-text ms-auto d-none d-md-inline">Leave a field empty to hide that part of the section.</span></div>
</form>
<?= $this->endSection() ?>
