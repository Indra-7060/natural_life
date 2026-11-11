<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
// Build an in-page table of contents from the policy's own headings.
$n    = 0;
$toc  = [];
$body = preg_replace_callback('#<h2>(.*?)</h2>#s', static function ($m) use (&$n, &$toc) {
    $id = 'section-' . (++$n);
    $toc[$id] = plain($m[1], 60);
    return '<h2 id="' . $id . '" style="scroll-margin-top:110px">' . $m[1] . '</h2>';
}, rich($page['body']));
?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow mt-4"><?= esc($page['eyebrow'] ?: 'Legal') ?></p>
        <h1 style="font-size:clamp(2rem,4.4vw,3.4rem);max-width:20ch"><?= esc($page['title']) ?></h1>
    </div>
</section>
<section class="section--tight">
    <div class="shell legal">
        <ul class="toc" data-toc aria-label="On this page">
            <?php foreach ($toc as $id => $label): ?><li><a href="#<?= $id ?>"><?= esc($label) ?></a></li><?php endforeach ?>
            <li class="mt-3"><a href="<?= site_url('contact-us') ?>">Questions? Contact us</a></li>
        </ul>
        <article class="prose"><?= $body ?></article>
    </div>
</section>
<?= $this->endSection() ?>
