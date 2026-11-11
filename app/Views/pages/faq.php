<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="page-hero page-hero--center">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <p class="eyebrow eyebrow--plain mt-4"><?= esc($page['eyebrow']) ?></p>
        <h1 class="mt-2" style="max-width:18ch"><?= esc($page['intro'] ?: $page['title']) ?></h1>
        <div class="faq-search mx-auto mt-4">
            <i class="bi bi-search" aria-hidden="true"></i>
            <label class="visually-hidden" for="faqSearch">Search questions</label>
            <input class="form-control" type="search" id="faqSearch" placeholder="Search questions…" data-faq-search>
        </div>
    </div>
</section>
<section class="section">
    <div class="shell shell--narrow">
        <div class="faq" id="faqList">
            <?php foreach ($faqs as $i => $f): ?>
                <div class="faq__item" data-faq-item>
                    <h2 class="m-0"><button class="faq__q" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="faq<?= $i ?>"><span><?= esc($f['question']) ?></span><i class="bi bi-plus-lg" aria-hidden="true"></i></button></h2>
                    <div id="faq<?= $i ?>" class="collapse <?= $i === 0 ? 'show' : '' ?>"><div class="faq__a prose"><?= rich($f['answer']) ?></div></div>
                </div>
            <?php endforeach ?>
        </div>
        <p class="muted mt-4" data-faq-empty hidden>No questions match your search.</p>
    </div>
</section>
<section class="section--tight bg-cream">
    <div class="shell cta-band">
        <div><span class="eyebrow">Still have a question?</span><h2 class="mt-3 mb-0">We are happy to help</h2></div>
        <div class="d-flex flex-wrap gap-2"><a class="btn btn-forest" href="<?= site_url('contact-us') ?>">Contact us <i class="bi bi-arrow-right"></i></a>
            <?php if (setting('contact_whatsapp')): ?><a class="btn btn-line" href="https://wa.me/<?= esc(setting('contact_whatsapp'), 'attr') ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp</a><?php endif ?></div>
    </div>
</section>
<?= $this->endSection() ?>
