<?php
/** Admin layout. Expects $title, $breadcrumbs, $admin, $newOrders, $newEnquiries. */
use App\Controllers\Admin\AdminController;

$current = '/' . trim(service('uri')->getPath(), '/');
$isOn    = static function (string $path) use ($current): bool {
    $p = '/' . $path;
    return $path === 'admin' ? $current === '/admin' : ($current === $p || str_starts_with($current, $p . '/'));
};
$counts = ['admin/orders' => $newOrders ?? 0, 'admin/enquiries' => $newEnquiries ?? 0];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title) ?> · Sugarlif Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&family=Fraunces:opsz,wght@9..144,500&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset_url('css/admin.css') ?>" rel="stylesheet">
    <?= csrf_meta() ?>
</head>
<body data-base="<?= rtrim(site_url(), '/') ?>" data-csrf-name="<?= csrf_token() ?>">
<div class="a-shell">
    <aside class="a-side" id="adminSide">
        <a class="a-side__brand text-decoration-none" href="<?= site_url('admin') ?>">
            <img src="<?= media_url(setting('logo_light'), 'original') ?>" alt="Sugarlif"><span>Admin</span>
        </a>
        <nav class="a-nav" aria-label="Admin">
            <?php foreach (AdminController::NAV as $group => $items):
                $visible = array_filter($items, static fn ($i) => admin_can($i[3]));
                if (! $visible) { continue; } ?>
                <div class="a-nav__group"><?= esc($group) ?></div>
                <?php foreach ($visible as [$label, $path, $icon]): ?>
                    <a class="<?= $isOn($path) || ($path === 'admin/menus' && $isOn('admin/menu-items')) ? 'is-active' : '' ?>" href="<?= site_url($path) ?>">
                        <i class="bi bi-<?= $icon ?>" aria-hidden="true"></i><?= esc($label) ?>
                        <?php if (! empty($counts[$path])): ?><span class="count"><?= (int) $counts[$path] ?></span><?php endif ?>
                    </a>
                <?php endforeach ?>
            <?php endforeach ?>
        </nav>
    </aside>

    <div class="a-main">
        <header class="a-top">
            <button class="btn-icon side-toggle" type="button" id="sideToggle" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
            <form class="a-top__search" action="<?= site_url('admin/search') ?>" method="get" role="search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <label class="visually-hidden" for="adminSearch">Search</label>
                <input class="form-control" type="search" id="adminSearch" name="q" placeholder="Search products, orders, customers, pages…" value="<?= esc((string) service('request')->getGet('q'), 'attr') ?>" maxlength="80">
            </form>
            <a class="btn btn-light btn-sm ms-auto" href="<?= site_url('/') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-md-inline">View website</span></a>
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="a-avatar"><?= esc(mb_strtoupper(mb_substr($admin['name'], 0, 1))) ?></span>
                    <span class="d-none d-md-inline text-start lh-sm"><strong class="d-block" style="font-size:.86rem"><?= esc($admin['name']) ?></strong><small class="text-secondary"><?= esc($admin['email']) ?></small></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= site_url('admin/profile') ?>"><i class="bi bi-person me-2"></i>My profile &amp; password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= site_url('admin/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                </ul>
            </div>
        </header>

        <main class="a-content">
            <?php if ($breadcrumbs): ?>
                <ol class="a-crumbs" aria-label="Breadcrumb">
                    <li><a href="<?= site_url('admin') ?>">Admin</a></li>
                    <?php foreach ($breadcrumbs as [$label, $url]): ?><li><?php if ($url): ?><a href="<?= esc($url, 'attr') ?>"><?= esc($label) ?></a><?php else: ?><?= esc($label) ?><?php endif ?></li><?php endforeach ?>
                </ol>
            <?php endif ?>
            <?= $this->include('admin/partials/flash') ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<!-- Confirmation dialog -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h2 class="h5" id="confirmTitle">Are you sure?</h2>
                <p class="text-secondary mb-4" id="confirmText">This cannot be undone.</p>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmOk">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media picker -->
<div class="modal fade" id="mediaPicker" tabindex="-1" aria-labelledby="mediaPickerTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h5 mb-0" id="mediaPickerTitle">Choose an image</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <input class="form-control" style="max-width:240px" type="search" id="pickerSearch" placeholder="Search by file name…" aria-label="Search images">
                    <select class="form-select" style="max-width:180px" id="pickerFolder" aria-label="Folder">
                        <option value="">All folders</option>
                        <?php foreach (\App\Libraries\MediaLibrary::FOLDERS as $f): ?><option value="<?= $f ?>"><?= ucfirst($f) ?></option><?php endforeach ?>
                    </select>
                    <label class="btn btn-light ms-auto mb-0"><i class="bi bi-upload"></i> Upload new<input type="file" id="pickerUpload" accept="image/*" hidden></label>
                </div>
                <div class="picker-grid" id="pickerGrid"></div>
                <p class="text-secondary small mt-3 mb-0" id="pickerStatus"></p>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset_url('js/admin.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
