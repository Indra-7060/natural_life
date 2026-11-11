<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <?php if ($page['eyebrow']): ?><p class="eyebrow mt-4"><?= esc($page['eyebrow']) ?></p><?php endif ?>
        <h1><?= esc($page['title']) ?></h1>
        <?php if ($page['intro']): ?><p class="lede"><?= esc($page['intro']) ?></p><?php endif ?>
    </div>
</section>
<section class="section--tight">
    <div class="shell shell--narrow">
        <?php if ($page['hero_image']): ?><img class="mb-4" style="border-radius:var(--radius)" src="<?= media_url($page['hero_image'], 'lg') ?>" alt=""><?php endif ?>
        <article class="prose prose--lg"><?= rich($page['body']) ?></article>
        <?php foreach ($s as $sec): ?>
            <div class="edu-block">
                <?php if ($sec['eyebrow']): ?><span class="eyebrow"><?= esc($sec['eyebrow']) ?></span><?php endif ?>
                <?php if ($sec['heading']): ?><h2><?= esc($sec['heading']) ?></h2><?php endif ?>
                <?php if ($sec['image']): ?><img class="mb-3" style="border-radius:var(--radius)" src="<?= media_url($sec['image'], 'lg') ?>" alt="<?= esc((string) $sec['image_alt'], 'attr') ?>" loading="lazy"><?php endif ?>
                <div class="prose"><?= rich($sec['body']) ?></div>
                <?php if ($items = section_items($sec)): ?><ul class="tick-list mt-3"><?php foreach ($items as $it): ?><li><?= esc(implode(' — ', array_filter(array_map('strval', $it)))) ?></li><?php endforeach ?></ul><?php endif ?>
                <?php if ($sec['cta_label']): ?><a class="btn btn-line mt-3" href="<?= menu_href((string) $sec['cta_url']) ?>"><?= esc($sec['cta_label']) ?></a><?php endif ?>
            </div>
        <?php endforeach ?>
    </div>
</section>
<?= $this->endSection() ?>
