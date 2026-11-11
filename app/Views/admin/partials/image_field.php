<?php
/** Image input: pick from the media library or upload a new file. Expects $name, $value, $label, optional $help. */
$value = (string) ($value ?? '');
?>
<label class="form-label" for="f_<?= $name ?>"><?= esc($label) ?></label>
<div class="img-field" data-image-field>
    <div class="img-field__preview" data-image-preview><?php if ($value): ?><img src="<?= media_url($value, 'sm') ?>" alt=""><?php else: ?><i class="bi bi-image"></i><?php endif ?></div>
    <div class="flex-grow-1">
        <input type="hidden" name="<?= $name ?>" id="f_<?= $name ?>" value="<?= esc($value, 'attr') ?>" data-image-input>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-light btn-sm" type="button" data-media-pick><i class="bi bi-images"></i> Choose from library</button>
            <label class="btn btn-light btn-sm mb-0"><i class="bi bi-upload"></i> Upload<input type="file" name="<?= $name ?>_upload" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" hidden data-image-upload></label>
            <button class="btn btn-light btn-sm" type="button" data-image-clear <?= $value ? '' : 'hidden' ?>><i class="bi bi-x-lg"></i> Remove</button>
        </div>
        <div class="form-text" data-image-name><?= $value ? esc($value) : 'JPG, PNG, WebP, GIF or SVG · up to 8 MB' ?></div>
        <?php if (! empty($help)): ?><div class="form-text"><?= esc($help) ?></div><?php endif ?>
    </div>
</div>
