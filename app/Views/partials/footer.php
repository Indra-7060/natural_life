<?php
$markets = array_filter([
    'Amazon'   => setting('marketplace_amazon'),
    'Flipkart' => setting('marketplace_flipkart'),
    'JioMart'  => setting('marketplace_jiomart'),
    'Tata 1mg' => setting('marketplace_1mg'),
]);
$social = array_filter([
    'facebook'  => setting('social_facebook'),
    'instagram' => setting('social_instagram'),
    'youtube'   => setting('social_youtube'),
    'linkedin'  => setting('social_linkedin'),
]);
$columns = ['Shop' => site_menu('footer_shop'), 'Company' => site_menu('footer_company'), 'Low GI' => site_menu('footer_learn')];
?>
<section class="section--tight bg-cream" aria-labelledby="newsletterTitle">
    <div class="shell newsletter">
        <div>
            <span class="eyebrow">Newsletter</span>
            <h2 class="mt-3 mb-0" id="newsletterTitle"><?= esc(setting('newsletter_heading', 'Sign up for our newsletter')) ?></h2>
        </div>
        <div>
            <p class="mb-3"><?= esc(setting('newsletter_text')) ?></p>
            <form class="newsletter-form" action="<?= site_url('newsletter/subscribe') ?>" method="post" data-newsletter>
                <?= csrf_field() ?>
                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                <label class="visually-hidden" for="newsletterEmail">Email address</label>
                <input type="email" id="newsletterEmail" name="email" placeholder="Email address" required maxlength="190" autocomplete="email">
                <button type="submit">Subscribe <i class="bi bi-arrow-right"></i></button>
            </form>
            <div class="form-note" data-newsletter-note role="status"></div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="shell">
        <div class="footer-top">
            <div class="footer-brand">
                <img src="<?= media_url(setting('logo_light'), 'original') ?>" alt="<?= esc(setting('site_name', 'Sugarlif'), 'attr') ?>" width="166" height="52" loading="lazy">
                <p><?= esc(setting('footer_about')) ?></p>
                <?php if (setting('innovator_url')): ?>
                    <a class="link-arrow" href="<?= esc(setting('innovator_url'), 'attr') ?>" target="_blank" rel="noopener"><?= esc(setting('innovator_label', 'Innovators website')) ?> <i class="bi bi-arrow-up-right"></i></a>
                <?php endif ?>
                <?php if ($social): ?>
                    <div class="footer-social">
                        <?php foreach ($social as $name => $url): ?><a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener" aria-label="<?= ucfirst($name) ?>"><i class="bi bi-<?= $name ?>"></i></a><?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>

            <?php foreach ($columns as $title => $items): ?>
                <nav aria-label="<?= esc($title, 'attr') ?>">
                    <details class="footer-col" open>
                    <summary><h6><?= esc($title) ?></h6><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <ul class="footer-links">
                        <?php foreach ($items as $item): ?><li><a href="<?= menu_href($item['url']) ?>"><?= esc($item['label']) ?></a></li><?php endforeach ?>
                    </ul>
                    </details>
                </nav>
            <?php endforeach ?>

            <div>
                <details class="footer-col" open>
                <summary><h6>Contact</h6><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                <ul class="footer-contact">
                    <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span><?= esc(setting('company_name')) ?><br><?= esc(setting('address_line1')) ?>,<br><?= esc(setting('address_city')) ?> <?= esc(setting('address_postcode')) ?>. <?= esc(setting('address_state')) ?></span></li>
                    <li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', setting('contact_phone')), 'attr') ?>"><?= esc(setting('contact_phone')) ?></a></li>
                    <li><i class="bi bi-envelope" aria-hidden="true"></i><span><a href="mailto:<?= esc(setting('contact_email_primary'), 'attr') ?>"><?= esc(setting('contact_email_primary')) ?></a><?php if (setting('contact_email_sales')): ?><br><a href="mailto:<?= esc(setting('contact_email_sales'), 'attr') ?>"><?= esc(setting('contact_email_sales')) ?></a><?php endif ?></span></li>
                </ul>
                </details>
            </div>
        </div>

        <?php if ($markets): ?>
            <div class="footer-market">
                <span>We are also available on</span>
                <?php foreach ($markets as $name => $url): ?>
                    <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener nofollow"><?= esc($name) ?> <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <div class="footer-bottom">
            <div>© <?= date('Y') ?> <?= esc(setting('copyright_name', 'Natural Life Speciality')) ?> | All rights reserved.
                <?php if (setting('fssai_number')): ?> · FSSAI Lic. No. <?= esc(setting('fssai_number')) ?><?php endif ?>
                <?php if (setting('gst_number')): ?> · GSTIN <?= esc(setting('gst_number')) ?><?php endif ?>
            </div>
            <ul>
                <?php foreach (site_menu('legal') as $item): ?><li><a href="<?= menu_href($item['url']) ?>"><?= esc($item['label']) ?></a></li><?php endforeach ?>
            </ul>
        </div>
    </div>
</footer>
