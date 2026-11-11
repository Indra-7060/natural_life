<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$ctx    = $context ?? '';
$action = site_url($path . ($row ? '/' . $row['id'] : '')) . $ctx;
$val    = static function (string $name, array $f) use ($row) {
    $old = old($name);
    if ($old !== null) { return $old; }
    if ($row) { return $row[$name] ?? ''; }
    return $f['default'] ?? '';
};
?>
<div class="a-head"><h1><?= esc($title) ?></h1></div>
<form class="panel" method="post" action="<?= $action ?>" enctype="multipart/form-data" data-guard novalidate>
    <?= csrf_field() ?>
    <?php if ($ctx): parse_str(ltrim($ctx, '?'), $ctxParams); if (isset($ctxParams['menu'])): ?><input type="hidden" name="menu_id" value="<?= (int) $ctxParams['menu'] ?>"><?php endif; endif ?>
    <div class="panel__body">
        <div class="row g-3">
            <?php foreach ($fields as $name => $f):
                $type  = $f['type'] ?? 'text';
                $value = $val($name, $f);
                $req   = str_contains($f['rules'] ?? '', 'required') && ! ($type === 'password' && $row);
                $label = $f['label'] . ($req ? ' *' : '');
                $col   = $f['col'] ?? 12;
                if (! empty($f['section'])): ?><div class="col-12"><div class="form-section"><?= esc($f['section']) ?></div></div><?php endif ?>
                <div class="col-md-<?= (int) $col ?>">
                    <?php if ($type === 'checkbox'): ?>
                        <div class="form-check form-switch mt-md-4 pt-md-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="f_<?= $name ?>" name="<?= $name ?>" value="1" <?= $value ? 'checked' : '' ?>>
                            <label class="form-check-label" for="f_<?= $name ?>"><?= esc($f['label']) ?></label>
                        </div>
                    <?php elseif ($type === 'image'): ?>
                        <?= view('admin/partials/image_field', ['name' => $name, 'value' => $value, 'label' => $label, 'help' => $f['help'] ?? null]) ?>
                    <?php elseif ($type === 'richtext'): ?>
                        <label class="form-label"><?= esc($label) ?></label>
                        <?= view('admin/partials/rte', ['name' => $name, 'value' => $value, 'label' => $f['label']]) ?>
                    <?php elseif ($type === 'textarea'): ?>
                        <label class="form-label" for="f_<?= $name ?>"><?= esc($label) ?></label>
                        <textarea class="form-control" id="f_<?= $name ?>" name="<?= $name ?>" rows="<?= (int) ($f['rows'] ?? 3) ?>"><?= esc((string) $value) ?></textarea>
                    <?php elseif ($type === 'select'): ?>
                        <label class="form-label" for="f_<?= $name ?>"><?= esc($label) ?></label>
                        <select class="form-select" id="f_<?= $name ?>" name="<?= $name ?>">
                            <?php foreach ($f['options'] as $k => $text): ?><option value="<?= esc((string) $k, 'attr') ?>" <?= (string) $value === (string) $k ? 'selected' : '' ?>><?= esc($text) ?></option><?php endforeach ?>
                        </select>
                    <?php elseif ($type === 'datetime'): ?>
                        <label class="form-label" for="f_<?= $name ?>"><?= esc($label) ?></label>
                        <input class="form-control" type="datetime-local" id="f_<?= $name ?>" name="<?= $name ?>" value="<?= $value ? date('Y-m-d\TH:i', strtotime((string) $value)) : '' ?>">
                    <?php elseif ($type === 'password'): ?>
                        <label class="form-label" for="f_<?= $name ?>"><?= esc($label) ?></label>
                        <input class="form-control" type="password" id="f_<?= $name ?>" name="<?= $name ?>" autocomplete="new-password">
                    <?php else: ?>
                        <label class="form-label" for="f_<?= $name ?>"><?= esc($label) ?></label>
                        <input class="form-control" type="<?= in_array($type, ['email', 'number', 'url'], true) ? $type : 'text' ?>" id="f_<?= $name ?>" name="<?= $name ?>" value="<?= esc((string) $value, 'attr') ?>" <?= $type === 'number' ? 'step="' . esc($f['step'] ?? '1', 'attr') . '"' : '' ?>>
                    <?php endif ?>
                    <?php if (! empty($f['help']) && $type !== 'image'): ?><div class="form-text"><?= esc($f['help']) ?></div><?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    <div class="form-foot">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Save <?= esc(strtolower($singular)) ?></button>
        <a class="btn btn-light" href="<?= site_url($path) . $ctx ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>
