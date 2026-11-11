<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php use App\Models\OrderModel; $o = $order; ?>
<div class="a-head">
    <div><h1>Order <?= esc($o['order_number']) ?></h1><div class="mt-2 d-flex flex-wrap gap-2 align-items-center"><?= status_badge($o['status']) ?> <?= status_badge($o['payment_status']) ?> <span class="text-secondary">Placed <?= date('j M Y, g:i a', strtotime((string) $o['created_at'])) ?></span></div></div>
    <a class="btn btn-light" href="<?= site_url('order/' . $o['order_number']) ?>?key=<?= esc($o['access_token'], 'attr') ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Customer view</a>
</div>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="panel">
            <div class="panel__head"><h2>Items</h2></div>
            <div class="table-scroll"><table class="a-table a-table--fit">
                <thead><tr><th></th><th>Product</th><th>Unit price</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
                <tbody><?php foreach ($o['items'] as $i): ?><tr>
                    <td style="width:60px"><img class="thumb" src="<?= media_url($i['image'], 'sm') ?>" alt=""></td>
                    <td><strong><?= esc($i['product_name']) ?></strong><?= $i['variation_label'] ? '<br><small class="text-secondary">' . esc($i['variation_label']) . '</small>' : '' ?><?= $i['sku'] ? '<br><small class="text-secondary">SKU ' . esc($i['sku']) . '</small>' : '' ?></td>
                    <td><?= money($i['unit_price']) ?></td><td><?= (int) $i['quantity'] ?></td><td class="text-end"><?= money($i['line_total']) ?></td>
                </tr><?php endforeach ?></tbody>
                <tfoot>
                    <tr><td colspan="4" class="text-end text-secondary">Subtotal</td><td class="text-end"><?= money($o['subtotal']) ?></td></tr>
                    <?php if ((float) $o['discount_total'] > 0): ?><tr><td colspan="4" class="text-end text-secondary">Discount<?= $o['coupon_code'] ? ' (' . esc($o['coupon_code']) . ')' : '' ?></td><td class="text-end">−<?= money($o['discount_total']) ?></td></tr><?php endif ?>
                    <tr><td colspan="4" class="text-end text-secondary">Shipping<?= $o['shipping_method'] ? ' — ' . esc($o['shipping_method']) : '' ?></td><td class="text-end"><?= (float) $o['shipping_total'] > 0 ? money($o['shipping_total']) : 'Free' ?></td></tr>
                    <tr><td colspan="4" class="text-end fw-semibold">Total</td><td class="text-end fw-semibold fs-5"><?= money($o['grand_total']) ?></td></tr>
                </tfoot>
            </table></div>
        </div>

        <div class="panel">
            <div class="panel__head"><h2>Payment</h2><?= status_badge($o['payment_status']) ?></div>
            <div class="panel__body">
                <?php if ($o['payments']): ?>
                    <div class="table-scroll mb-3"><table class="a-table">
                        <thead><tr><th>Date</th><th>Gateway</th><th>Result</th><th>Reference</th><th>Mode</th><th class="text-end">Amount</th><th>Verified</th></tr></thead>
                        <tbody><?php foreach ($o['payments'] as $pay): ?><tr>
                            <td><?= date('j M Y, g:i a', strtotime((string) $pay['created_at'])) ?></td><td><?= esc(ucfirst($pay['gateway'])) ?></td><td><?= esc($pay['status']) ?><?= $pay['message'] ? '<br><small class="text-secondary">' . esc($pay['message']) . '</small>' : '' ?></td>
                            <td><?= esc((string) $pay['gateway_reference']) ?: '—' ?></td><td><?= esc((string) $pay['payment_mode']) ?: '—' ?></td><td class="text-end"><?= money($pay['amount']) ?></td><td><?= $pay['verified'] ? '<i class="bi bi-check-circle-fill text-success"></i> Yes' : '<i class="bi bi-x-circle text-danger"></i> No' ?></td>
                        </tr><?php endforeach ?></tbody>
                    </table></div>
                <?php else: ?><p class="text-secondary">No payment attempts recorded. Method: <?= esc(ucfirst($o['payment_method'])) ?>.</p><?php endif ?>
                <?php if ($o['payment_status'] !== 'paid'): ?>
                    <details>
                        <summary class="fw-semibold" style="cursor:pointer">Record a payment received outside the gateway</summary>
                        <form class="row g-3 mt-1" method="post" action="<?= site_url('admin/orders/' . $o['id'] . '/payment') ?>" data-confirm="Mark this order as paid (<?= money($o['grand_total']) ?>)? Only do this after confirming the money has been received." data-confirm-label="Mark as paid"><?= csrf_field() ?>
                            <div class="col-md-5"><label class="form-label" for="reference">Payment reference *</label><input class="form-control" id="reference" name="reference" placeholder="Bank / UPI reference" required></div>
                            <div class="col-md-4"><label class="form-label" for="mode">Mode</label><input class="form-control" id="mode" name="mode" placeholder="e.g. Bank transfer"></div>
                            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="confirm" name="confirm" value="1" required><label class="form-check-label" for="confirm">I have confirmed that <?= money($o['grand_total']) ?> has been received.</label></div></div>
                            <div class="col-12"><button class="btn btn-primary" type="submit">Record payment</button></div>
                        </form>
                    </details>
                <?php endif ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel__head"><h2>Order history</h2></div>
            <div class="panel__body"><ul class="timeline"><?php foreach ($o['history'] as $h): ?><li><strong><?= esc(ucfirst($h['status'])) ?></strong> — <?= esc((string) $h['note']) ?><small><?= date('j M Y, g:i a', strtotime((string) $h['created_at'])) ?> · <?= esc((string) $h['changed_by']) ?></small></li><?php endforeach ?></ul></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="panel">
            <div class="panel__head"><h2>Update status</h2></div>
            <form class="panel__body" method="post" action="<?= site_url('admin/orders/' . $o['id'] . '/status') ?>"><?= csrf_field() ?>
                <label class="form-label" for="status">Order status</label>
                <select class="form-select mb-3" id="status" name="status"><?php foreach (OrderModel::STATUSES as $k => $l): ?><option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select>
                <label class="form-label" for="note">Note (shown in the order history)</label>
                <textarea class="form-control mb-3" id="note" name="note" rows="2" maxlength="250"></textarea>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="notify" name="notify" value="1" checked><label class="form-check-label" for="notify">Email the customer about this update</label></div>
                <button class="btn btn-primary w-100" type="submit">Update status</button>
            </form>
        </div>
        <div class="panel">
            <div class="panel__head"><h2>Shipping</h2></div>
            <form class="panel__body" method="post" action="<?= site_url('admin/orders/' . $o['id'] . '/shipping') ?>"><?= csrf_field() ?>
                <address class="mb-3"><strong><?= esc($o['shipping_name']) ?></strong><br><?= esc($o['shipping_line1']) ?><?= $o['shipping_line2'] ? ', ' . esc($o['shipping_line2']) : '' ?><br><?= esc($o['shipping_city']) ?>, <?= esc($o['shipping_state']) ?> <?= esc($o['shipping_postcode']) ?><br><?= esc($o['shipping_phone']) ?></address>
                <label class="form-label" for="courier_name">Courier</label><input class="form-control mb-2" id="courier_name" name="courier_name" value="<?= esc((string) $o['courier_name'], 'attr') ?>">
                <label class="form-label" for="tracking_number">Tracking number</label><input class="form-control mb-2" id="tracking_number" name="tracking_number" value="<?= esc((string) $o['tracking_number'], 'attr') ?>">
                <label class="form-label" for="tracking_url">Tracking link</label><input class="form-control mb-3" type="url" id="tracking_url" name="tracking_url" value="<?= esc((string) $o['tracking_url'], 'attr') ?>" placeholder="https://">
                <button class="btn btn-light w-100" type="submit">Save shipping details</button>
            </form>
        </div>
        <div class="panel">
            <div class="panel__head"><h2>Customer</h2></div>
            <div class="panel__body">
                <dl class="dl" style="grid-template-columns:90px 1fr">
                    <dt>Email</dt><dd><a href="mailto:<?= esc($o['email'], 'attr') ?>"><?= esc($o['email']) ?></a></dd>
                    <dt>Phone</dt><dd><?= esc($o['phone']) ?></dd>
                    <dt>Account</dt><dd><?= $customer ? '<a href="' . site_url('admin/customers/' . $customer['id']) . '">' . esc(trim($customer['first_name'] . ' ' . $customer['last_name'])) . '</a>' : 'Guest checkout' ?></dd>
                    <dt>Orders</dt><dd><?= (int) $orderCount ?> with this email</dd>
                    <dt>Billing</dt><dd><?= esc($o['billing_name']) ?>, <?= esc($o['billing_line1']) ?>, <?= esc($o['billing_city']) ?> <?= esc($o['billing_postcode']) ?></dd>
                </dl>
                <?php if ($o['customer_note']): ?><div class="a-flash a-flash--info mt-3 mb-0"><i class="bi bi-chat-left-text"></i><div><strong>Customer note</strong><br><?= esc($o['customer_note']) ?></div></div><?php endif ?>
            </div>
        </div>
        <div class="panel">
            <div class="panel__head"><h2>Internal note</h2></div>
            <form class="panel__body" method="post" action="<?= site_url('admin/orders/' . $o['id'] . '/note') ?>"><?= csrf_field() ?>
                <label class="visually-hidden" for="admin_note">Internal note</label>
                <textarea class="form-control mb-3" id="admin_note" name="admin_note" rows="3" placeholder="Only visible to administrators"><?= esc((string) $o['admin_note']) ?></textarea>
                <button class="btn btn-light w-100" type="submit">Save note</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
