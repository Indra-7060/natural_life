<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $where = ['primary' => 'Header (desktop menu and mobile menu)', 'footer_company' => 'Footer — “Company” column', 'footer_learn' => 'Footer — “Low GI” column', 'footer_shop' => 'Footer — “Shop” column', 'legal' => 'Footer — bottom bar']; ?>
<div class="a-head"><h1>Navigation</h1></div>
<p class="a-intro">Choose a menu to add, rename, reorder or hide its links. The Shop drop-down in the header is built automatically from your categories and featured products.</p>
<div class="panel">
    <div class="table-scroll"><table class="a-table">
        <thead><tr><th>Menu</th><th>Where it appears</th><th>Links</th><th class="actions"></th></tr></thead>
        <tbody><?php foreach ($menus as $m): ?><tr>
            <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/menu-items') ?>?menu=<?= $m['id'] ?>"><?= esc($m['name']) ?></a></td>
            <td class="text-secondary"><?= esc($where[$m['location']] ?? $m['location']) ?></td>
            <td><?= (int) $m['item_count'] ?></td>
            <td class="actions"><a class="btn btn-light btn-sm" href="<?= site_url('admin/menu-items') ?>?menu=<?= $m['id'] ?>"><i class="bi bi-pencil"></i> Edit links</a></td>
        </tr><?php endforeach ?></tbody>
    </table></div>
</div>
<?= $this->endSection() ?>
