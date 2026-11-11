<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php $r = $s['results'] ?? null; $c = $s['conclusions'] ?? null; $ref = $s['reference'] ?? null; ?>
<?= $this->include('pages/_edu_hero') ?>
<?php if ($r): ?>
<section class="section">
    <div class="shell">
        <div class="split split--wide-text" style="align-items:start">
            <div>
                <span class="eyebrow"><?= esc($r['eyebrow']) ?></span><h2 class="mt-3"><?= esc($r['heading']) ?></h2>
                <div class="table-wrap mt-4">
                    <table class="table-compare study-table">
                        <thead><tr><th scope="col">Measure</th><th scope="col">Ketogenic diet</th><th scope="col" class="is-brand">Low-GI nutrigenetic diet</th></tr></thead>
                        <tbody><?php foreach (section_items($r) as $row): ?><tr><th scope="row"><?= esc($row['measure'] ?? '') ?></th><td><?= esc($row['keto'] ?? '') ?></td><td class="is-brand"><?= esc($row['lowgi'] ?? '') ?></td></tr><?php endforeach ?></tbody>
                    </table>
                </div>
                <div class="prose mt-4 small"><?= rich($r['body']) ?></div>
            </div>
            <?php if ($r['image']): ?><figure class="figure-card reveal"><img src="<?= media_url($r['image'], 'md') ?>" alt="<?= esc($r['image_alt'], 'attr') ?>" loading="lazy"><figcaption><?= esc($r['image_alt']) ?></figcaption></figure><?php endif ?>
        </div>
    </div>
</section>
<?php endif ?>
<?php if ($c): ?>
<section class="section bg-forest on-dark">
    <div class="shell shell--narrow">
        <span class="eyebrow"><?= esc($c['eyebrow']) ?></span><h2 class="mt-3"><?= esc($c['heading']) ?></h2>
        <div class="prose prose--lg mt-4" style="color:#dfe6d8"><?= rich($c['body']) ?></div>
        <?php if ($ref && $ref['cta_url']): ?><p class="small mt-4 mb-0" style="color:var(--sage)"><?= esc($ref['heading']) ?>: <a href="<?= esc($ref['cta_url'], 'attr') ?>" target="_blank" rel="noopener"><?= esc($ref['cta_label']) ?></a></p><?php endif ?>
    </div>
</section>
<?php endif ?>
<?= $this->include('pages/_edu_footer') ?>
<?= $this->endSection() ?>
