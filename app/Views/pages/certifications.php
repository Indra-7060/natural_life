<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php $req = $s['request'] ?? null; ?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow mt-4"><?= esc($page['eyebrow']) ?></p>
        <h1 style="max-width:20ch;font-size:clamp(2rem,4.4vw,3.6rem)"><?= esc($page['intro'] ?: $page['title']) ?></h1>
    </div>
</section>
<section class="section">
    <div class="shell">
        <div class="cert-list">
            <?php foreach ($certs as $i => $c): ?>
                <article class="cert-row reveal">
                    <div class="cert-row__no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
                    <div><h2><?= esc($c['name']) ?></h2><?php if ($c['issuer_location']): ?><div class="cert-row__where"><?= esc($c['issuer_location']) ?></div><?php endif ?>
                        <?php if ($c['image']): ?><a href="<?= media_url($c['image'], 'original') ?>" target="_blank" rel="noopener"><img src="<?= media_url($c['image'], 'md') ?>" alt="<?= esc($c['name'], 'attr') ?> certificate" loading="lazy"></a><?php endif ?>
                    </div>
                    <div><p><?= esc($c['description']) ?></p>
                        <?php if ($c['document']): ?><a class="link-arrow mt-3" href="<?= esc($c['document'], 'attr') ?>" target="_blank" rel="noopener">View document <i class="bi bi-arrow-up-right"></i></a><?php endif ?></div>
                </article>
            <?php endforeach ?>
        </div>
    </div>
</section>
<?php if ($req): ?>
<section class="section bg-forest on-dark">
    <div class="shell text-center">
        <p class="eyebrow eyebrow--plain eyebrow--light">Certificates &amp; reports</p>
        <h2 class="mx-auto mt-3" style="max-width:22ch"><?= esc($req['heading']) ?></h2>
        <p class="h3 mt-3" style="color:var(--sage-light)"><?= esc(plain($req['body'])) ?></p>
        <?php if ($req['cta_label']): ?><a class="btn btn-ivory btn-lg mt-3" href="<?= esc($req['cta_url'], 'attr') ?>"><i class="bi bi-envelope"></i> <?= esc($req['cta_label']) ?></a><?php endif ?>
    </div>
</section>
<?php endif ?>
<?= $this->endSection() ?>
