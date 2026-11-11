<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$page1 = $pager->getCurrentPage('blog') === 1;
$lead  = ($page1 && $posts) ? $posts[0] : null;
$rest  = $lead ? array_slice($posts, 1) : $posts;
?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow mt-4">The Sugarlif Journal</p>
        <h1><?= esc($category['name'] ?? 'Sugar, explained with a smile') ?></h1>
        <?php if (count($categories) > 1): ?>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a class="chip" href="<?= site_url('blog') ?>">All</a>
                <?php foreach ($categories as $c): ?><a class="chip" href="<?= site_url('blog/category/' . $c['slug']) ?>"><?= esc($c['name']) ?></a><?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>
<section class="section--tight">
    <div class="shell">
        <?php if (! $posts): ?>
            <div class="empty-state"><i class="bi bi-journal-text"></i><h2>No articles yet</h2><p>Please check back soon.</p></div>
        <?php endif ?>
        <?php if ($lead): ?>
            <?= view('partials/post_lead', ['post' => $lead, 'label' => 'Latest', 'heading' => 'h2', 'class' => 'mb-5 post-card--wide reveal', 'limit' => 240]) ?>
        <?php endif ?>
        <div class="blog-list">
            <?php foreach ($rest as $i => $post): ?>
                <a class="blog-card" href="<?= site_url('blog/' . $post['slug']) ?>">
                    <span class="blog-card__no" aria-hidden="true"><?= str_pad((string) ($i + ($lead ? 2 : 1)), 2, '0', STR_PAD_LEFT) ?></span>
                    <span>
                        <span class="post-meta"><?= date('j F Y', strtotime((string) $post['published_at'])) ?><?= $post['category_name'] ? ' · ' . esc($post['category_name']) : '' ?></span>
                        <h2><?= esc($post['title']) ?></h2>
                        <p><?= esc(plain($post['excerpt'], 170)) ?></p>
                        <span class="link-arrow">Read article <i class="bi bi-arrow-right"></i></span>
                    </span>
                </a>
            <?php endforeach ?>
        </div>
        <?= $pager->links('blog', 'site') ?>
    </div>
</section>
<?= $this->endSection() ?>
