<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:18px 0;">
    <?php foreach ($order['items'] as $item): ?>
        <tr><td style="padding:10px 0;border-bottom:1px solid #ebe5d7;"><?= esc($item['product_name']) ?><?= $item['variation_label'] ? ' — ' . esc($item['variation_label']) : '' ?> × <?= (int) $item['quantity'] ?></td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #ebe5d7;"><?= money($item['line_total']) ?></td></tr>
    <?php endforeach ?>
    <tr><td style="padding:8px 0;color:#5e6a60;">Subtotal</td><td align="right"><?= money($order['subtotal']) ?></td></tr>
    <?php if ((float) $order['discount_total'] > 0): ?><tr><td style="padding:8px 0;color:#5e6a60;">Discount</td><td align="right">−<?= money($order['discount_total']) ?></td></tr><?php endif ?>
    <tr><td style="padding:8px 0;color:#5e6a60;">Shipping</td><td align="right"><?= (float) $order['shipping_total'] > 0 ? money($order['shipping_total']) : 'Free' ?></td></tr>
    <tr><td style="padding:12px 0;border-top:1px solid #1f3a2b;font-weight:bold;">Total</td><td align="right" style="padding:12px 0;border-top:1px solid #1f3a2b;font-weight:bold;"><?= money($order['grand_total']) ?></td></tr>
</table>
<p style="margin:0 0 6px;font-weight:bold;">Deliver to</p>
<p style="margin:0;color:#414a43;"><?= esc($order['shipping_name']) ?><br><?= esc($order['shipping_line1']) ?><?= $order['shipping_line2'] ? ', ' . esc($order['shipping_line2']) : '' ?><br><?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_state']) ?> <?= esc($order['shipping_postcode']) ?><br><?= esc($order['shipping_phone']) ?></p>
