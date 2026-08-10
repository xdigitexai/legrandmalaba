<?php include 'header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    :root {
        --primary-color: #3b82f6; --success-color: #22c55e; --warning-color: #f59e0b;
        --danger-color: #ef4444; --info-color: #0ea5e9; --purple-color: #9333ea;
        --gray-color: #6b7280; --light-gray-color: #f0f2f5; --white-color: #ffffff;
        --dark-text-color: #1e293b; --light-text-color: #64748b;
    }
    body {
        background-color: var(--light-gray-color);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .container-fluid {
        padding: 15px;
    }
    .dashboard-card {
        background: var(--white-color);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 25px;
    }
    .dashboard-card .card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 16px;
        font-weight: 600;
        color: var(--dark-text-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .dashboard-card .card-body {
        padding: 20px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }
    .stat-card {
        background: var(--white-color);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--dark-text-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #e2e8f0;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border-color: var(--primary-color);
    }
    .stat-details { display: flex; flex-direction: column; gap: 5px; }
    .stat-label { font-size: 14px; color: var(--light-text-color); font-weight: 500; }
    .stat-value { font-size: 24px; font-weight: 700; color: var(--dark-text-color); line-height: 1.1; }
    .stat-value small { font-size: 16px; font-weight: 500; }
    .stat-icon {
        width: 48px; height: 48px; border-radius: 12px; display: flex;
        align-items: center; justify-content: center; font-size: 20px;
    }
    .stat-icon.bg-primary { background: rgba(59, 130, 246, 0.1); color: var(--primary-color); }
    .stat-icon.bg-success { background: rgba(34, 197, 94, 0.1); color: var(--success-color); }
    .stat-icon.bg-warning { background: rgba(245, 159, 11, 0.1); color: var(--warning-color); }
    .stat-icon.bg-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
    .stat-icon.bg-info { background: rgba(14, 165, 233, 0.1); color: var(--info-color); }
    .stat-icon.bg-purple { background: rgba(147, 51, 234, 0.1); color: var(--purple-color); }

    .table-responsive { max-height: 400px; overflow-y: auto; }
    .table { width: 100%; margin-bottom: 0; }
    .table thead th { border-bottom-width: 1px; font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--light-text-color); }
    .table td, .table th { vertical-align: middle; white-space: nowrap; }
    .table .badge { font-size: 11px; padding: 5px 10px; border-radius: 12px; font-weight: 500;}
    .badge-completed { background-color: rgba(34, 197, 94, 0.1); color: var(--success-color); }
    .badge-pending { background-color: rgba(245, 159, 11, 0.1); color: var(--warning-color); }
    .badge-fail, .badge-cancelled, .badge-canceled { background-color: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
    .badge-inprogress, .badge-processing { background-color: rgba(14, 165, 233, 0.1); color: var(--info-color); }
    .badge-partial { background-color: rgba(147, 51, 234, 0.1); color: var(--purple-color); }
</style>

<div class="container-fluid">

    <div class="dashboard-card">
        <div class="card-header"><i class="fas fa-analytics"></i>Financial Overview</div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Total Profit</span><span class="stat-value"><small>₱</small><?= number_format($total_profit ?? 0, 2) ?></span></div><div class="stat-icon bg-success"><i class="fas fa-chart-line"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Profit Today</span><span class="stat-value"><small>₱</small><?= number_format($profit_today ?? 0, 2) ?></span></div><div class="stat-icon bg-success"><i class="fas fa-calendar-day"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Profit This Month</span><span class="stat-value"><small>₱</small><?= number_format($profit_month ?? 0, 2) ?></span></div><div class="stat-icon bg-success"><i class="fas fa-calendar-alt"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Total Funds Added</span><span class="stat-value"><small>₱</small><?= number_format($total_payments_value ?? 0, 2) ?></span></div><div class="stat-icon bg-primary"><i class="fas fa-hand-holding-usd"></i></div></div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header"><i class="fas fa-users"></i>User Statistics</div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Total Users</span><span class="stat-value"><?= number_format($total_users ?? 0) ?></span></div><div class="stat-icon bg-primary"><i class="fas fa-users"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Total Subscribers in Telegram</span><span class="stat-value"><?= number_format($total_telegram_users ?? 0) ?></span></div><div class="stat-icon bg-primary"><i class="fas fa-user-friends"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">New Users Today</span><span class="stat-value"><?= number_format($new_users_today ?? 0) ?></span></div><div class="stat-icon bg-primary"><i class="fas fa-user-plus"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">New Users This Week</span><span class="stat-value"><?= number_format($new_users_week ?? 0) ?></span></div><div class="stat-icon bg-primary"><i class="fas fa-user-friends"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Banned Users</span><span class="stat-value"><?= number_format($banned_users ?? 0) ?></span></div><div class="stat-icon bg-danger"><i class="fas fa-user-slash"></i></div></div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header"><i class="fas fa-shopping-cart"></i>Order Breakdown</div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Total Orders</span><span class="stat-value"><?= number_format($total_orders ?? 0) ?></span></div><div class="stat-icon bg-info"><i class="fas fa-globe-americas"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Completed</span><span class="stat-value"><?= number_format($completed_orders ?? 0) ?></span></div><div class="stat-icon bg-success"><i class="fas fa-check"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Pending</span><span class="stat-value"><?= number_format($pending_orders ?? 0) ?></span></div><div class="stat-icon bg-warning"><i class="fas fa-clock"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">In Progress</span><span class="stat-value"><?= number_format($inprogress_orders ?? 0) ?></span></div><div class="stat-icon bg-info"><i class="fas fa-spinner fa-spin"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Processing</span><span class="stat-value"><?= number_format($processing_orders ?? 0) ?></span></div><div class="stat-icon bg-info"><i class="fas fa-sync-alt fa-spin"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Partial</span><span class="stat-value"><?= number_format($partial_orders ?? 0) ?></span></div><div class="stat-icon bg-purple"><i class="fas fa-star-half-alt"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Failed</span><span class="stat-value"><?= number_format($failed_orders ?? 0) ?></span></div><div class="stat-icon bg-danger"><i class="fas fa-times-circle"></i></div></div>
                <div class="stat-card"><div class="stat-details"><span class="stat-label">Cancelled</span><span class="stat-value"><?= number_format($cancelled_orders ?? 0) ?></span></div><div class="stat-icon bg-danger"><i class="fas fa-ban"></i></div></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="dashboard-card">
                <div class="card-header"><i class="fas fa-chart-pie"></i>Order Status Overview</div>
                <div class="card-body"><canvas id="orderStatusChart" style="max-height: 350px;"></canvas></div>
            </div>
            <div class="dashboard-card">
                <div class="card-header"><i class="fas fa-chart-bar"></i>Platform Totals</div>
                <div class="card-body"><canvas id="keyTotalsChart" style="max-height: 350px;"></canvas></div>
            </div>
        </div>
        <div class="col-lg-5">
    <div class="dashboard-card">
        <div class="card-header"><i class="fas fa-hourglass-half"></i>Last Pending Order</div>
        <div class="card-body table-responsive p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Service</th>
                        <th>Total</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
    <?php if (!empty($last_pending_order) && isset($last_pending_order[0])): ?>
        <?php $order = $last_pending_order[0]; ?>
        <tr>
            <td><?= htmlspecialchars($order['username']) ?></td>
            <td title="<?= htmlspecialchars($order['service_name']) ?>">
                <?= htmlspecialchars($order['service_name']) ?>
            </td>
            <td>₱<?= number_format($order['order_charge'], 2) ?></td>
            <td>
                <?php
                    if (!empty($order['order_create'])) {
                        echo date("M d, Y H:i", strtotime($order['order_create']));
                    } else {
                        echo '-';
                    }
                ?>
            </td>
        </tr>
    <?php else: ?>
        <tr><td colspan="4" class="text-center">No pending orders found.</td></tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header"><i class="fas fa-check-circle"></i>Last Completed Order</div>
        <div class="card-body table-responsive p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Service</th>
                        <th>Total</th>
                        <th>Completion Date</th>
                    </tr>
                </thead>
                <tbody>
    <?php if (!empty($last_completed_order) && isset($last_completed_order[0])): ?>
        <?php $order = $last_completed_order[0]; ?>
        <tr>
            <td><?= htmlspecialchars($order['username']) ?></td>
            <td title="<?= htmlspecialchars($order['service_name']) ?>">
                <?= htmlspecialchars($order['service_name']) ?>
            </td>
            <td>₱<?= number_format($order['order_charge'], 2) ?></td>
            <td>
                <?php
                    if (!empty($order['order_create'])) {
                        echo date("M d, Y H:i", strtotime($order['order_create']));
                    } else {
                        echo '-';
                    }
                ?>
            </td>
        </tr>
    <?php else: ?>
        <tr><td colspan="4" class="text-center">No completed orders found.</td></tr>
    <?php endif; ?>
</tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartDefaultOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 20, font: { size: 12 } } } } };
    
    // Order Status Doughnut Chart
    const orderStatusCtx = document.getElementById('orderStatusChart');
    if (orderStatusCtx) {
        new Chart(orderStatusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Completed', 'Failed', 'Cancelled', 'Partial', 'Processing'],
                datasets: [{
                    label: 'Order Status',
                    data: [
                        <?= (int)($pending_orders ?? 0) ?>, <?= (int)($inprogress_orders ?? 0) ?>, <?= (int)($completed_orders ?? 0) ?>,
                        <?= (int)($failed_orders ?? 0) ?>, <?= (int)($cancelled_orders ?? 0) ?>, <?= (int)($partial_orders ?? 0) ?>, <?= (int)($processing_orders ?? 0) ?>
                    ],
                    backgroundColor: ['#f59e0b', '#0ea5e9', '#22c55e', '#ef4444', '#6b7280', '#9333ea', '#3b82f6'],
                    borderColor: 'var(--white-color)', borderWidth: 2
                }]
            },
            options: chartDefaultOptions
        });
    }

    // Key Totals Bar Chart
    const keyTotalsCtx = document.getElementById('keyTotalsChart');
    if (keyTotalsCtx) {
        new Chart(keyTotalsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Total Users', 'Total Orders', 'Active Services', 'Providers'],
                datasets: [{
                    label: 'Total Count',
                    data: [
                        <?= (int)($total_users ?? 0) ?>, 
                        <?= (int)($total_orders ?? 0) ?>, 
                        <?= (int)($active_services ?? 0) ?>, 
                        <?= (int)($total_providers ?? 0) ?>
                    ],
                    backgroundColor: ['#3b82f6', '#0ea5e9', '#22c55e', '#9333ea'],
                    borderRadius: 5
                }]
            },
            options: { ...chartDefaultOptions, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
});
</script>

<?php include 'footer.php'; ?>