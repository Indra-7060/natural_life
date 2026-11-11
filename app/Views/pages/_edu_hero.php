<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow mt-4"><?= esc($page['eyebrow']) ?></p>
        <h1><?= esc($page['intro'] ?: $page['title']) ?></h1>
    </div>
</section>
