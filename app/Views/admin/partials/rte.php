<?php /** Rich-text editor bound to a hidden textarea. Expects $name, $value, optional $small. */ ?>
<div class="rte <?= ! empty($small) ? 'rte--sm' : '' ?>" data-rte>
    <div class="rte__bar" role="toolbar" aria-label="Formatting">
        <button type="button" data-cmd="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
        <button type="button" data-cmd="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
        <button type="button" data-block="h2" title="Heading">H2</button>
        <button type="button" data-block="h3" title="Sub-heading">H3</button>
        <button type="button" data-block="p" title="Paragraph">¶</button>
        <button type="button" data-cmd="insertUnorderedList" title="Bulleted list"><i class="bi bi-list-ul"></i></button>
        <button type="button" data-cmd="insertOrderedList" title="Numbered list"><i class="bi bi-list-ol"></i></button>
        <button type="button" data-block="blockquote" title="Quote"><i class="bi bi-quote"></i></button>
        <button type="button" data-link title="Link"><i class="bi bi-link-45deg"></i></button>
        <button type="button" data-cmd="unlink" title="Remove link"><i class="bi bi-link"></i></button>
        <button type="button" data-cmd="removeFormat" title="Clear formatting"><i class="bi bi-eraser"></i></button>
    </div>
    <div class="rte__area" contenteditable="true" role="textbox" aria-multiline="true" aria-label="<?= esc($label ?? 'Text', 'attr') ?>"><?= rich((string) ($value ?? '')) ?></div>
    <textarea name="<?= $name ?>" hidden><?= esc((string) ($value ?? '')) ?></textarea>
</div>
