<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$err    = static fn (string $f) => isset($errors[$f]) ? '<div class="field-error">' . esc($errors[$f]) . '</div>' : '';
$tel    = preg_replace('/[^0-9+]/', '', (string) setting('contact_phone'));
?>
<section class="bg-forest on-dark">
    <div class="shell section--tight">
        <?= $this->include('partials/breadcrumbs') ?>
        <div class="contact-grid mt-4">
            <div>
                <p class="eyebrow eyebrow--light"><?= esc($page['eyebrow']) ?></p>
                <h1 class="mt-3" style="color:var(--ivory)"><?= esc($page['intro'] ?: $page['title']) ?></h1>
                <?php if ($page['body']): ?><div class="lede mt-3" style="color:#cfd8c8"><?= rich($page['body']) ?></div><?php endif ?>
                <ul class="contact-list mt-4">
                    <li><span>Email Address</span><a href="mailto:<?= esc(setting('contact_email_primary'), 'attr') ?>"><?= esc(setting('contact_email_primary')) ?></a>
                        <?php if (setting('contact_email_sales')): ?><a href="mailto:<?= esc(setting('contact_email_sales'), 'attr') ?>"><?= esc(setting('contact_email_sales')) ?></a><?php endif ?></li>
                    <li><span>Phone Number</span><a href="tel:<?= esc($tel, 'attr') ?>"><?= esc(setting('contact_phone')) ?></a>
                        <?php if (setting('contact_whatsapp')): ?><a class="small mt-2" style="font-family:var(--font-text);font-size:.95rem" href="https://wa.me/<?= esc(setting('contact_whatsapp'), 'attr') ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Chat on WhatsApp</a><?php endif ?></li>
                    <li><span>Office Address</span><p><?= esc(setting('address_line1')) ?>,<br><?= esc(setting('address_city')) ?> <?= esc(setting('address_postcode')) ?>. <?= esc(setting('address_state')) ?></p></li>
                </ul>
            </div>
            <div class="form-card" id="enquiry" style="color:var(--ink)">
                <h2 class="h3" style="color:var(--forest-deep)">Send us a message</h2>
                <?= $this->include('partials/flash') ?>
                <form action="<?= site_url('contact-us') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label" for="first_name">First name *</label><input class="form-control" id="first_name" name="first_name" value="<?= esc(old('first_name', ''), 'attr') ?>" required autocomplete="given-name"><?= $err('first_name') ?></div>
                        <div class="col-sm-6"><label class="form-label" for="last_name">Last name</label><input class="form-control" id="last_name" name="last_name" value="<?= esc(old('last_name', ''), 'attr') ?>" autocomplete="family-name"></div>
                        <div class="col-sm-6"><label class="form-label" for="cemail">Email *</label><input class="form-control" type="email" id="cemail" name="email" value="<?= esc(old('email', ''), 'attr') ?>" required autocomplete="email"><?= $err('email') ?></div>
                        <div class="col-sm-6"><label class="form-label" for="cphone">Phone no.</label><input class="form-control" type="tel" id="cphone" name="phone" value="<?= esc(old('phone', ''), 'attr') ?>" autocomplete="tel"><?= $err('phone') ?></div>
                        <div class="col-12"><label class="form-label" for="subject">Subject</label><input class="form-control" id="subject" name="subject" value="<?= esc(old('subject', ''), 'attr') ?>" maxlength="190"></div>
                        <div class="col-12"><label class="form-label" for="message">Message *</label><textarea class="form-control" id="message" name="message" rows="5" required maxlength="3000"><?= esc(old('message', '')) ?></textarea><?= $err('message') ?></div>
                    </div>
                    <button class="btn btn-forest btn-lg mt-4" type="submit">Submit now <i class="bi bi-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
