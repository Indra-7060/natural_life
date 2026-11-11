<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="a-head"><h1>Dashboard</h1><span class="text-secondary"><?= date('l, j F Y') ?></span></div>

<div class="kpis">
    <a class="kpi kpi--accent" href="<?= site_url('admin/orders?payment=paid') ?>"><i class="bi bi-currency-rupee"></i><div><b><?= money($stats['revenue']) ?></b><span>Total sales (paid) · <?= money($stats['revenueMonth']) ?> this month</span></div></a>
    <a class="kpi" href="<?= site_url('admin/orders') ?>"><i class="bi bi-receipt"></i><div><b><?= $stats['orders'] ?></b><span>Total orders</span></div></a>
    <a class="kpi" href="<?= site_url('admin/orders?status=pending') ?>"><i class="bi bi-hourglass-split"></i><div><b><?= $stats['pending'] ?></b><span>Pending / processing orders</span></div></a>
    <a class="kpi" href="<?= site_url('admin/orders?status=completed') ?>"><i class="bi bi-check2-circle"></i><div><b><?= $stats['completed'] ?></b><span>Completed orders</span></div></a>
    <a class="kpi" href="<?= site_url('admin/products') ?>"><i class="bi bi-box-seam"></i><div><b><?= $stats['products'] ?></b><span>Products · <?= $stats['outOfStock'] ?> out of stock</span></div></a>
    <a class="kpi" href="<?= site_url('admin/categories') ?>"><i class="bi bi-tags"></i><div><b><?= $stats['categories'] ?></b><span>Categories</span></div></a>
    <a class="kpi" href="<?= site_url('admin/customers') ?>"><i class="bi bi-people"></i><div><b><?= $stats['customers'] ?></b><span>Customers</span></div></a>
    <a class="kpi" href="<?= site_url('admin/enquiries') ?>"><i class="bi bi-envelope"></i><div><b><?= $stats['enquiries'] ?></b><span>Enquiries · <?= $stats['subscribers'] ?> subscribers</span></div></a>
</div>

<div class="dash-grid">
    <div>
        <div class="panel">
            <div class="panel__head"><h2>Sales — last 30 days</h2><span class="text-secondary small">Paid orders only</span></div>
            <div class="panel__body"><div style="height:280px"><canvas id="salesChart" aria-label="Sales in the last 30 days" role="img"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel__head"><h2>Recent orders</h2><a class="btn btn-light btn-sm" href="<?= site_url('admin/orders') ?>">All orders</a></div>
            <?php if ($orders): ?>
                <div class="table-scroll"><table class="a-table">
                    <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Status</th><th>Payment</th><th class="text-end">Total</th></tr></thead>
                    <tbody><?php foreach ($orders as $o): ?><tr>
                        <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/orders/' . $o['id']) ?>"><?= esc($o['order_number']) ?></a></td>
                        <td><?= esc($o['shipping_name']) ?></td><td><?= date('j M, g:i a', strtotime((string) $o['created_at'])) ?></td>
                        <td><?= status_badge($o['status']) ?></td><td><?= status_badge($o['payment_status']) ?></td><td class="text-end"><?= money($o['grand_total']) ?></td>
                    </tr><?php endforeach ?></tbody>
                </table></div>
            <?php else: ?><div class="empty"><i class="bi bi-receipt"></i>No orders yet.</div><?php endif ?>
        </div>
    </div>
    <div>
        <div class="panel">
            <div class="panel__head"><h2>Orders by status</h2></div>
            <div class="panel__body"><div style="height:220px"><canvas id="statusChart" aria-label="Orders by status" role="img"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel__head"><h2>Top products</h2><span class="text-secondary small">By paid sales</span></div>
            <?php if ($top): ?><table class="a-table"><tbody><?php foreach ($top as $t): ?><tr><td><?= esc($t['product_name']) ?><br><small class="text-secondary"><?= (int) $t['qty'] ?> sold</small></td><td class="text-end"><?= money($t['total']) ?></td></tr><?php endforeach ?></tbody></table>
            <?php else: ?><div class="empty">No paid sales yet.</div><?php endif ?>
        </div>
        <div class="panel">
            <div class="panel__head"><h2>Recent enquiries</h2><a class="btn btn-light btn-sm" href="<?= site_url('admin/enquiries') ?>">All</a></div>
            <?php if ($enquiries): ?><table class="a-table"><tbody><?php foreach ($enquiries as $e): ?><tr>
                <td><a class="fw-semibold text-decoration-none" href="<?= site_url('admin/enquiries/' . $e['id']) ?>"><?= esc(trim($e['first_name'] . ' ' . $e['last_name'])) ?></a><br><small class="text-secondary"><?= esc(plain($e['subject'] ?: $e['message'], 50)) ?></small></td>
                <td class="text-end"><?= status_badge($e['status']) ?></td></tr><?php endforeach ?></tbody></table>
            <?php else: ?><div class="empty">No enquiries yet.</div><?php endif ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/chartjs/chart.umd.min.js') ?>"></script>
<script>
(function () {
    const chart = <?= json_encode($chart) ?>;
    const status = <?= json_encode($status ?: new stdClass()) ?>;
    Chart.defaults.font.family = '"DM Sans", system-ui, sans-serif'; Chart.defaults.color = '#667065';
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: { labels: chart.labels, datasets: [{ label: 'Sales (₹)', data: chart.sales, backgroundColor: '#1f3a2b', borderRadius: 3, maxBarThickness: 18 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => '₹' + c.parsed.y.toLocaleString('en-IN') + ' · ' + chart.orders[c.dataIndex] + ' order(s)' } } },
            scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }, y: { beginAtZero: true, suggestedMax: 1000, grid: { color: '#eeece4' }, ticks: { callback: (v) => '₹' + v.toLocaleString('en-IN') } } } }
    });
    const keys = Object.keys(status);
    const colors = { pending: '#c89b3c', processing: '#4f8fa8', shipped: '#a9b8a0', completed: '#1f3a2b', cancelled: '#b9b6aa', refunded: '#b4633a' };
    if (keys.length) {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: { labels: keys.map((k) => k.charAt(0).toUpperCase() + k.slice(1)), datasets: [{ data: keys.map((k) => +status[k]), backgroundColor: keys.map((k) => colors[k] || '#999'), borderWidth: 2, borderColor: '#fff' }] },
            options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'right', labels: { boxWidth: 10, boxHeight: 10 } } } }
        });
    } else {
        document.getElementById('statusChart').parentElement.innerHTML = '<div class="empty">No orders yet.</div>';
    }
})();
</script>
<?= $this->endSection() ?>
