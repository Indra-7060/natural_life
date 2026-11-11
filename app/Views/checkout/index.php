<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$err    = static fn (string $f) => isset($errors[$f]) ? '<div class="field-error">' . esc($errors[$f]) . '</div>' : '';
$inv    = static fn (string $f) => isset($errors[$f]) ? 'is-invalid' : '';
$def    = $addresses[0] ?? null;
$val    = static fn (string $f, ?string $fallback = '') => esc(old($f, $fallback ?? ''), 'attr');
$states = indian_states();
?>
<section class="page-hero">
    <div class="shell">
        <?= $this->include('partials/breadcrumbs') ?>
        <h1>Checkout</h1>
    </div>
</section>
<section class="section--tight">
    <div class="shell">
        <?= $this->include('partials/flash') ?>
        <form class="checkout-layout" action="<?= site_url('checkout') ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <div>
                <div class="step">
                    <h2><small>01</small> Contact</h2>
                    <?php if (! $customer): ?><p class="muted">Have an account? <a href="<?= site_url('my-account/login') ?>">Sign in</a> for a faster checkout. You can also continue as a guest.</p><?php endif ?>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="email">Email *</label><input class="form-control <?= $inv('email') ?>" type="email" id="email" name="email" value="<?= $val('email', $customer['email'] ?? '') ?>" required autocomplete="email"><?= $err('email') ?></div>
                        <div class="col-md-6"><label class="form-label" for="phone">Mobile number *</label><input class="form-control <?= $inv('phone') ?>" type="tel" id="phone" name="phone" value="<?= $val('phone', $def['phone'] ?? ($customer['phone'] ?? '')) ?>" required autocomplete="tel"><?= $err('phone') ?></div>
                    </div>
                </div>

                <div class="step">
                    <h2><small>02</small> Shipping address</h2>
                    <?php if ($addresses): ?>
                        <div class="row g-3 mb-3">
                            <?php foreach ($addresses as $i => $a): ?>
                                <div class="col-md-6">
                                    <label class="address-card">
                                        <input class="form-check-input me-2" type="radio" name="saved_address" <?= $i === 0 ? 'checked' : '' ?> data-address-pick="<?= esc(json_encode(['shipping_name' => $a['full_name'], 'phone' => $a['phone'], 'shipping_line1' => $a['line1'], 'shipping_line2' => $a['line2'], 'shipping_city' => $a['city'], 'shipping_state' => $a['state'], 'shipping_postcode' => $a['postcode']]), 'attr') ?>">
                                        <strong><?= esc($a['label']) ?></strong><br><span class="muted small"><?= esc($a['full_name']) ?>, <?= esc($a['line1']) ?>, <?= esc($a['city']) ?> <?= esc($a['postcode']) ?></span>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label" for="shipping_name">Full name *</label><input class="form-control <?= $inv('shipping_name') ?>" id="shipping_name" name="shipping_name" value="<?= $val('shipping_name', $def['full_name'] ?? ($customer ? trim($customer['first_name'] . ' ' . $customer['last_name']) : '')) ?>" required autocomplete="name"><?= $err('shipping_name') ?></div>
                        <div class="col-12"><label class="form-label" for="shipping_line1">Address *</label><input class="form-control <?= $inv('shipping_line1') ?>" id="shipping_line1" name="shipping_line1" value="<?= $val('shipping_line1', $def['line1'] ?? '') ?>" placeholder="House / flat number, street" required autocomplete="address-line1"><?= $err('shipping_line1') ?></div>
                        <div class="col-12"><label class="form-label" for="shipping_line2">Area / landmark</label><input class="form-control" id="shipping_line2" name="shipping_line2" value="<?= $val('shipping_line2', $def['line2'] ?? '') ?>" autocomplete="address-line2"></div>
                        <div class="col-md-4"><label class="form-label" for="shipping_city">City *</label><input class="form-control <?= $inv('shipping_city') ?>" id="shipping_city" name="shipping_city" value="<?= $val('shipping_city', $def['city'] ?? '') ?>" required autocomplete="address-level2"><?= $err('shipping_city') ?></div>
                        <div class="col-md-4"><label class="form-label" for="shipping_state">State *</label>
                            <select class="form-select <?= $inv('shipping_state') ?>" id="shipping_state" name="shipping_state" required autocomplete="address-level1">
                                <option value="">Select state</option>
                                <?php $cur = old('shipping_state', $def['state'] ?? ''); foreach ($states as $st): ?><option <?= $cur === $st ? 'selected' : '' ?>><?= esc($st) ?></option><?php endforeach ?>
                            </select><?= $err('shipping_state') ?></div>
                        <div class="col-md-4"><label class="form-label" for="shipping_postcode">PIN code *</label><input class="form-control <?= $inv('shipping_postcode') ?>" id="shipping_postcode" name="shipping_postcode" value="<?= $val('shipping_postcode', $def['postcode'] ?? '') ?>" inputmode="numeric" maxlength="6" required autocomplete="postal-code"><?= $err('shipping_postcode') ?></div>
                    </div>
                    <?php if ($customer): ?><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="save_address" value="1" id="saveAddress" <?= $addresses ? '' : 'checked' ?>><label class="form-check-label" for="saveAddress">Save this address to my account</label></div><?php endif ?>
                    <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="billing_same" value="1" id="billingSame" <?= old('billing_name') ? '' : 'checked' ?>><label class="form-check-label" for="billingSame">Billing address is the same as shipping</label></div>

                    <div id="billingFields" class="mt-4" hidden>
                        <h3 class="h4 mb-3">Billing address</h3>
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label" for="billing_name">Full name *</label><input class="form-control <?= $inv('billing_name') ?>" id="billing_name" name="billing_name" value="<?= $val('billing_name') ?>"><?= $err('billing_name') ?></div>
                            <div class="col-12"><label class="form-label" for="billing_line1">Address *</label><input class="form-control <?= $inv('billing_line1') ?>" id="billing_line1" name="billing_line1" value="<?= $val('billing_line1') ?>"><?= $err('billing_line1') ?></div>
                            <div class="col-12"><label class="form-label" for="billing_line2">Area / landmark</label><input class="form-control" id="billing_line2" name="billing_line2" value="<?= $val('billing_line2') ?>"></div>
                            <div class="col-md-4"><label class="form-label" for="billing_city">City *</label><input class="form-control <?= $inv('billing_city') ?>" id="billing_city" name="billing_city" value="<?= $val('billing_city') ?>"><?= $err('billing_city') ?></div>
                            <div class="col-md-4"><label class="form-label" for="billing_state">State *</label>
                                <select class="form-select <?= $inv('billing_state') ?>" id="billing_state" name="billing_state"><option value="">Select state</option>
                                    <?php foreach ($states as $st): ?><option <?= old('billing_state') === $st ? 'selected' : '' ?>><?= esc($st) ?></option><?php endforeach ?>
                                </select><?= $err('billing_state') ?></div>
                            <div class="col-md-4"><label class="form-label" for="billing_postcode">PIN code *</label><input class="form-control <?= $inv('billing_postcode') ?>" id="billing_postcode" name="billing_postcode" value="<?= $val('billing_postcode') ?>" inputmode="numeric" maxlength="6"><?= $err('billing_postcode') ?></div>
                        </div>
                    </div>
                </div>

                <div class="step">
                    <h2><small>03</small> Payment</h2>
                    <div class="pay-option">
                        <i class="bi bi-credit-card-2-front" aria-hidden="true"></i>
                        <div><strong>Pay online with CCAvenue</strong><br><span class="muted small">After placing your order you will be taken to CCAvenue’s secure page to complete the payment.</span></div>
                    </div>
                    <?php if (! $gatewayReady): ?>
                        <div class="flash flash--info mt-3"><i class="bi bi-info-circle"></i><div>Online payment is not switched on for this installation yet. Your order will be saved as <strong>pending payment</strong> and our team will contact you to complete it.</div></div>
                    <?php endif ?>
                    <div class="mt-3"><label class="form-label" for="customer_note">Order note (optional)</label><textarea class="form-control" id="customer_note" name="customer_note" rows="2" maxlength="500"><?= esc(old('customer_note', '')) ?></textarea></div>
                </div>
            </div>

            <aside class="summary" aria-label="Order summary">
                <h2>Your order</h2>
                <?php foreach ($lines as $l): ?>
                    <div class="mini-line">
                        <img src="<?= media_url($l['image'], 'sm') ?>" alt="" width="56" height="56">
                        <div><?= esc($l['name']) ?><small><?= $l['variation_label'] ? esc($l['variation_label']) . ' · ' : '' ?>Qty <?= $l['quantity'] ?></small></div>
                        <strong><?= money($l['line_total']) ?></strong>
                    </div>
                <?php endforeach ?>
                <div class="mt-3"><?= view('partials/totals', ['totals' => $totals]) ?></div>
                <div class="form-check mt-4">
                    <input class="form-check-input <?= $inv('accept_terms') ?>" type="checkbox" name="accept_terms" value="1" id="acceptTerms" required>
                    <label class="form-check-label small" for="acceptTerms">I have read and agree to the <a href="<?= site_url('terms-and-conditions') ?>" target="_blank">Terms</a>, <a href="<?= site_url('privacy-policy') ?>" target="_blank">Privacy Policy</a> and <a href="<?= site_url('refund_returns') ?>" target="_blank">Refund and Returns Policy</a>.</label>
                    <?= $err('accept_terms') ?>
                </div>
                <div class="d-grid mt-3"><button class="btn btn-forest btn-lg" type="submit">Place order · <?= money($totals['total']) ?></button></div>
                <a class="link-arrow mt-3" href="<?= site_url('cart') ?>"><i class="bi bi-arrow-left"></i> Edit cart</a>
            </aside>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
