<?php
/**
 * Interactive Glycemic Index scale + blood-glucose response curve.
 * Original vector artwork for the redesign. Bands follow the client's published
 * classification: Low GI below 55, Moderate 56-69, High above 70 (pure glucose = 100).
 * The curves are an illustrative sketch of the behaviour the client's copy describes
 * (high GI: sharp spike then rapid decline; low GI: slow rise and gradual decline).
 */
$bands = $bands ?? [['Low GI', 'Below 55'], ['Moderate GI', 'Between 56-69'], ['High GI', 'Above 70']];
$keys  = ['low', 'mid', 'high'];
$notes = [
    'low'  => 'Foods with low GI release glucose into the blood stream at a slow, sustainable rate.',
    'mid'  => 'Moderate GI foods sit between the two responses.',
    'high' => 'The consumption of high-GI foods causes a sharp spike in blood glucose levels.',
];
?>
<div class="figure-card" data-gi>
    <svg class="gi-curve" viewBox="0 0 520 250" role="img" aria-label="Illustration: blood glucose rises sharply and falls quickly after high GI foods, and rises slowly and declines gradually after low GI foods">
        <line x1="44" y1="210" x2="500" y2="210" stroke="#ddd6c6"/>
        <line x1="44" y1="20" x2="44" y2="210" stroke="#ddd6c6"/>
        <text x="44" y="236">0</text><text x="262" y="236" text-anchor="middle">1 hour</text><text x="500" y="236" text-anchor="end">2 hours</text>
        <text x="14" y="120" transform="rotate(-90 14 120)" text-anchor="middle">Blood glucose</text>
        <path class="area" data-gi-curve="high" fill="#b4633a" d="M44 210 C90 210 110 34 170 34 C230 34 250 200 330 214 C400 224 460 212 500 210 Z"/>
        <path class="area" data-gi-curve="mid" fill="#c89b3c" d="M44 210 C100 210 130 84 200 84 C270 84 300 180 380 200 C430 210 470 210 500 210 Z"/>
        <path class="area is-active" data-gi-curve="low" fill="#5c8a5b" d="M44 210 C110 210 150 132 240 132 C330 132 380 176 500 196 L500 210 Z"/>
        <path class="curve" data-gi-curve="high" stroke="#b4633a" d="M44 210 C90 210 110 34 170 34 C230 34 250 200 330 214 C400 224 460 212 500 210"/>
        <path class="curve" data-gi-curve="mid" stroke="#c89b3c" d="M44 210 C100 210 130 84 200 84 C270 84 300 180 380 200 C430 210 470 210 500 210"/>
        <path class="curve is-active" data-gi-curve="low" stroke="#5c8a5b" d="M44 210 C110 210 150 132 240 132 C330 132 380 176 500 196"/>
    </svg>
    <div class="gi-scale mt-3" role="group" aria-label="Glycemic Index bands">
        <?php foreach ($bands as $i => [$title, $range]): $k = $keys[$i] ?? 'low'; ?>
            <button class="gi-band gi-band--<?= $k ?> <?= $i === 0 ? 'is-active' : '' ?>" type="button" data-gi-band="<?= $k ?>" data-gi-text="<?= esc($notes[$k], 'attr') ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                <b><?= esc($title) ?></b><span><?= esc($range) ?></span>
            </button>
        <?php endforeach ?>
    </div>
    <p class="gi-note mb-0" data-gi-note><?= esc($notes['low']) ?></p>
    <figcaption>Values are relative to pure glucose, which is 100. Curves are illustrative.</figcaption>
</div>
