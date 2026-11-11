<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$a = $s['about'] ?? null; $t = $s['testing'] ?? null; $b = $s['benefits'] ?? null; $o = $s['outcomes'] ?? null;
$vr = $s['varieties'] ?? null; $c1 = $s['compare-1'] ?? null; $c2 = $s['compare-2'] ?? null; $shop = $s['shop'] ?? null;
$yn = static function (string $v): string {
    $l = strtolower(trim($v));
    if ($l === 'yes' || $l === 'no') { return '<span class="yn">' . ($l === 'yes' ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>') . ' ' . esc($v) . '</span>'; }
    return esc($v);
};
?>
<?php if ($a): ?>
<section class="section--tight">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <div class="split mt-4">
            <div>
                <span class="eyebrow"><?= esc($a['eyebrow']) ?></span>
                <h1 class="mt-3" style="font-size:clamp(2.2rem,4.6vw,3.8rem)"><?= esc($a['heading']) ?></h1>
                <div class="prose mt-4"><?= rich($a['body']) ?></div>
                <?php if ($stats = section_items($a)): ?><div class="stat-row"><?php foreach ($stats as $st): ?><div><b><?= esc($st['title'] ?? '') ?></b><span><?= esc($st['text'] ?? '') ?></span></div><?php endforeach ?></div><?php endif ?>
            </div>
            <?php if ($a['image']): ?><div class="framed framed--arch framed--square"><img src="<?= media_url($a['image'], 'lg') ?>" alt="<?= esc($a['image_alt'], 'attr') ?>" width="900" height="900" fetchpriority="high"></div><?php endif ?>
        </div>
    </div>
</section>
<?php endif ?>

<?php if ($t): ?>
<section class="section bg-forest on-dark">
    <div class="shell split split--wide-media" style="align-items:start">
        <div><span class="eyebrow"><?= esc($t['eyebrow']) ?></span><h2 class="mt-3"><?= esc($t['heading']) ?></h2></div>
        <div class="prose prose--lg" style="color:#dfe6d8"><?= rich($t['body']) ?></div>
    </div>
</section>
<?php endif ?>

<?php if ($vr): ?>
<section class="section--tight bg-cream">
    <div class="shell">
        <span class="eyebrow"><?= esc($vr['eyebrow']) ?></span><h2 class="mt-3 mb-4"><?= esc($vr['heading']) ?></h2>
        <ol class="index-list" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))"><?php foreach (section_items($vr) as $it): ?><li class="h4 mb-0" style="font-family:var(--font-display)"><?= esc($it['text'] ?? '') ?></li><?php endforeach ?></ol>
    </div>
</section>
<?php endif ?>

<?php if ($b || $o): ?>
<section class="section">
    <div class="shell <?= $b && $o ? 'split' : '' ?>" style="align-items:start">
        <?php if ($b): ?><div class="reveal"><span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2 class="mt-3 mb-4"><?= esc($b['heading']) ?></h2>
            <ul class="tick-list <?= $o ? '' : 'tick-list--2' ?>"><?php foreach (section_items($b) as $it): ?><li><?= esc($it['text'] ?? '') ?></li><?php endforeach ?></ul></div><?php endif ?>
        <?php if ($o): ?><div class="reveal"><span class="eyebrow"><?= esc($o['eyebrow']) ?></span><h2 class="mt-3 mb-4"><?= esc($o['heading']) ?></h2>
            <div class="numbered numbered--light" style="grid-template-columns:1fr"><?php foreach (section_items($o) as $it): ?><div class="numbered__item" style="padding:18px 0"><p><?= esc($it['text'] ?? '') ?></p></div><?php endforeach ?></div></div><?php endif ?>
    </div>
</section>
<?php endif ?>

<?php if ($c1): $rows = section_items($c1); $head = array_shift($rows); ?>
<section class="section bg-mist">
    <div class="shell shell--narrow">
        <span class="eyebrow"><?= esc($c1['eyebrow']) ?></span><h2 class="mt-3 mb-4"><?= esc($c1['heading']) ?></h2>
        <div class="ledger">
            <div class="ledger__head"><div><?= esc($head['a'] ?? '') ?></div><div><?= esc($head['b'] ?? '') ?></div></div>
            <?php foreach ($rows as $r): ?><div class="ledger__row"><div><i class="bi bi-check-lg" aria-hidden="true"></i><span><?= esc($r['a'] ?? '') ?></span></div><div><i class="bi bi-x-lg" aria-hidden="true"></i><span><?= esc($r['b'] ?? '') ?></span></div></div><?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>

<?php if ($c2): $rows = section_items($c2); $head = array_shift($rows); ?>
<section class="section">
    <div class="shell shell--narrow">
        <span class="eyebrow"><?= esc($c2['eyebrow']) ?></span><h2 class="mt-3 mb-4"><?= esc($c2['heading']) ?></h2>
        <div class="table-wrap">
            <table class="table-compare">
                <thead><tr><th scope="col"><?= esc($head['parameter'] ?? '') ?></th><th scope="col"><?= esc($head['artificial'] ?? '') ?></th><th scope="col"><?= esc($head['stevia'] ?? '') ?></th><th scope="col" class="is-brand"><?= esc($head['sugarlif'] ?? '') ?></th></tr></thead>
                <tbody><?php foreach ($rows as $r): ?><tr><th scope="row"><?= esc($r['parameter'] ?? '') ?></th><td><?= $yn($r['artificial'] ?? '') ?></td><td><?= $yn($r['stevia'] ?? '') ?></td><td class="is-brand"><?= $yn($r['sugarlif'] ?? '') ?></td></tr><?php endforeach ?></tbody>
            </table>
        </div>
    </div>
</section>
<?php endif ?>

<?= view('pages/_shop_cta', ['shop' => $shop, 'product' => $product ?? null]) ?>
<?= $this->endSection() ?>
