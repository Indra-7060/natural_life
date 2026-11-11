<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php $a = $s['applications'] ?? null; ?>
<section class="page-hero page-hero--forest">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow eyebrow--light mt-4"><?= esc($page['eyebrow']) ?></p>
        <h1><?= esc($page['intro'] ?: $page['title']) ?></h1>
    </div>
</section>
<?php if ($a): ?>
<section class="section">
    <div class="shell split split--wide-text" style="align-items:start">
        <ol class="app-list"><?php foreach (section_items($a) as $it): ?><li class="reveal"><span><?= esc($it['text'] ?? '') ?></span></li><?php endforeach ?></ol>
        <?php if ($a['image']): ?><div class="framed framed--square reveal" style="position:sticky;top:120px"><img src="<?= media_url($a['image'], 'lg') ?>" alt="<?= esc($a['image_alt'], 'attr') ?>" loading="lazy" width="900" height="900"></div><?php endif ?>
    </div>
</section>
<?php endif ?>
<?= view('pages/_shop_cta', ['shop' => $s['shop'] ?? null, 'product' => null]) ?>
<?= $this->endSection() ?>
