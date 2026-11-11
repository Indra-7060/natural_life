<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$chapters = array_filter($s, static fn ($k) => str_starts_with($k, 'story-'), ARRAY_FILTER_USE_KEY);
$words    = ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight'];
$journey  = $s['journey'] ?? null;
$usps     = $s['usps'] ?? null;
?>
<section class="page-hero page-hero--forest" style="padding-bottom:clamp(56px,9vw,128px)">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow eyebrow--light mt-5"><?= esc($page['eyebrow']) ?> — <?= esc($page['intro'] ?: $page['title']) ?></p>
        <h1 class="opening-quote mt-3"><?= esc($s["hero"]["heading"] ?? $page["title"]) ?></h1>
        <?php if (! empty($s["hero"]["body"])): ?><p class="lede mt-4"><?= esc(plain($s["hero"]["body"])) ?></p><?php endif ?>
    </div>
</section>

<section class="section pb-0">
    <div class="shell">
        <?php $n = 0; foreach ($chapters as $c): ?>
            <article class="chapter reveal">
                <div class="chapter__no"><?= str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) ?><small>Chapter <?= $words[$n] ?? $n + 1 ?></small></div>
                <div>
                    <h2><?= esc($c['heading']) ?></h2>
                    <div class="prose <?= $n === 0 ? 'prose--lg' : '' ?>" style="max-width:68ch"><?= rich($c['body']) ?></div>
                </div>
            </article>
        <?php $n++; endforeach ?>
    </div>
</section>

<?php if (! empty($certs)): ?>
<section class="section--tight">
    <div class="shell">
        <div class="stat-row">
            <?php foreach ($certs as $cert): ?><div><b style="font-size:clamp(1.2rem,2vw,1.6rem)"><?= esc($cert['name']) ?></b><span><?= esc($cert['issuer_location']) ?></span></div><?php endforeach ?>
        </div>
        <a class="link-arrow mt-4" href="<?= site_url('certification') ?>">About our certifications <i class="bi bi-arrow-right"></i></a>
    </div>
</section>
<?php endif ?>

<?php if ($journey): ?>
<section class="section bg-cream">
    <div class="shell split split--wide-media" style="align-items:start">
        <div class="reveal" style="position:sticky;top:120px">
            <span class="eyebrow"><?= esc($journey['eyebrow']) ?></span>
            <h2 class="mt-3"><?= esc($journey['heading']) ?></h2>
        </div>
        <div class="prose reveal" style="max-width:68ch"><?= rich($journey['body']) ?></div>
    </div>
</section>
<?php endif ?>

<?php if ($usps): ?>
<section class="section bg-forest on-dark">
    <div class="shell">
        <div class="section-head reveal"><div><span class="eyebrow"><?= esc($usps['eyebrow']) ?></span><h2><?= esc($usps['heading']) ?></h2></div></div>
        <div class="numbered reveal"><?php foreach (section_items($usps) as $item): ?><div class="numbered__item"><p><?= esc($item['text'] ?? '') ?></p></div><?php endforeach ?></div>
    </div>
</section>
<?php endif ?>

<section class="section--tight">
    <div class="shell cta-band">
        <div><span class="eyebrow">Next</span><h2 class="mt-3 mb-0">Meet the people behind Sugarlif</h2></div>
        <div class="d-flex flex-wrap gap-2"><a class="btn btn-forest" href="<?= site_url('team') ?>">Our team <i class="bi bi-arrow-right"></i></a><a class="btn btn-line" href="<?= site_url('shop') ?>">Shop the range</a></div>
    </div>
</section>
<?= $this->endSection() ?>
