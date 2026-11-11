<?php if (! empty($breadcrumbs)): ?>
<nav aria-label="Breadcrumb">
    <ol class="crumbs">
        <?php foreach ($breadcrumbs as [$label, $url]): ?>
            <li><?php if ($url): ?><a href="<?= esc($url, 'attr') ?>"><?= esc($label) ?></a><?php else: ?><span aria-current="page"><?= esc($label) ?></span><?php endif ?></li>
        <?php endforeach ?>
    </ol>
</nav>
<?php endif ?>
