<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$sec  = static fn (string $k) => $s[$k] ?? null;
$leaf = '<svg viewBox="0 0 200 260" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M100 250C100 170 96 90 100 10"/><path d="M100 60C70 52 44 30 34 6c30 2 56 18 66 44M100 60c30-8 56-30 66-54-30 2-56 18-66 44M100 118C64 110 34 84 22 54c36 4 66 24 78 54M100 118c36-8 66-34 78-64-36 4-66 24-78 54M100 180C60 172 28 142 16 108c40 6 72 28 84 62M100 180c40-8 72-38 84-72-40 6-72 28-84 62"/></svg>';
?>

<!-- ============================ HERO ============================ -->
<section class="herox" data-hero aria-roledescription="carousel" aria-label="Featured">
    <div class="shell">
        <div class="herox__stage">
            <div class="herox__media" aria-hidden="true">
                <?php foreach ($banners as $i => $b): ?>
                    <img class="herox__img <?= $i === 0 ? 'is-active' : '' ?>" data-hero-img src="<?= media_url($b['image'], 'lg') ?>" alt="" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> width="1600" height="770">
                <?php endforeach ?>
            </div>
            <div class="herox__shade" aria-hidden="true"></div>

            <div class="herox__content">
                <?php foreach ($banners as $i => $b): ?>
                    <div class="herox__slide <?= $i === 0 ? 'is-active' : '' ?>" data-hero-slide <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
                        <?php if ($b['eyebrow']): ?><span class="eyebrow eyebrow--light"><?= esc($b['eyebrow']) ?></span><?php endif ?>
                        <<?= $i === 0 ? 'h1' : 'p' ?> class="herox__title h-display"><?= esc($b['heading']) ?></<?= $i === 0 ? 'h1' : 'p' ?>>
                        <?php if ($b['subheading']): ?><p class="herox__sub"><?= esc($b['subheading']) ?></p><?php endif ?>
                        <div class="herox__actions">
                            <?php if ($b['cta_label']): ?><a class="btn btn-ivory btn-lg" href="<?= menu_href($b['cta_url'] ?: '/shop') ?>" <?= $i === 0 ? '' : 'tabindex="-1"' ?>><?= esc($b['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
                            <a class="link-arrow" href="<?= site_url('glycemic-index') ?>" <?= $i === 0 ? '' : 'tabindex="-1"' ?>>What is Low GI? <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>

            <?php if (count($banners) > 1): ?>
                <div class="herox__controls">
                    <div class="herox__dots" role="tablist" aria-label="Choose slide">
                        <?php foreach ($banners as $i => $b): ?>
                            <button class="herox__dot <?= $i === 0 ? 'is-active' : '' ?>" type="button" role="tab" data-hero-dot="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>: <?= esc($b['eyebrow'] ?: $b['heading'], 'attr') ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                        <?php endforeach ?>
                    </div>
                </div>
            <?php endif ?>
            <div class="herox__badge" aria-hidden="true"><div><b>Low GI</b><span>Tested at<br>GI Labs, Canada</span></div></div>
        </div>

        <?php if ($p = $sec('proof')): $icons = ['patch-check', 'hourglass-split', 'shield-check', 'emoji-smile']; ?>
            <div class="proofx reveal-group">
                <?php foreach (section_items($p) as $n => $item): ?>
                    <div class="proofx__item"><i class="bi bi-<?= $icons[$n % 4] ?>" aria-hidden="true"></i><div><b><?= esc($item['title'] ?? '') ?></b><span><?= esc($item['text'] ?? '') ?></span></div></div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<!-- ============================ FEATURED COLLECTION ============================ -->
<?php if (($f = $sec('featured')) && $featured): $lead = $featured[0]; $rest = array_slice($featured, 1); ?>
<section class="section">
    <div class="shell">
        <div class="section-head reveal">
            <div><span class="eyebrow"><?= esc($f['eyebrow']) ?></span><h2><?= esc($f['heading']) ?></h2></div>
            <div><?= rich($f['body']) ?><?php if ($f['cta_label']): ?><a class="link-arrow mt-3" href="<?= menu_href($f['cta_url']) ?>"><?= esc($f['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?></div>
        </div>
        <div class="showcase">
            <a class="showcase__lead reveal reveal--left" href="<?= site_url('product/' . $lead['slug']) ?>" data-zoom>
                <img src="<?= media_url($lead['image'], 'lg') ?>" alt="<?= esc($lead['name'], 'attr') ?>" loading="lazy" width="1254" height="1254">
                <span class="showcase__body">
                    <span class="badge-tag badge-tag--soft">Our original</span>
                    <span class="showcase__name"><?= esc($lead['name']) ?></span>
                    <span class="showcase__tag"><?= esc($lead['tagline']) ?></span>
                    <span class="showcase__foot"><span><?= product_price_html($lead) ?></span><span class="btn btn-ivory btn-sm">Choose pack <i class="bi bi-arrow-right"></i></span></span>
                </span>
            </a>
            <div class="showcase__grid reveal-group">
                <?php foreach ($rest as $p): ?><?= view('partials/product_card', ['p' => $p, 'wishlistIds' => $wishlistIds]) ?><?php endforeach ?>
                <div class="showcase__note">
                    <div>
                        <i class="bi bi-truck" aria-hidden="true"></i>
                        <p class="h3 mt-3 mb-2"><?= esc(setting('announcement_text')) ?></p>
                        <p class="muted mb-0"><?= esc(setting('shipping_region_note')) ?> Orders are processed in <?= esc(setting('order_processing_time', '1 business day')) ?>.</p>
                    </div>
                    <a class="link-arrow mt-4 align-self-start" href="<?= site_url('shop') ?>">Shop the full range <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ SHOP BY CATEGORY ============================ -->
<?php if (($c = $sec('categories')) && $categories): ?>
<section class="section--tight">
    <div class="shell">
        <div class="section-head reveal"><div><span class="eyebrow"><?= esc($c['eyebrow']) ?></span><h2><?= esc($c['heading']) ?></h2></div></div>
        <div class="cat-list reveal-group">
            <?php foreach ($categories as $i => $cat): ?>
                <a class="cat-row" href="<?= site_url('product-category/' . $cat['slug']) ?>">
                    <span class="cat-row__no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="cat-row__name"><?= esc($cat['name']) ?></span>
                    <span class="cat-row__desc"><?= esc($cat['description']) ?> · <?= (int) $cat['product_count'] ?> <?= (int) $cat['product_count'] === 1 ? 'product' : 'products' ?></span>
                    <span class="cat-row__arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                    <?php if ($cat['image'] || $cat['cover']): ?><span class="cat-row__thumb" aria-hidden="true"><img src="<?= media_url($cat['image'] ?: $cat['cover'], 'sm') ?>" alt="" loading="lazy" width="340" height="340"></span><?php endif ?>
                </a>
            <?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ OUR STORY ============================ -->
<?php if ($st = $sec('story')): ?>
<section class="section bg-cream">
    <div class="shell split">
        <div class="framed framed--story reveal reveal--left" data-zoom>
            <span class="framed__clip"><img src="<?= media_url($st['image'], 'lg') ?>" alt="<?= esc($st['image_alt'], 'attr') ?>" loading="lazy" width="900" height="1035"></span>
        </div>
        <div class="reveal">
            <span class="eyebrow"><?= esc($st['eyebrow']) ?></span>
            <h2 class="mt-3"><?= esc($st['heading']) ?></h2>
            <div class="prose mt-4"><?= rich($st['body']) ?></div>
            <?php if ($st['cta_label']): ?><a class="btn btn-line mt-4" href="<?= menu_href($st['cta_url']) ?>"><?= esc($st['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ WHAT MAKES SUGARLIF DIFFERENT ============================ -->
<?php if ($d = $sec('difference')): ?>
<section class="section bg-forest on-dark band">
    <div class="band__leaf"><?= $leaf ?></div>
    <div class="shell">
        <div class="section-head reveal"><div><span class="eyebrow"><?= esc($d['eyebrow']) ?></span><h2><?= esc($d['heading']) ?></h2></div></div>
        <div class="numbered reveal-group">
            <?php foreach (section_items($d) as $item): ?><div class="numbered__item"><p><?= esc($item['text'] ?? '') ?></p></div><?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ LOW GI EDUCATION ============================ -->
<?php if ($g = $sec('lowgi')): ?>
<section class="section">
    <div class="shell split split--wide-media">
        <div class="reveal">
            <span class="eyebrow"><?= esc($g['eyebrow']) ?></span>
            <h2 class="mt-3"><?= esc($g['heading']) ?></h2>
            <div class="prose mt-4"><?= rich($g['body']) ?></div>
            <?php foreach (section_items($g) as $item): ?>
                <div class="def-card"><h3><?= esc($item['title'] ?? '') ?></h3><p><?= esc($item['text'] ?? '') ?></p></div>
            <?php endforeach ?>
            <?php if ($g['cta_label']): ?><a class="link-arrow mt-3" href="<?= menu_href($g['cta_url']) ?>"><?= esc($g['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
        </div>
        <div class="reveal"><?= view('partials/gi_scale') ?></div>
    </div>
</section>
<?php endif ?>

<!-- ============================ BENEFITS + COMPARISON ============================ -->
<?php $bn = $sec('benefits'); $cmp = $sec('compare'); if ($bn || $cmp): ?>
<section class="section bg-mist">
    <div class="shell">
        <div class="benefits-stack">
            <?php if ($bn): ?>
            <div class="reveal">
                <span class="eyebrow"><?= esc($bn['eyebrow']) ?></span>
                <h2 class="mt-3"><?= esc($bn['heading']) ?></h2>
                <?php $bi = ['activity', 'graph-down-arrow', 'shield-check', 'droplet-half', 'lightning-charge', 'heart-pulse']; ?>
                <div class="benefit-cards reveal-group mt-4">
                    <?php foreach (section_items($bn) as $n => $item): ?><div class="benefit-card"><i class="bi bi-<?= $bi[$n % 6] ?>" aria-hidden="true"></i><span><?= esc($item['text'] ?? '') ?></span></div><?php endforeach ?>
                </div>
                <?php if ($bn['cta_label']): ?><a class="link-arrow mt-4" href="<?= menu_href($bn['cta_url']) ?>"><?= esc($bn['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
            </div>
            <?php endif ?>
            <?php if ($cmp): ?>
            <div class="reveal">
                <span class="eyebrow"><?= esc($cmp['eyebrow']) ?></span>
                <h3 class="h2 mt-3 mb-4" style="font-size:clamp(1.5rem,2.6vw,2.2rem)"><?= esc($cmp['heading']) ?></h3>
                <div class="ledger">
                    <div class="ledger__head"><div>Sugarlif® Low GI Foods</div><div>High GI Foods</div></div>
                    <?php foreach (section_items($cmp) as $row): ?>
                        <div class="ledger__row">
                            <div><i class="bi bi-check-lg" aria-hidden="true"></i><span><?= esc($row['low'] ?? '') ?></span></div>
                            <div><i class="bi bi-x-lg" aria-hidden="true"></i><span><?= esc($row['high'] ?? '') ?></span></div>
                        </div>
                    <?php endforeach ?>
                </div>
                <p class="gi-note mt-3">Sugarlif is not a medication. Please continue to work with your doctor or dietician. <a href="<?= site_url('faq') ?>">Read the FAQ</a>.</p>
            </div>
            <?php endif ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ RESEARCH & CERTIFICATION ============================ -->
<?php if ($r = $sec('research')): ?>
<section class="section bg-forest on-dark band">
    <div class="band__leaf"><?= $leaf ?></div>
    <div class="shell">
        <div class="split split--wide-text reveal" style="align-items:end">
            <div><span class="eyebrow"><?= esc($r['eyebrow']) ?></span><h2 class="mt-3 mb-0"><?= esc($r['heading']) ?></h2></div>
            <div><div class="prose" style="color:#d5ddce"><?= rich($r['body']) ?></div>
                <?php if ($r['cta_label']): ?><a class="btn btn-line mt-3" href="<?= menu_href($r['cta_url']) ?>"><?= esc($r['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?></div>
        </div>
        <?php if ($certs): ?>
            <div class="cert-inline reveal-group">
                <?php foreach ($certs as $cert): ?><div><b><?= esc($cert['name']) ?></b><span><?= esc($cert['issuer_location']) ?></span></div><?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>
<?php endif ?>

<!-- ============================ SECOND PRODUCT ROW ============================ -->
<?php if (($b = $sec('bestsellers')) && $bestsellers): ?>
<section class="section">
    <div class="shell">
        <div class="section-head reveal">
            <div><span class="eyebrow"><?= esc($b['eyebrow']) ?></span><h2><?= esc($b['heading']) ?></h2></div>
            <a class="link-arrow" href="<?= site_url('shop') ?>">View all products <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="product-grid product-grid--4 reveal-group">
            <?php foreach ($bestsellers as $p): ?><?= view('partials/product_card', ['p' => $p, 'wishlistIds' => $wishlistIds]) ?><?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ FULL-WIDTH VISUAL ============================ -->
<?php if (($v = $sec('visual')) && $v['image']): ?>
<section class="visual" data-zoom="in">
    <img src="<?= media_url($v['image'], 'lg') ?>" alt="<?= esc($v['image_alt'], 'attr') ?>" loading="lazy" width="1600" height="653">
    <div class="shell">
        <div class="visual__body reveal">
            <span class="eyebrow eyebrow--light"><?= esc($v['eyebrow']) ?></span>
            <h2><?= esc($v['heading']) ?></h2>
            <?php if ($v['cta_label']): ?><a class="btn btn-ivory" href="<?= menu_href($v['cta_url']) ?>"><?= esc($v['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ TESTIMONIALS ============================ -->
<?php if (($t = $sec('testimonials')) && $testimonials): ?>
<section class="section" data-quotes>
    <div class="shell quotes">
        <div class="reveal">
            <span class="eyebrow"><?= esc($t['eyebrow']) ?></span>
            <h2 class="mt-3"><?= esc($t['heading']) ?></h2>
            <div class="round-nav">
                <button class="round-btn" type="button" data-quotes-prev aria-label="Previous testimonial"><i class="bi bi-arrow-left"></i></button>
                <button class="round-btn" type="button" data-quotes-next aria-label="Next testimonial"><i class="bi bi-arrow-right"></i></button>
            </div>
            <p class="muted small mt-3 mb-0"><span data-quotes-current>1</span> of <?= count($testimonials) ?></p>
        </div>
        <div class="quotes__stage reveal reveal--right" aria-live="polite">
            <?php foreach ($testimonials as $i => $q): ?>
                <figure class="quote <?= $i === 0 ? 'is-active' : '' ?>" data-quote <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
                    <span class="quote__mark" aria-hidden="true">“</span>
                    <?php if ($q['title']): ?><div class="quote__title"><?= esc($q['title']) ?></div><?php endif ?>
                    <blockquote <?= preg_match('/[\x{0900}-\x{097F}]/u', $q['content']) ? 'lang="hi"' : '' ?>><?= nl2br(esc($q['content'])) ?></blockquote>
                    <figcaption>
                        <span class="avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($q['name'], 0, 1))) ?></span>
                        <span><b><?= esc($q['name']) ?></b><?= esc($q['meta']) ?><br><span class="stars" aria-label="Rated <?= (int) $q['rating'] ?> out of 5"><?= str_repeat('★', (int) $q['rating']) ?></span></span>
                    </figcaption>
                </figure>
            <?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ JOURNAL ============================ -->
<?php if (($j = $sec('journal')) && $posts): $lead = $posts[0]; ?>
<section class="section bg-cream">
    <div class="shell">
        <div class="section-head reveal">
            <div><span class="eyebrow"><?= esc($j['eyebrow']) ?></span><h2><?= esc($j['heading']) ?></h2></div>
            <?php if ($j['cta_label']): ?><a class="link-arrow" href="<?= menu_href($j['cta_url']) ?>"><?= esc($j['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
        </div>
        <div class="jpanel reveal">
            <a class="jpanel__lead" href="<?= site_url('blog/' . $lead['slug']) ?>">
                <?php if ($lead['image']): ?>
                    <span class="jpanel__thumb"><img src="<?= media_url($lead['image'], 'md') ?>" alt="" loading="lazy" width="600" height="600"></span>
                <?php endif ?>
                <span class="jpanel__leadtext">
                    <span class="jpanel__kicker"><i class="bi bi-stars" aria-hidden="true"></i> Latest article · <?= date('j F Y', strtotime((string) $lead['published_at'])) ?></span>
                    <h3 class="jpanel__title"><?= esc($lead['title']) ?></h3>
                    <span class="jpanel__excerpt"><?= esc(plain($lead['excerpt'], 150)) ?></span>
                    <span class="btn btn-ivory">Read article <i class="bi bi-arrow-right"></i></span>
                </span>
            </a>
            <div class="jpanel__list">
                <span class="jpanel__listhead">More to read</span>
                <?php foreach (array_slice($posts, 1) as $i => $post): ?>
                    <a class="jrow" href="<?= site_url('blog/' . $post['slug']) ?>">
                        <span class="jrow__no" aria-hidden="true"><?= str_pad((string) ($i + 2), 2, '0', STR_PAD_LEFT) ?></span>
                        <span class="jrow__text">
                            <span class="post-meta"><?= date('j F Y', strtotime((string) $post['published_at'])) ?></span>
                            <h3 class="jrow__title"><?= esc($post['title']) ?></h3>
                            <span class="jrow__excerpt"><?= esc(plain($post['excerpt'], 95)) ?></span>
                        </span>
                        <span class="jrow__arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                    </a>
                <?php endforeach ?>
                <?php if ($j['cta_label']): ?><a class="link-arrow jpanel__all" href="<?= menu_href($j['cta_url']) ?>"><?= esc($j['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
            </div>
        </div>
    </div>
</section>
<?php endif ?>

<!-- ============================ FAQ ============================ -->
<?php if (($q = $sec('faq')) && $faqs): ?>
<section class="section">
    <div class="shell split split--wide-media" style="align-items:start">
        <div class="reveal">
            <span class="eyebrow"><?= esc($q['eyebrow']) ?></span>
            <h2 class="mt-3"><?= esc($q['heading']) ?></h2>
            <?php if ($q['cta_label']): ?><a class="btn btn-line mt-4" href="<?= menu_href($q['cta_url']) ?>"><?= esc($q['cta_label']) ?> <i class="bi bi-arrow-right"></i></a><?php endif ?>
        </div>
        <div class="faq reveal reveal--right" id="homeFaq">
            <?php foreach ($faqs as $i => $faq): ?>
                <div class="faq__item">
                    <h3 class="m-0"><button class="faq__q" type="button" data-bs-toggle="collapse" data-bs-target="#hfaq<?= $i ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="hfaq<?= $i ?>"><span><?= esc($faq['question']) ?></span><i class="bi bi-plus-lg" aria-hidden="true"></i></button></h3>
                    <div id="hfaq<?= $i ?>" class="collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#homeFaq"><div class="faq__a prose"><?= rich($faq['answer']) ?></div></div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>
<?php endif ?>

<?= $this->endSection() ?>
