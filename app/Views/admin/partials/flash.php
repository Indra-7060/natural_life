<?php
$icons = ['success' => 'check-circle', 'error' => 'exclamation-circle', 'info' => 'info-circle'];
foreach ($icons as $type => $icon):
    if ($msg = session($type)): ?>
    <div class="a-flash a-flash--<?= $type ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>"><i class="bi bi-<?= $icon ?>" aria-hidden="true"></i><div><?= esc($msg) ?></div></div>
<?php endif; endforeach;
if ($errors = session('errors')): ?>
    <div class="a-flash a-flash--error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>
        <div><strong>Please correct the following:</strong><ul><?php foreach ((array) $errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?></ul></div></div>
<?php endif ?>
