<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="shell shell--narrow text-center">
        <?php if ($gateway): ?>
            <div class="spinner-border text-secondary mb-4" role="status"><span class="visually-hidden">Redirecting…</span></div>
            <h1 class="h2">Taking you to CCAvenue…</h1>
            <p class="lede mx-auto">Order <strong><?= esc($order['order_number']) ?></strong> · <?= money($order['grand_total']) ?>. Please do not close this window.</p>
            <form id="gatewayForm" method="post" action="<?= esc($gateway['action'], 'attr') ?>">
                <input type="hidden" name="encRequest" value="<?= esc($gateway['encRequest'], 'attr') ?>">
                <input type="hidden" name="access_code" value="<?= esc($gateway['access_code'], 'attr') ?>">
                <noscript><button class="btn btn-forest btn-lg mt-3" type="submit">Continue to payment</button></noscript>
            </form>
        <?php else: ?>
            <span class="eyebrow eyebrow--plain">Order <?= esc($order['order_number']) ?></span>
            <h1 class="h2 mt-3">Your order has been placed</h1>
            <p class="lede mx-auto">Online payment is not switched on for this installation yet, so your order is saved as <strong>pending payment</strong>. Nothing has been charged. Our team will contact you at <?= esc($order['email']) ?> to complete the payment of <?= money($order['grand_total']) ?>.</p>
            <a class="btn btn-forest mt-3" href="<?= site_url('order/' . $order['order_number']) ?>?key=<?= esc($order['access_token'], 'attr') ?>">View order details</a>
        <?php endif ?>
    </div>
</section>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($gateway): ?><script>window.addEventListener('load', function () { document.getElementById('gatewayForm').submit(); });</script><?php endif ?>
<?= $this->endSection() ?>
