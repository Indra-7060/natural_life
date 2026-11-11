<?php
/**
 * Lead article card. Expects $post; optional $label (text before the date), $heading (h2|h3), $class.
 * With a cover image the picture sits beside the text (never underneath it, because article
 * artwork often carries its own lettering); without one it is a typographic forest card.
 */
$tag  = ($heading ?? 'h3') === 'h2' ? 'h2' : 'h3';
$date = date('j F Y', strtotime((string) $post['published_at']));
$url  = site_url('blog/' . $post['slug']);
?>
<?php if ($post['image']): ?>
    <a class="post-card <?= esc($class ?? '', 'attr') ?>" href="<?= $url ?>">
        <span class="post-card__media"><img src="<?= media_url($post['image'], 'md') ?>" alt="" loading="lazy" width="900" height="900"></span>
        <span class="post-card__body">
            <span class="post-meta"><?= ! empty($label) ? esc($label) . ' · ' : '' ?><?= $date ?></span>
            <<?= $tag ?> class="post-card__title"><?= esc($post['title']) ?></<?= $tag ?>>
            <span class="post-card__text"><?= esc(plain($post['excerpt'], $limit ?? 170)) ?></span>
            <span class="link-arrow">Read article <i class="bi bi-arrow-right"></i></span>
        </span>
    </a>
<?php else: ?>
    <a class="post-lead <?= esc($class ?? '', 'attr') ?>" href="<?= $url ?>">
        <span class="post-lead__no" aria-hidden="true">01</span>
        <span class="post-meta"><?= ! empty($label) ? esc($label) . ' · ' : '' ?><?= $date ?></span>
        <<?= $tag ?> class="h3"><?= esc($post['title']) ?></<?= $tag ?>>
        <p><?= esc(plain($post['excerpt'], $limit ?? 170)) ?></p>
        <span class="link-arrow align-self-start">Read article <i class="bi bi-arrow-right"></i></span>
    </a>
<?php endif ?>
