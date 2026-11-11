<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Payment</th><th>Total</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong><?= esc($o['order_number']) ?></strong></td>
                <td><?= date('j M Y', strtotime((string) $o['created_at'])) ?></td>
                <td><?= status_badge($o['status']) ?></td>
                <td><?= status_badge($o['payment_status']) ?></td>
                <td><?= money($o['grand_total']) ?></td>
                <td class="text-end"><a class="link-arrow" href="<?= site_url('my-account/orders/' . $o['order_number']) ?>">View</a></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
