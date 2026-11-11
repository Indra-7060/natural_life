<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$groups = [];
foreach ($members as $m) { $groups[$m['group']][] = $m; }
$first = true;
?>
<section class="page-hero page-hero--center">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow eyebrow--plain mt-4"><?= esc($page['eyebrow']) ?></p>
        <h1 class="mt-2"><?= esc($page['intro'] ?: $page['title']) ?></h1>
    </div>
</section>
<?php foreach ($groups as $group => $people): ?>
<section class="section<?= $first ? '' : '--tight bg-cream' ?>">
    <div class="shell">
        <div class="section-head"><div><span class="eyebrow"><?= $first ? 'Leadership' : 'Team' ?></span><h2><?= esc($group) ?></h2></div></div>
        <div class="<?= $first ? 'team-lead' : 'team-rest' ?>" <?= $first ? 'style="max-width:920px;margin-inline:auto"' : '' ?>>
            <?php foreach ($people as $m): ?>
                <figure class="person <?= $first ? 'person--lead' : '' ?> reveal">
                    <div class="person__img"><img src="<?= media_url($m['image'], 'md') ?>" alt="<?= esc($m['name'], 'attr') ?>" loading="lazy" width="500" height="575"></div>
                    <figcaption><?php if ($m['designation']): ?><span><?= esc($m['designation']) ?></span><?php endif ?><b><?= esc($m['name']) ?></b>
                        <?php if ($m['bio']): ?><p class="muted mt-2 mb-0"><?= esc($m['bio']) ?></p><?php endif ?></figcaption>
                </figure>
            <?php endforeach ?>
        </div>
    </div>
</section>
<?php $first = false; endforeach ?>
<section class="section--tight">
    <div class="shell cta-band">
        <div><span class="eyebrow">Our story</span><h2 class="mt-3 mb-0">It all began with one question…</h2></div>
        <a class="btn btn-forest" href="<?= site_url('about-naturalife') ?>">Read our story <i class="bi bi-arrow-right"></i></a>
    </div>
</section>
<?= $this->endSection() ?>
