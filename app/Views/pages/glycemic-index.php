<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$toc = [];
foreach (['what' => 'What is GI?', 'affects' => 'How GI affects you', 'load-link' => 'GI and Glycemic Load', 'gi-vs-gl' => 'GI vs GL', 'why' => 'Why Low GI', 'load' => 'What is Glycemic Load?'] as $k => $label) { if (isset($s[$k])) { $toc[$k] = $label; } }
$bands = array_map(static fn ($i) => [$i['title'] ?? '', $i['text'] ?? ''], section_items($s['scale'] ?? null));
?>
<?= $this->include('pages/_edu_hero') ?>
<section class="section">
    <div class="shell edu-layout">
        <ul class="toc" data-toc aria-label="On this page"><?php foreach ($toc as $k => $label): ?><li><a href="#<?= $k ?>"><?= esc($label) ?></a></li><?php endforeach ?></ul>
        <div>
            <?php if ($b = $s['what'] ?? null): ?>
            <div class="edu-block" id="what">
                <span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2><?= esc($b['heading']) ?></h2>
                <div class="prose prose--lg" style="max-width:66ch"><?= rich($b['body']) ?></div>
                <?php if ($sc = $s['scale'] ?? null): ?>
                    <p class="fw-semibold mt-5 mb-3"><?= esc($sc['heading']) ?></p>
                    <div style="max-width:720px"><?= view('partials/gi_scale', $bands ? ['bands' => $bands] : []) ?></div>
                <?php endif ?>
            </div>
            <?php endif ?>

            <?php if ($b = $s['affects'] ?? null): $paras = preg_split('#(?<=</p>)#', (string) $b['body'], -1, PREG_SPLIT_NO_EMPTY); ?>
            <div class="edu-block" id="affects">
                <span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2><?= esc($b['heading']) ?></h2>
                <div class="pair">
                    <div><b>High GI</b><div class="prose mt-2"><?= rich($paras[0] ?? '') ?></div></div>
                    <div><b>Low GI</b><div class="prose mt-2"><?= rich($paras[1] ?? '') ?></div></div>
                </div>
            </div>
            <?php endif ?>

            <?php if ($b = $s['load-link'] ?? null): ?>
            <div class="edu-block" id="load-link">
                <span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2><?= esc($b['heading']) ?></h2>
                <div class="stat-row mb-4" style="margin-top:0"><div><b>GI</b><span>measures the QUALITY of carbohydrates</span></div><div><b>GL</b><span>measures the QUANTITY of carbohydrates</span></div></div>
                <div class="prose" style="max-width:66ch"><?= rich($b['body']) ?></div>
            </div>
            <?php endif ?>

            <?php if ($b = $s['gi-vs-gl'] ?? null): $items = section_items($b); ?>
            <div class="edu-block" id="gi-vs-gl">
                <span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2><?= esc($b['heading']) ?></h2>
                <div class="pair"><?php foreach ($items as $it): ?><div><b><?= esc($it['title'] ?? '') ?></b><?= esc($it['text'] ?? '') ?></div><?php endforeach ?></div>
                <?php if ($b['image']): ?><figure class="figure-card mt-4" style="max-width:560px"><img src="<?= media_url($b['image'], 'md') ?>" alt="<?= esc($b['image_alt'], 'attr') ?>" loading="lazy"><figcaption><?= esc($b['image_alt']) ?></figcaption></figure><?php endif ?>
            </div>
            <?php endif ?>

            <?php if ($b = $s['why'] ?? null): ?>
            <div class="edu-block" id="why">
                <span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2 style="max-width:26ch"><?= esc($b['heading']) ?></h2>
                <div class="prose" style="max-width:66ch"><?= rich($b['body']) ?></div>
            </div>
            <?php endif ?>

            <?php if ($b = $s['load'] ?? null): ?>
            <div class="edu-block" id="load">
                <span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2><?= esc($b['heading']) ?></h2>
                <div class="prose" style="max-width:66ch"><?= rich($b['body']) ?></div>
                <div class="figure-card mt-4 text-center" style="max-width:560px"><p class="h3 mb-0">GL = GI × carbohydrate per serving</p><figcaption>The glycemic load is obtained by multiplying the quality of carbohydrate in a given food (GI) by the amount of carbohydrate in a serving of that food.</figcaption></div>
            </div>
            <?php endif ?>
        </div>
    </div>
</section>
<?= $this->include('pages/_edu_footer') ?>
<?= $this->endSection() ?>
