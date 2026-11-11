<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Pages</h1><a class="btn btn-primary" href="<?= site_url('admin/pages/new') ?>"><i class="bi bi-plus-lg"></i> Add page</a></div>
<p class="a-intro">Each page has its own layout. Open a page to edit its title, SEO details and content sections. The home page is edited under <a href="<?= site_url('admin/home') ?>">Home page</a>.</p>
<div class="panel">
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th>Page</th><th>Address</th><th>Layout</th><th>Sections</th><th>Status</th><th class="actions">Actions</th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?><tr>
            <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/pages/' . $r['id'] . '/edit') ?>"><?= esc($r['title']) ?></a></td>
            <td><code>/<?= esc($r['slug']) ?></code></td>
            <td><?= esc($templates[$r['template']] ?? $r['template']) ?></td>
            <td><?= (int) $r['section_count'] ?></td>
            <td><?= $r['is_published'] ? '<span class="badge-status badge-status--success">Published</span>' : '<span class="badge-status badge-status--secondary">Hidden</span>' ?></td>
            <td class="actions">
                <a class="btn-icon" href="<?= site_url($r['slug']) ?>" target="_blank" rel="noopener" title="View" aria-label="View on website"><i class="bi bi-eye"></i></a>
                <a class="btn-icon" href="<?= site_url('admin/pages/' . $r['id'] . '/edit') ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                <?php if (! $r['is_system'] && $r['template'] === 'default'): ?>
                    <form class="d-inline" method="post" action="<?= site_url('admin/pages/' . $r['id'] . '/delete') ?>" data-confirm="Delete the page “<?= esc($r['title'], 'attr') ?>”?"><?= csrf_field() ?><button class="btn-icon danger" type="submit" aria-label="Delete"><i class="bi bi-trash"></i></button></form>
                <?php endif ?>
            </td>
        </tr><?php endforeach ?></tbody>
    </table></div>
</div>
<?= $this->endSection() ?>
