<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Search<?= $q !== '' ? ': “' . esc($q) . '”' : '' ?></h1></div>
<?php if (mb_strlen($q) < 2): ?>
    <div class="panel"><div class="empty"><i class="bi bi-search"></i>Type at least two characters in the search box above.</div></div>
<?php elseif (! $results): ?>
    <div class="panel"><div class="empty"><i class="bi bi-search"></i>No results for “<?= esc($q) ?>”.</div></div>
<?php else: foreach ($results as $group => $items): ?>
    <div class="panel">
        <div class="panel__head"><h2><?= esc($group) ?></h2><span class="text-secondary small"><?= count($items) ?> result(s)</span></div>
        <table class="a-table"><tbody>
            <?php foreach ($items as [$label, $url, $meta]): ?><tr><td><a class="fw-semibold text-decoration-none" href="<?= esc($url, 'attr') ?>"><?= esc($label) ?></a></td><td class="text-end text-secondary"><?= esc($meta) ?></td></tr><?php endforeach ?>
        </tbody></table>
    </div>
<?php endforeach; endif ?>
<?= $this->endSection() ?>
