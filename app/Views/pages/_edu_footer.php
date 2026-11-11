<?php
/** Shared close for education pages: the client's own "not a medication" statement + onward links. */
$links = ['glycemic-index' => 'Glycemic Index', 'health-benefits-of-low-gi' => 'Health Benefits of Low GI', 'low-gi-diet-vs-other-diet' => 'Low GI Diet Vs Other Diet'];
unset($links[$page['slug']]);
?>
<section class="section--tight bg-cream">
    <div class="shell">
        <p class="muted small mb-4" style="max-width:80ch"><i class="bi bi-info-circle me-1"></i> This page is general information published by Sugarlif. Sugarlif is NOT a medication. We recommend all to continue to work with their doctors and/or dieticians in monitoring their blood sugar levels.</p>
        <div class="cta-band">
            <div><span class="eyebrow">Keep reading</span>
                <div class="d-flex flex-wrap gap-4 mt-3"><?php foreach ($links as $slug => $label): ?><a class="link-arrow h4 mb-0" href="<?= site_url($slug) ?>"><?= esc($label) ?> <i class="bi bi-arrow-right"></i></a><?php endforeach ?></div>
            </div>
            <a class="btn btn-forest" href="<?= site_url('shop') ?>">Shop Low GI foods <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>
