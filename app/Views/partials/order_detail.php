<?php /** Order items, totals, addresses and tracking. Expects $order (with items, history). */ ?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <?= status_badge($order['status']) ?> <?= status_badge($order['payment_status']) ?>
    <span class="muted small">Placed <?= date('j M Y, g:i a', strtotime((string) $order['created_at'])) ?></span>
</div>
<?php if ($order['tracking_number']): ?>
    <div class="flash flash--info"><i class="bi bi-truck"></i><div><strong>Shipped<?= $order['courier_name'] ? ' with ' . esc($order['courier_name']) : '' ?></strong><br>Tracking number: <?= esc($order['tracking_number']) ?>
        <?php if ($order['tracking_url']): ?> · <a href="<?= esc($order['tracking_url'], 'attr') ?>" target="_blank" rel="noopener">Track shipment</a><?php endif ?></div></div>
<?php endif ?>
<?php foreach ($order['items'] as $item): ?>
    <div class="mini-line">
        <img src="<?= media_url($item['image'], 'sm') ?>" alt="" width="56" height="56">
        <div><?= esc($item['product_name']) ?><small><?= $item['variation_label'] ? esc($item['variation_label']) . ' · ' : '' ?><?= money($item['unit_price']) ?> × <?= (int) $item['quantity'] ?></small></div>
        <strong><?= money($item['line_total']) ?></strong>
    </div>
<?php endforeach ?>
<div class="row g-4 mt-2">
    <div class="col-md-6 order-md-2">
        <?= view('partials/totals', ['totals' => ['subtotal' => $order['subtotal'], 'discount' => $order['discount_total'], 'coupon_code' => $order['coupon_code'], 'shipping' => $order['shipping_total'], 'total' => $order['grand_total']]]) ?>
    </div>
    <div class="col-md-6">
        <h3 class="h5" style="font-family:var(--font-text);font-weight:600;font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)">Deliver to</h3>
        <address class="mb-0"><?= esc($order['shipping_name']) ?><br><?= esc($order['shipping_line1']) ?><?= $order['shipping_line2'] ? ', ' . esc($order['shipping_line2']) : '' ?><br><?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_state']) ?> <?= esc($order['shipping_postcode']) ?><br><?= esc($order['shipping_phone']) ?></address>
    </div>
</div>
<?php if (! empty($order['history'])): ?>
    <h3 class="h4 mt-5 mb-3">Order updates</h3>
    <ul class="timeline">
        <?php foreach ($order['history'] as $h): ?><li><strong><?= esc(ucfirst($h['status'])) ?></strong> — <?= esc($h['note']) ?><small><?= date('j M Y, g:i a', strtotime((string) $h['created_at'])) ?></small></li><?php endforeach ?>
    </ul>
<?php endif ?>
