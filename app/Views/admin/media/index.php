<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Media library</h1></div>
<div class="panel mb-4">
    <form class="panel__body d-flex flex-wrap gap-2 align-items-center" method="post" action="<?= site_url('admin/media') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input class="form-control" style="max-width:380px" type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" aria-label="Choose images" required>
        <select class="form-select" style="max-width:190px" name="folder" aria-label="Folder"><?php foreach ($folders as $f): ?><option value="<?= $f ?>" <?= ($folder ?: 'general') === $f ? 'selected' : '' ?>><?= ucfirst($f) ?></option><?php endforeach ?></select>
        <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i> Upload</button>
        <span class="form-text">JPG, PNG, WebP, GIF or SVG · up to <?= (int) (\App\Libraries\MediaLibrary::maxBytes() / 1048576) ?> MB each. The original is kept untouched; fast-loading copies are generated automatically.</span>
    </form>
</div>
<div class="panel">
    <form class="filters-bar" method="get" action="<?= site_url('admin/media') ?>">
        <input class="form-control grow" type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Search by file name or description…" aria-label="Search media">
        <select class="form-select" name="folder" aria-label="Folder" data-autosubmit><option value="">All folders</option><?php foreach ($folders as $f): ?><option value="<?= $f ?>" <?= $folder === $f ? 'selected' : '' ?>><?= ucfirst($f) ?></option><?php endforeach ?></select>
        <button class="btn btn-light" type="submit"><i class="bi bi-search"></i> Search</button>
    </form>
    <div class="panel__body">
        <?php if (! $rows): ?><div class="empty"><i class="bi bi-image"></i>No images found.</div><?php else: ?>
        <div class="media-grid">
            <?php foreach ($rows as $m): ?>
                <div class="media-card">
                    <a class="media-card__img" href="<?= media_url($m['path'], 'original') ?>" target="_blank" rel="noopener" title="Open original"><img src="<?= media_url($m['path'], 'sm') ?>" alt="<?= esc((string) $m['alt_text'], 'attr') ?>" loading="lazy"></a>
                    <div class="media-card__body">
                        <div class="media-card__name" title="<?= esc($m['file_name'], 'attr') ?>"><?= esc($m['file_name']) ?></div>
                        <div class="media-card__meta"><?= ucfirst($m['folder']) ?><?= $m['width'] ? ' · ' . $m['width'] . '×' . $m['height'] : '' ?> · <?= $m['size_bytes'] > 1048576 ? round($m['size_bytes'] / 1048576, 1) . ' MB' : round($m['size_bytes'] / 1024) . ' KB' ?></div>
                        <div class="media-card__meta"><?= $m['usages'] ? '<i class="bi bi-link-45deg"></i> ' . esc(implode(', ', $m['usages'])) : 'Not in use' ?></div>
                        <form class="d-flex gap-1 mt-auto" method="post" action="<?= site_url('admin/media/' . $m['id']) ?>"><?= csrf_field() ?>
                            <input class="form-control form-control-sm" name="alt_text" value="<?= esc((string) $m['alt_text'], 'attr') ?>" placeholder="Description" aria-label="Image description">
                            <button class="btn-icon" type="submit" title="Save description" aria-label="Save description"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <?php if (! $m['usages']): ?>
                            <form method="post" action="<?= site_url('admin/media/' . $m['id'] . '/delete') ?>" data-confirm="Delete <?= esc($m['file_name'], 'attr') ?> permanently?"><?= csrf_field() ?><button class="btn btn-light btn-sm w-100" type="submit"><i class="bi bi-trash"></i> Delete</button></form>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
        <?php endif ?>
    </div>
    <?= $pager->links('admin', 'admin') ?>
</div>
<?= $this->endSection() ?>
