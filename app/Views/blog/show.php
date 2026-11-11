<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<article>
    <header class="page-hero page-hero--center">
        <div class="shell article-head">
            <?= $this->include('partials/breadcrumbs') ?>
            <p class="post-meta mt-4"><?= date('j F Y', strtotime((string) $post['published_at'])) ?> · <?= (int) $readMinutes ?> min read<?= $post['category_name'] ? ' · ' . esc($post['category_name']) : '' ?></p>
            <h1><?= esc($post['title']) ?></h1>
        </div>
    </header>
    <div class="section--tight">
        <div class="shell">
            <?php if ($post['image']): ?><img class="d-block mx-auto mb-5" style="border-radius:var(--radius);max-width:min(100%,720px)" src="<?= media_url($post['image'], 'lg') ?>" alt="" width="1254" height="1254"><?php endif ?>
            <div class="article prose prose--lg"><?= rich($post['content']) ?></div>
            <div class="article mt-5 pt-4" style="border-top:1px solid var(--rule)">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                    <a class="link-arrow" href="<?= site_url('blog') ?>"><i class="bi bi-arrow-left"></i> All articles</a>
                    <a class="btn btn-forest" href="<?= site_url('shop') ?>">Shop Low GI foods <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</article>
<?php if ($more): ?>
<section class="section bg-cream">
    <div class="shell">
        <div class="section-head"><div><span class="eyebrow">Keep reading</span><h2>More from the journal</h2></div></div>
        <?php foreach ($more as $i => $p): ?>
            <a class="post-row" href="<?= site_url('blog/' . $p['slug']) ?>"><span class="post-row__no" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <span><span class="post-meta"><?= date('j F Y', strtotime((string) $p['published_at'])) ?></span><h3 class="mt-1"><?= esc($p['title']) ?></h3><p><?= esc(plain($p['excerpt'], 150)) ?></p></span></a>
        <?php endforeach ?>
    </div>
</section>
<?php endif ?>
<?= $this->endSection() ?>
