<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php $b = $s['benefits'] ?? null; $v = $s['versus'] ?? null; $m = $s['mechanism'] ?? null; ?>
<?= $this->include('pages/_edu_hero') ?>
<?php if ($b): ?>
<section class="section">
    <div class="shell">
        <div class="section-head"><div><span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2 style="max-width:24ch"><?= esc(plain($b['body'])) ?>…</h2></div></div>
        <ol class="index-list reveal"><?php foreach (section_items($b) as $it): ?><li><?= esc($it['text'] ?? '') ?></li><?php endforeach ?></ol>
    </div>
</section>
<?php endif ?>
<?php if ($v && $v['image']): ?>
<section class="section bg-mist">
    <div class="shell split split--wide-media">
        <div><span class="eyebrow"><?= esc($v['eyebrow']) ?></span><h2 class="mt-3"><?= esc($v['heading']) ?></h2>
            <?php if ($v["body"]): ?><div class="lede mt-3"><?= rich($v["body"]) ?></div><?php endif ?>
            <a class="link-arrow mt-2" href="<?= site_url('glycemic-index') ?>">How the Glycemic Index works <i class="bi bi-arrow-right"></i></a></div>
        <figure class="figure-card reveal"><img src="<?= media_url($v['image'], 'md') ?>" alt="<?= esc($v['image_alt'], 'attr') ?>" loading="lazy"></figure>
    </div>
</section>
<?php endif ?>
<?php if ($m): ?>
<section class="section">
    <div class="shell split">
        <?php if ($m['image']): ?><figure class="figure-card reveal"><img src="<?= media_url($m['image'], 'md') ?>" alt="<?= esc($m['image_alt'], 'attr') ?>" loading="lazy"></figure><?php endif ?>
        <div class="reveal"><span class="eyebrow"><?= esc($m['eyebrow']) ?></span><h2 class="mt-3"><?= esc($m['heading']) ?></h2><div class="prose mt-4"><?= rich($m['body']) ?></div></div>
    </div>
</section>
<?php endif ?>
<?= $this->include('pages/_edu_footer') ?>
<?= $this->endSection() ?>
