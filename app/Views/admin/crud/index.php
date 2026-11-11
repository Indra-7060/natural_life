<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $ctx = $context ?? ''; $amp = $ctx ? '&' . ltrim($ctx, '?') : ''; ?>
<div class="a-head">
    <h1><?= esc($plural) ?></h1>
    <?php if ($canCreate): ?><a class="btn btn-primary" href="<?= site_url($path . '/new') . $ctx ?>"><i class="bi bi-plus-lg"></i> Add <?= esc(strtolower($singular)) ?></a><?php endif ?>
</div>
<?php if ($intro): ?><p class="a-intro"><?= esc($intro) ?></p><?php endif ?>

<div class="panel">
    <?php if ($searchable): ?>
        <form class="filters-bar" method="get" action="<?= site_url($path) ?>">
            <?php if ($ctx): parse_str(ltrim($ctx, '?'), $ctxParams); foreach ($ctxParams as $k => $v): ?><input type="hidden" name="<?= esc($k, 'attr') ?>" value="<?= esc($v, 'attr') ?>"><?php endforeach; endif ?>
            <input class="form-control grow" type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Search <?= esc(strtolower($plural), 'attr') ?>…" aria-label="Search">
            <button class="btn btn-light" type="submit"><i class="bi bi-search"></i> Search</button>
            <?php if ($q !== ''): ?><a class="btn btn-light" href="<?= site_url($path) . $ctx ?>">Clear</a><?php endif ?>
        </form>
    <?php endif ?>

    <?php if (! $rows): ?>
        <div class="empty"><i class="bi bi-inbox"></i><?= $q !== '' ? 'Nothing matches your search.' : 'Nothing here yet.' ?></div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="a-table">
                <thead><tr>
                    <?php foreach ($columns as $field => $col): ?><th scope="col"><?= esc(is_array($col) ? $col[0] : $col) ?></th><?php endforeach ?>
                    <th scope="col" class="actions">Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $field => $col): $type = is_array($col) ? ($col[1] ?? 'text') : 'text'; $val = $row[$field] ?? null; ?>
                            <td>
                                <?php if ($type === 'toggle'): ?>
                                    <form method="post" action="<?= site_url($path . '/' . $row['id'] . '/toggle/' . $field) . $ctx ?>"><?= csrf_field() ?>
                                        <button class="switch-btn" type="submit" aria-label="<?= $val ? 'Turn off' : 'Turn on' ?> <?= esc(is_array($col) ? $col[0] : $col, 'attr') ?>" aria-pressed="<?= $val ? 'true' : 'false' ?>"><span class="switch <?= $val ? 'on' : '' ?>"></span></button>
                                    </form>
                                <?php elseif ($type === 'image'): ?>
                                    <?php if ($val): ?><img class="thumb" src="<?= media_url($val, 'sm') ?>" alt="" loading="lazy"><?php else: ?><span class="text-secondary">—</span><?php endif ?>
                                <?php elseif ($type === 'date'): ?>
                                    <?= $val ? date('j M Y', strtotime((string) $val)) : '<span class="text-secondary">—</span>' ?>
                                <?php elseif ($type === 'money'): ?>
                                    <?= money($val) ?>
                                <?php elseif ($type === 'badge'): ?>
                                    <?= status_badge((string) $val) ?>
                                <?php else: ?>
                                    <?php if ($field === array_key_first(array_filter($columns, static fn ($c) => ! is_array($c) || ($c[1] ?? 'text') === 'text'))): ?>
                                        <a class="fw-semibold text-decoration-none" href="<?= site_url($path . '/' . $row['id'] . '/edit') . $ctx ?>"><?= esc(plain((string) $val, 90)) ?: '—' ?></a>
                                    <?php else: ?>
                                        <?= ($val === null || $val === '') ? '<span class="text-secondary">—</span>' : esc(plain((string) $val, 70)) ?>
                                    <?php endif ?>
                                <?php endif ?>
                            </td>
                        <?php endforeach ?>
                        <td class="actions">
                            <a class="btn-icon" href="<?= site_url($path . '/' . $row['id'] . '/edit') . $ctx ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                            <?php if ($canDelete): ?>
                                <form class="d-inline" method="post" action="<?= site_url($path . '/' . $row['id'] . '/delete') . $ctx ?>" data-confirm="Delete this <?= esc(strtolower($singular), 'attr') ?>? This cannot be undone."><?= csrf_field() ?>
                                    <button class="btn-icon danger" type="submit" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links('admin', 'admin') ?>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
