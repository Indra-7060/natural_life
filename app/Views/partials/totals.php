<dl class="totals">
    <div><dt>Subtotal</dt><dd><?= money($totals['subtotal']) ?></dd></div>
    <?php if ($totals['discount'] > 0): ?><div><dt>Discount<?= ! empty($totals['coupon_code']) ? ' (' . esc($totals['coupon_code']) . ')' : '' ?></dt><dd>−<?= money($totals['discount']) ?></dd></div><?php endif ?>
    <div><dt>Shipping</dt><dd><?= $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' ?></dd></div>
    <div class="totals__grand"><dt>Total <small class="muted fw-normal">incl. taxes</small></dt><dd><?= money($totals['total']) ?></dd></div>
</dl>
