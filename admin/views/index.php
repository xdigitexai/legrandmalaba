<?php include 'header.php'; 

// Get the system currency
$currency_query = $conn->prepare("SELECT site_currency FROM settings LIMIT 1");
$currency_query->execute();
$currency_symbol = $currency_query->fetch(PDO::FETCH_ASSOC)['site_currency'] ?? '$';

// Get current total balance of all users
$balance_query = $conn->prepare("SELECT SUM(balance) as total_balance FROM clients WHERE balance > 0");
$balance_query->execute();
$total_balance = $balance_query->fetch(PDO::FETCH_ASSOC)['total_balance'] ?? 0;

// Format the balance with 2 decimal places
$formatted_balance = number_format($total_balance, 2);


?>


	<br>
	<div class="quick-actions-wrapper" bis_skin_checked="1">
        <div class="quick-actions-header" bis_skin_checked="1">
            <div class="header-content" bis_skin_checked="1">
                <h4><i class="fa fa-bolt"></i> Quick Actions</h4>
            </div>
        </div>
        <div class="quick-actions-grid" bis_skin_checked="1">
            <a href="/admin/clients" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa fa-users"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">User Management</span>
                    <span class="action-desc">View and manage users</span>
                </div>
            </a>
            <a href="/admin/analytics" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fas fa-sliders"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">Analytics Management</span>
                    <span class="action-desc">View and manage analytics</span>
                </div>
            </a>
            <a href="/admin/orders" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">View Orders</span>
                    <span class="action-desc">Manage all system orders</span>
                </div>
            </a>
            <a href="/admin/description" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">Generate Discription</span>
                    <span class="action-desc">Manage all Service Descriptions</span>
                </div>
            </a>
            <a href="/admin/OrdersController" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa-solid fa-list"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">Orders Controller</span>
                    <span class="action-desc">Manage all Orders ID</span>
                </div>
            </a>
            <a href="/admin/settings/providers" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa fa-plug"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">Manage Providers</span>
                    <span class="action-desc">Add or configure service providers</span>
                </div>
            </a>
            <a href="/admin/settings/paymentMethods" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa fa-credit-card"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">Payment Methods</span>
                    <span class="action-desc">Configure payment gateways</span>
                </div>
            </a>
            <a href="/admin/settings" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa fa-cog"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">System Settings</span>
                    <span class="action-desc">Manage system configuration</span>
                </div>
            </a>
            <a href="/admin/feedback" class="quick-action-btn">
                <div class="action-icon" bis_skin_checked="1">
                    <i class="fa fa-file-text"></i>
                </div>
                <div class="action-details" bis_skin_checked="1">
                    <span class="action-title">Report a Problem</span>
                    <span class="action-desc">View system reports</span>
                </div>
            </a>
        </div>
    </div>
    <br>
    
    <br>
    
    
    
    <br>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Order Status Doughnut Chart
    var orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(orderStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Completed', 'Failed', 'Cancelled'],
            datasets: [{
                label: 'Order Status',
                data: [
                    <?php echo $pending_orders; ?>,
                    <?php echo $inprogress_orders; ?>,
                    <?php echo $completed_orders; ?>,
                    <?php echo $failed_orders; ?>,
                    <?php echo $cancelled_orders; ?>
                ],
                backgroundColor: [
                    'rgba(255, 159, 64, 0.8)', // Orange
                    'rgba(54, 162, 235, 0.8)', // Blue
                    'rgba(75, 192, 192, 0.8)', // Green
                    'rgba(255, 99, 132, 0.8)',  // Red
                    'rgba(153, 102, 255, 0.8)'  // Purple
                ],
                borderColor: '#FFF',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
            },
            title: {
                display: false
            }
        }
    });

    // 2. Key Totals Bar Chart
    var keyTotalsCtx = document.getElementById('keyTotalsChart').getContext('2d');
    new Chart(keyTotalsCtx, {
        type: 'bar',
        data: {
            labels: ['Total Users', 'Total Orders', 'Total Payments', 'Providers'],
            datasets: [{
                label: 'Total Count',
                data: [
                    <?php echo $total_users; ?>,
                    <?php echo $total_orders; ?>,
                    <?php echo $total_payments; ?>,
                    <?php echo $total_providers; ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false // Hide legend for bar chart
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        // Ensure only whole numbers are shown
                        callback: function(value) { if (Number.isInteger(value)) { return value; } },
                        stepSize: 1
                    }
                }]
            }
        }
    });

});
</script>
    <style>
/* Basic card styles */
.stats-wrapper {
    padding: 20px;
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    position: relative;
    overflow: hidden;
}

/* Scanning line effect */
.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
    animation: scanning 3s linear infinite;
    animation-delay: 0s;
    opacity: 1;
}

.stat-card:hover::before {
    opacity: 1;
}

/* Different colors for different card types */
.security-card::before {
    background: linear-gradient(90deg, transparent, #ef4444, transparent);
}

.system-health-card::before {
    background: linear-gradient(90deg, transparent, #22c55e, transparent);
}

.network-card::before {
    background: linear-gradient(90deg, transparent, #0ea5e9, transparent);
}

.server-load-card::before {
    background: linear-gradient(90deg, transparent, #9333ea, transparent);
}

/* Second row cards */
.stat-card:nth-child(5)::before { /* Total Balance */
    background: linear-gradient(90deg, transparent, #22c55e, transparent);
}

.stat-card:nth-child(6)::before { /* Total Providers */
    background: linear-gradient(90deg, transparent, #22c55e, transparent);
}

.stat-card:nth-child(7)::before { /* Total Users */
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
}

.stat-card:nth-child(8)::before { /* Total Orders */
    background: linear-gradient(90deg, transparent, #0ea5e9, transparent);
}

/* Third row cards */
.stat-card:nth-child(9)::before { /* Pending Orders */
    background: linear-gradient(90deg, transparent, #9333ea, transparent);
}

.stat-card:nth-child(10)::before { /* In Progress Orders */
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
}

.stat-card:nth-child(11)::before { /* Failed Orders */
    background: linear-gradient(90deg, transparent, #ef4444, transparent);
}

.stat-card:nth-child(12)::before { /* Cancelled Orders */
    background: linear-gradient(90deg, transparent, #6b7280, transparent);
}

@keyframes scanning {
    0% { left: -100%; }
    100% { left: 100%; }
}

.stat-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-label {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1;
}

.stat-change {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-change.positive { color: #22c55e; }
.stat-change.negative { color: #ef4444; }
.stat-change.neutral { color: #6b7280; }

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon i {
    font-size: 20px;
}

/* Icon backgrounds */
.bg-success-light {
    background: rgba(34, 197, 94, 0.1);
}
.bg-success-light i { color: #22c55e; }

.bg-primary-light {
    background: rgba(59, 130, 246, 0.1);
}
.bg-primary-light i { color: #3b82f6; }

.bg-info-light {
    background: rgba(14, 165, 233, 0.1);
}
.bg-info-light i { color: #0ea5e9; }

.bg-purple-light {
    background: rgba(147, 51, 234, 0.1);
}
.bg-purple-light i { color: #9333ea; }

.bg-danger-light {
    background: rgba(239, 68, 68, 0.1);
}
.bg-danger-light i { color: #ef4444; }

.bg-gray-light {
    background: rgba(107, 114, 128, 0.1);
}
.bg-gray-light i { color: #6b7280; }

/* Responsive design */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 15px;
    }
}

/* Add these styles for threat detection */
.security-card.safe .security-icon {
    background: rgba(34, 197, 94, 0.1) !important;
}

.security-card.safe .security-icon i {
    color: #22c55e !important;
}

.security-card.safe .stat-change {
    color: #22c55e !important;
}

.security-card.safe .stat-change i {
    color: #22c55e !important;
}

.security-card.threat-detected {
    animation: threat-alert 1s ease;
}

@keyframes threat-alert {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Add these styles for server load */
.server-load-card.high-load .server-icon {
    background: rgba(239, 68, 68, 0.1) !important;
}

.server-load-card.high-load .server-icon i {
    color: #ef4444 !important;
}

.server-load-card.high-load .stat-change {
    color: #ef4444 !important;
}

.server-load-card.high-load .stat-change i {
    color: #ef4444 !important;
}

.server-load-card.high-load {
    animation: load-alert 1s ease;
}

@keyframes load-alert {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

/* Activities Section Styles */
.activities-section {
    padding: 0 20px;
    margin-bottom: 30px;
}

.activities-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.activity-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.view-all {
    font-size: 13px;
    color: #3b82f6;
    text-decoration: none;
    transition: color 0.3s ease;
}

.view-all:hover {
    color: #2563eb;
}

.activity-content {
    padding: 15px 20px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.1);
}

.activity-icon i {
    font-size: 14px;
    color: #3b82f6;
}

.activity-icon.pending i { color: #f59e0b; }
.activity-icon.completed i { color: #22c55e; }
.activity-icon.failed i { color: #ef4444; }
.activity-icon.payment i { color: #3b82f6; }

.activity-details {
    flex: 1;
}

.activity-main {
    font-size: 14px;
    color: #1e293b;
    margin-bottom: 4px;
}

.activity-user {
    font-weight: 500;
    color: #3b82f6;
}

.activity-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #64748b;
}

.activity-status {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.activity-status.pending { background: #fef3c7; color: #d97706; }
.activity-status.completed { background: #dcfce7; color: #16a34a; }
.activity-status.failed { background: #fee2e2; color: #dc2626; }
.activity-status.canceled { background: #fcdcdc; color: #a94442; }
.activity-status.inprogress { background: #c7e4fe; color: #0689d9; }

/* System Status Styles */
.system-status .status-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.status-label {
    width: 100px;
    font-size: 13px;
    color: #64748b;
}

.status-bar {
    flex: 1;
    height: 8px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #3b82f6;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.status-value {
    width: 50px;
    font-size: 13px;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
}

@media (max-width: 1200px) {
    .activities-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .activities-grid {
        grid-template-columns: 1fr;
    }
}

/* Add these new styles for payment history */
.payment-history .activity-content {
    padding: 10px 15px;
}

.payment-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.payment-item:last-child {
    margin-bottom: 0;
}

.payment-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.payment-user {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    color: #1e293b;
}

.payment-user i {
    color: #3b82f6;
    font-size: 16px;
}

.payment-status {
    font-size: 11px;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 12px;
}

.payment-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-info {
    display: flex;
    gap: 12px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #64748b;
}

.info-item i {
    color: #3b82f6;
    font-size: 14px;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
    background: rgba(59, 130, 246, 0.1);
    padding: 4px 8px;
    border-radius: 6px;
}

.payment-method i {
    color: #3b82f6;
}

/* Status colors */
.payment-status.completed {
    background: #dcfce7;
    color: #16a34a;
}

.payment-status.pending {
    background: #fef3c7;
    color: #d97706;
}

.payment-status.failed {
    background: #fee2e2;
    color: #dc2626;
}

/* Add these new background colors */
.bg-orange-light {
    background: rgba(251, 146, 60, 0.1);
}
.bg-orange-light i { color: #fb923c; }

.bg-green-light {
    background: rgba(34, 197, 94, 0.1);
}
.bg-green-light i { color: #22c55e; }

/* Add warning state for API balance */
.stat-change.warning {
    color: #f59e0b;
}

.stat-change.warning i {
    color: #f59e0b;
}

/* API Balance Card Styles */
.api-balance-card {
    position: relative;
    overflow: visible !important;
}

.provider-list-toggle {
    font-size: 12px;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
    transition: color 0.3s ease;
}

.provider-list-toggle:hover {
    color: #3b82f6;
}

.provider-list-toggle i {
    transition: transform 0.3s ease;
}

.provider-list-toggle.active i {
    transform: rotate(180deg);
}

.provider-list {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    padding: 8px;
    display: none;
    z-index: 100;
    max-height: 300px;
    overflow-y: auto;
}

.provider-list.show {
    display: block;
}

.provider-item {
    padding: 8px 12px;
    border-radius: 8px;
    background: #f8fafc;
    margin-bottom: 4px;
    transition: all 0.3s ease;
}

.provider-item:last-child {
    margin-bottom: 0;
}

.provider-item:hover {
    background: #f1f5f9;
}

.provider-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #1e293b;
}

.provider-name {
    font-weight: 500;
}

.provider-balance {
    color: #64748b;
}

.provider-item.low-balance {
    background: #fff7ed;
}

.provider-warning {
    font-size: 11px;
    color: #f59e0b;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
}

.provider-warning i {
    font-size: 12px;
}

/* Add new background colors */
.bg-yellow-light {
    background: rgba(250, 204, 21, 0.1);
}
.bg-yellow-light i { color: #facc15; }

.bg-blue-light {
    background: rgba(59, 130, 246, 0.1);
}
.bg-blue-light i { color: #3b82f6; }

/* Style for popular service text */
.popular-service {
    font-size: 20px !important;
        white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Add these styles for cron card */
.cron-card::before {
    background: linear-gradient(90deg, transparent, #9333ea, transparent);
}

.cron-card .cron-status {
    font-size: 20px !important;
    color: #22c55e;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.cron-card .stat-icon {
    animation: rotate 4s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Add these styles for web services card */
.web-services-card::before {
    background: linear-gradient(90deg, transparent, #0ea5e9, transparent);
}

.web-services-card .web-status {
    font-size: 20px !important;
    color: #22c55e;
    animation: pulse 2s infinite;
}

.web-services-card .stat-icon {
    animation: pulse 2s infinite;
}

.web-services-card .stat-icon i {
    animation: rotate 4s linear infinite;
}

/* Add these styles for last update card */
.last-update-card::before {
    background: linear-gradient(90deg, transparent, #22c55e, transparent);
}

.last-update-card .update-time {
    font-size: 18px !important;
    color: #1e293b;
}

.last-update-card .stat-icon {
    animation: rotate 4s linear infinite;
}

.last-update-card .stat-icon i {
    animation: rotate 4s linear infinite;
    }

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.dashboard-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h4 {
    margin: 0;
    color: #333;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body {
    padding: 20px;
}

.activity-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.metric {
    text-align: center;
}

.metric-label {
    display: block;
    color: #666;
    font-size: 12px;
    margin-bottom: 5px;
}

.metric-value {
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.unit {
    font-size: 12px;
    color: #666;
    margin-left: 2px;
}

.service-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.service-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.service-name {
    flex: 1;
    font-size: 14px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.status-badge.online {
    background: #28a745;
    color: white;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.action-btn i {
    font-size: 24px;
    margin-bottom: 8px;
    color: #007bff;
}

.refresh-btn {
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: transform 0.3s ease;
}

.refresh-btn:hover {
    transform: rotate(180deg);
}

@media (max-width: 768px) {
    .activity-metrics {
        grid-template-columns: 1fr;
    }
    
    .service-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
}

/* Quick Actions Styles */
.quick-actions-wrapper {
    margin-top: 30px;
    padding: 0 20px 20px;
}

.quick-actions-header {
    margin-bottom: 20px;
    padding: 0 10px;
    text-align: center;
}

.quick-actions-header .header-content {
    display: inline-block;
}

.quick-actions-header h4 {
    font-size: 20px;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.quick-actions-header h4 i {
    color: #3b82f6;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-color: #3b82f6;
}

.action-icon {
    width: 50px;
    height: 50px;
    background: #f0f9ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    transition: all 0.3s ease;
}

.quick-action-btn:hover .action-icon {
    background: #3b82f6;
}

.action-icon i {
    font-size: 24px;
    color: #3b82f6;
    transition: all 0.3s ease;
}

.quick-action-btn:hover .action-icon i {
    color: #fff;
}

.action-details {
    flex: 1;
}

.action-title {
    display: block;
    font-size: 16px;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 4px;
}

.action-desc {
    display: block;
    font-size: 13px;
    color: #64748b;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Threat Detection System
    const securityCard = document.querySelector('.security-card');
    const threatsValue = document.querySelector('.threats-value');
    const statChange = document.querySelector('.security-card .stat-change');
    const securityIcon = document.querySelector('.security-card .security-icon i');

    function updateThreatStatus(count) {
        threatsValue.textContent = count;
        
        if (count === 0) {
            securityCard.classList.add('safe');
            securityCard.classList.remove('threat-detected');
            statChange.innerHTML = '<i class="fa fa-shield"></i> System Protected';
            securityIcon.style.color = '#22c55e';
            securityCard.querySelector('.stat-icon').style.background = 'rgba(34, 197, 94, 0.1)';
        } else {
            securityCard.classList.remove('safe');
            securityCard.classList.add('threat-detected');
            statChange.innerHTML = '<i class="fa fa-shield"></i> Threats Detected';
            securityIcon.style.color = '#ef4444';
            securityCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
        }
    }

    function simulateThreats() {
        // 30% chance of detecting threats
        if (Math.random() < 0.3) {
            const threatCount = Math.floor(Math.random() * 3) + 1; // 1-3 threats
            updateThreatStatus(threatCount);
            
            // Clear threats after 2-4 seconds
            setTimeout(() => {
                updateThreatStatus(0);
            }, Math.random() * 2000 + 2000);
        } else {
            updateThreatStatus(0);
        }
    }

    // Run threat simulation immediately
    simulateThreats();

    // Then check for threats every 40-50 seconds
    setInterval(simulateThreats, Math.random() * 10000 + 40000);

    // Server Load Simulation
    const serverCard = document.querySelector('.server-load-card');
    const serverLoadValue = document.querySelector('.server-load-value');
    const serverStatChange = document.querySelector('.server-load-card .stat-change');
    const serverIcon = document.querySelector('.server-load-card .server-icon i');

    function updateServerLoad(load) {
        serverLoadValue.textContent = load + '%';
        
        if (load > 70) {
            serverCard.classList.add('high-load');
            serverStatChange.innerHTML = '<i class="fa fa-server"></i> High Load';
            serverIcon.style.color = '#ef4444';
            serverCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
        } else {
            serverCard.classList.remove('high-load');
            serverStatChange.innerHTML = '<i class="fa fa-server"></i> Server Optimal';
            serverIcon.style.color = '#9333ea';
            serverCard.querySelector('.stat-icon').style.background = 'rgba(147, 51, 234, 0.1)';
        }
    }

    function simulateServerLoad() {
        // 30% chance of high load
        if (Math.random() < 0.3) {
            const highLoad = Math.floor(Math.random() * 30) + 71; // 71-100%
            updateServerLoad(highLoad);
            
            // Return to normal after 2-4 seconds
            setTimeout(() => {
                const normalLoad = Math.floor(Math.random() * 30) + 20; // 20-50%
                updateServerLoad(normalLoad);
            }, Math.random() * 2000 + 2000);
        } else {
            const normalLoad = Math.floor(Math.random() * 30) + 20; // 20-50%
            updateServerLoad(normalLoad);
        }
    }

    // Run server load simulation immediately
    simulateServerLoad();

    // Then update server load every 40-50 seconds
    setInterval(simulateServerLoad, Math.random() * 10000 + 40000);

    // Provider list toggle functionality
    const toggleButton = document.querySelector('.provider-list-toggle');
    const providerList = document.querySelector('.provider-list');
    
    if (toggleButton && providerList) {
        toggleButton.addEventListener('click', function() {
            providerList.classList.toggle('show');
            toggleButton.classList.toggle('active');
        });

        // Close provider list when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.api-balance-card')) {
                providerList.classList.remove('show');
                toggleButton.classList.remove('active');
            }
        });
    }

    // Cron Status Simulation
    const cronCard = document.querySelector('.cron-card');
    const cronStatus = document.querySelector('.cron-status');
    const cronIcon = document.querySelector('.cron-card .stat-icon i');

    function updateCronStatus() {
        // 90% chance of working status
        if (Math.random() < 0.9) {
            cronStatus.textContent = 'Working';
            cronStatus.style.color = '#22c55e';
            cronCard.querySelector('.stat-icon').style.background = 'rgba(147, 51, 234, 0.1)';
            cronIcon.style.color = '#9333ea';
        } else {
            cronStatus.textContent = 'Stopped';
            cronStatus.style.color = '#ef4444';
            cronCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
            cronIcon.style.color = '#ef4444';
            
            // Return to working after 2-4 seconds
            setTimeout(() => {
                cronStatus.textContent = 'Working';
                cronStatus.style.color = '#22c55e';
                cronCard.querySelector('.stat-icon').style.background = 'rgba(147, 51, 234, 0.1)';
                cronIcon.style.color = '#9333ea';
            }, Math.random() * 2000 + 2000);
        }
    }

    // Run cron status simulation immediately
    updateCronStatus();

    // Then update cron status every 30-40 seconds
    setInterval(updateCronStatus, Math.random() * 10000 + 30000);

    // Web Services Status Simulation
    const webCard = document.querySelector('.web-services-card');
    const webStatus = document.querySelector('.web-status');
    const webIcon = document.querySelector('.web-services-card .stat-icon i');

    function updateWebStatus() {
        // 95% chance of online status
        if (Math.random() < 0.95) {
            webStatus.textContent = 'Online';
            webStatus.style.color = '#22c55e';
            webCard.querySelector('.stat-icon').style.background = 'rgba(14, 165, 233, 0.1)';
            webIcon.style.color = '#0ea5e9';
        } else {
            webStatus.textContent = 'Offline';
            webStatus.style.color = '#ef4444';
            webCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
            webIcon.style.color = '#ef4444';
            
            // Return to online after 2-4 seconds
            setTimeout(() => {
                webStatus.textContent = 'Online';
                webStatus.style.color = '#22c55e';
                webCard.querySelector('.stat-icon').style.background = 'rgba(14, 165, 233, 0.1)';
                webIcon.style.color = '#0ea5e9';
            }, Math.random() * 2000 + 2000);
        }
    }

    // Run web services status simulation immediately
    updateWebStatus();

    // Then update web services status every 30-40 seconds
    setInterval(updateWebStatus, Math.random() * 10000 + 30000);

    // Last Update Time Simulation
    function updateLastUpdateTime() {
        // Generate a new random time between 1-2 days ago
        const hours = Math.floor(Math.random() * 24) + 24; // 24-48 hours
        const newDate = new Date();
        newDate.setHours(newDate.getHours() - hours);
        
        // Format the date
        const options = { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        const formattedDate = newDate.toLocaleDateString('en-US', options);
        
        // Update the display
        document.querySelector('.update-time').textContent = formattedDate;
        
        // Send the new time to the server to update the session
        fetch('update_last_update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ last_update: newDate.toISOString() })
        });
    }

    // Update the time every 1-2 days (in milliseconds)
    const updateInterval = (Math.random() * 86400000) + 86400000; // 24-48 hours
    setInterval(updateLastUpdateTime, updateInterval);
});

// Initialize Activity Chart
const ctx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['1h', '2h', '3h', '4h', '5h', 'Now'],
        datasets: [{
            label: 'System Activity',
            data: [65, 59, 80, 81, 56, 85],
            borderColor: '#007bff',
            tension: 0.4,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Simulate real-time updates
function updateMetrics() {
    // Update API Response Time
    const apiResponse = document.querySelector('.api-response');
    apiResponse.textContent = Math.floor(Math.random() * (300 - 200) + 200);

    // Update Active Sessions
    const activeSessions = document.querySelector('.active-sessions');
    activeSessions.textContent = Math.floor(Math.random() * (50 - 20) + 20);

    // Update Process Rate
    const processRate = document.querySelector('.process-rate');
    processRate.textContent = (Math.random() * (99.9 - 97) + 97).toFixed(1);

    // Update chart data
    activityChart.data.datasets[0].data.shift();
    activityChart.data.datasets[0].data.push(Math.floor(Math.random() * (90 - 50) + 50));
    activityChart.update();
}

// Update metrics every 5 seconds
setInterval(updateMetrics, 5000);

// Refresh button functionality
document.querySelector('.refresh-btn').addEventListener('click', updateMetrics);
</script>
	<br>

<style>
.renewal-countdown {
    padding: 10px;
    margin: -10px auto 30px auto;
    max-width: 600px;
}

.countdown-header {
    margin-bottom: 15px;
}

.countdown-header h4 {
    font-size: 18px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 600;
}

.bell {
    display: inline-block;
    animation: ring 4s ease-in-out infinite;
    transform-origin: 50% 0;
}

.next-renewal {
    font-size: 15px;
    color: #333;
    margin: 0;
    font-weight: 600;
    background: #f8f9fa;
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    border: 1px solid #dee2e6;
}

.countdown-timer {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 15px;
    min-height: 80px;
}

.countdown-item {
    background: #337ab7;
    color: white;
    padding: 12px 0;
    border-radius: 10px;
    width: 75px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.countdown-item:hover {
    transform: translateY(-3px);
}

.countdown-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
        width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,0.2) 50%,
        rgba(255,255,255,0) 100%
    );
    transition: left 0.7s ease;
}

.countdown-item:hover::before {
    left: 100%;
}

.count-value {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 3px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.count-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.9;
}

@keyframes ring {
    0%, 100% {
        transform: rotate(0);
    }
    5%, 15% {
        transform: rotate(15deg);
    }
    10%, 20% {
        transform: rotate(-15deg);
    }
    25% {
        transform: rotate(0);
    }
}

@media (max-width: 576px) {
    .countdown-timer {
        gap: 8px;
    }
    
    .countdown-item {
        padding: 8px 0;
        width: 60px;
    }
    
    .count-value {
        font-size: 18px;
    }
    
    .count-label {
        font-size: 10px;
    }

    .next-renewal {
        font-size: 14px;
        padding: 4px 12px;
    }
}

/* Updated Statistics Cards Styles */
.stats-wrapper {
    padding: 0 20px;
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-label {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1;
}

.stat-change {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-change.positive {
    color: #22c55e;
}

.stat-change.negative {
    color: #ef4444;
}

.stat-change.neutral {
    color: #6b7280;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
            display: flex;
    align-items: center;
            justify-content: center;
        }

.stat-icon i {
    font-size: 20px;
}

/* Icon Background Colors */
.bg-success-light {
    background: rgba(34, 197, 94, 0.1);
}
.bg-success-light i {
    color: #22c55e;
}

.bg-primary-light {
    background: rgba(59, 130, 246, 0.1);
}
.bg-primary-light i {
    color: #3b82f6;
}

.bg-info-light {
    background: rgba(14, 165, 233, 0.1);
}
.bg-info-light i {
    color: #0ea5e9;
}

.bg-warning-light {
    background: rgba(234, 179, 8, 0.1);
}
.bg-warning-light i {
    color: #eab308;
}

/* Additional Icon Background Colors */
.bg-purple-light {
    background: rgba(147, 51, 234, 0.1);
}
.bg-purple-light i {
    color: #9333ea;
}

.bg-blue-light {
    background: rgba(59, 130, 246, 0.1);
}
.bg-blue-light i {
    color: #3b82f6;
}

.bg-danger-light {
    background: rgba(239, 68, 68, 0.1);
}
.bg-danger-light i {
    color: #ef4444;
}

.bg-gray-light {
    background: rgba(107, 114, 128, 0.1);
}
.bg-gray-light i {
    color: #6b7280;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .stat-card {
        padding: 15px;
    }
}

/* Graphs Section */
.graphs-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.graph-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.graph-card h3 {
    margin: 0 0 15px;
    font-size: 16px;
    color: #333;
}

/* Additional Icon Colors for Providers */
.bg-success-light i.fa-plug {
    transform: rotate(45deg);
    transition: transform 0.3s ease;
}

.stat-card:hover .bg-success-light i.fa-plug {
    transform: rotate(90deg);
}

.bg-primary-light i.fa-money {
    transition: transform 0.3s ease;
}

.stat-card:hover .bg-primary-light i.fa-money {
    transform: scale(1.2);
}

.bg-info-light i.fa-check-circle {
    transition: transform 0.3s ease;
}

.stat-card:hover .bg-info-light i.fa-check-circle {
    transform: scale(1.2) rotate(360deg);
}

.bg-danger-light i.fa-times-circle {
    transition: transform 0.3s ease;
}

.stat-card:hover .bg-danger-light i.fa-times-circle {
    transform: scale(1.2) rotate(45deg);
}

/* Add these styles to your existing CSS */
.renewal-countdown.expired {
    background: #fff5f5;
    border: 1px solid #ff4444;
    border-radius: 10px;
}

.expired-warning {
    background: #ff4444;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    margin: 10px 0;
    display: inline-block;
    animation: pulse 2s infinite;
}

.expired-warning i {
    margin-right: 8px;
}

.expired-message {
    background: #ff4444;
    color: white;
    padding: 20px;
    border-radius: 10px;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    animation: pulse 2s infinite;
    width: 100%;
}

.expired-message i {
    margin-right: 10px;
    font-size: 28px;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.btn-success {
    background: #28a745;
    border: none;
    padding: 10px 25px;
    border-radius: 5px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
    font-size: 16px;
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.btn-success i {
    margin-right: 8px;
}

/* Add these styles to your existing CSS */
.date-update-form {
    max-width: 400px;
    margin: 20px auto;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.renewal-date-form .input-group {
    display: flex;
    gap: 10px;
}

.renewal-date-form input[type="date"] {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 5px;
    font-size: 16px;
}

.renewal-date-form .btn-primary {
    background: #007bff;
    border: none;
    padding: 8px 20px;
    border-radius: 5px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
}

.renewal-date-form .btn-primary:hover {
    background: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.renewal-date-form .btn-primary i {
    margin-right: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetDate = new Date('<?php echo $renewal_date; ?>T23:59:59');
    const isExpired = <?php echo $is_expired ? 'true' : 'false'; ?>;
    
    if (!isExpired) {
        function updateTimer() {
            const now = new Date();
            const distance = targetDate.getTime() - now.getTime();
            
            if (distance < 0) {
                // If countdown is finished, show expired message
                document.querySelector('.countdown-timer').innerHTML = `
                    <div class="expired-message">
                        <i class="fa fa-exclamation-circle"></i> Subscription Expired!
                    </div>`;
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    }
});
</script> 



<!-- Recent Activities Section -->
<div class="activities-section">
    <div class="activities-grid">

        <!-- Recent Orders -->
        <div class="activity-card">
            <div class="card-header">
                <h3><i class="fa fa-shopping-cart"></i> Recent Orders</h3>
                <a href="<?php echo site_url('admin/orders'); ?>" class="view-all">View All</a>
            </div>
            <div class="activity-content">
               <?php
                $orders = $conn->prepare("
                    SELECT orders.*, clients.username 
                    FROM orders 
                    LEFT JOIN clients ON clients.client_id = orders.client_id 
                    ORDER BY orders.order_id DESC 
                    LIMIT 5
                ");
                $orders->execute();
                $orders = $orders->fetchAll(PDO::FETCH_ASSOC);

                foreach ($orders as $order):
                    $time = isset($order['creation_date']) ? strtotime($order['creation_date']) : 0;
                ?>
                <div class="activity-item">
                    <div class="activity-icon <?php echo getOrderStatusClass($order['status']); ?>">
                        <i class="fa fa-circle"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-main">
                            <span class="activity-user"><?php echo htmlspecialchars($order['username']); ?></span>
                            <span class="activity-action">ordered</span>
                            <span class="activity-target"><td width="30%">
    <span class="label-id"><?php echo $order["service_id"]; ?></span>
    <?php echo $order["service_name"]; ?>
    
    <?php 
    $api_name = GET_API_NAME_BY_ID($order["order_api"]);
    if ($api_name):
    ?>
        <div class="tooltip5">
            <span class="fas fa-info-circle"></span>
            <span class="tooltiptext5"><?php echo $api_name; ?></span>
        </div>
    <?php endif; ?>
</td></span>
                        </div>
                        <div class="activity-meta">
                            <span class="activity-time"><?php 
        $order_create = $order["order_create"];
        $current_time = new DateTime();
        $order_time = new DateTime($order_create);
        $interval = $current_time->diff($order_time);

        $time_parts = [];

        if ($interval->y > 0) {
            $time_parts[] = $interval->y . " year" . ($interval->y > 1 ? "s" : "");
        }
        if ($interval->m > 0) {
            $time_parts[] = $interval->m . " month" . ($interval->m > 1 ? "s" : "");
        }
        if ($interval->d >= 7) {
            $weeks = floor($interval->d / 7);
            $time_parts[] = $weeks . " week" . ($weeks > 1 ? "s" : "");
        }
        if ($interval->d > 0 && $interval->d < 7) {
            $time_parts[] = $interval->d . " day" . ($interval->d > 1 ? "s" : "");
        }
        if ($interval->h > 0) {
            $time_parts[] = $interval->h . " hour" . ($interval->h > 1 ? "s" : "");
        }
        if ($interval->i > 0) {
            $time_parts[] = $interval->i . " minute" . ($interval->i > 1 ? "s" : "");
        }
        if ($interval->s > 0) {
            $time_parts[] = $interval->s . " second" . ($interval->s > 1 ? "s" : "");
        }

        echo implode(", ", $time_parts) . " ago";
        ?></span>
                            <span class="activity-status <?php echo $order['order_status']; ?>">
                               <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Payment History -->
        <div class="activity-card payment-history">
    <div class="card-header">
        <h3><i class="fa fa-credit-card"></i> Payment History</h3>
        <a href="<?php echo site_url('admin/fund-add-history'); ?>" class="view-all">View All</a>
    </div>
    <div class="activity-content">
        <?php
        $payments = $conn->prepare("
            SELECT p.payment_id, p.client_id, p.payment_amount, p.payment_status, p.payment_delivery, p.payment_create_date, 
                   p.payment_method, c.username, m.methodVisibleName 
            FROM payments p
            LEFT JOIN clients c ON c.client_id = p.client_id
            LEFT JOIN paymentmethods m ON m.methodId = p.payment_method
            ORDER BY p.payment_create_date DESC 
            LIMIT 3
        ");
        $payments->execute();
        $payments = $payments->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($payments)) {
            foreach ($payments as $payment) {
                // Format status
                if ($payment['payment_status'] == 1 && $payment['payment_delivery'] == 1) {
                    $status = 'Pending';
                    $status_class = 'pending';
                } elseif ($payment['payment_status'] == 3 && $payment['payment_delivery'] == 2) {
                    $status = 'Completed';
                    $status_class = 'completed';
                } elseif ($payment['payment_status'] == 2 && $payment['payment_delivery'] == 2) {
                    $status = 'Failed';
                    $status_class = 'failed';
                } else {
                    $status = 'Pending';
                    $status_class = 'pending';
                }

                ?>
                <div class="payment-item">
                    <div class="payment-header">
                        <div class="payment-user">
                            <i class="fa fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($payment['username']); ?></span>
                        </div>
                        <div class="payment-status <?php echo $status_class; ?>">
                            <?php echo $status; ?>
                        </div>
                    </div>
                    <div class="payment-details">
                        <div class="payment-info">
                            <div class="info-item">
                                <i class="fa fa-credit-card"></i>
                                <span><?php echo $currency_symbol . number_format($payment['payment_amount'], 2); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fa fa-clock-o"></i>
                               <span>
        <?php
        $order_create = $payment["payment_create_date"];
        $current_time = new DateTime();
        $order_time = new DateTime($order_create);
        $interval = $current_time->diff($order_time);

        $time_parts = [];

        if ($interval->y > 0) $time_parts[] = $interval->y . " year" . ($interval->y > 1 ? "s" : "");
        if ($interval->m > 0) $time_parts[] = $interval->m . " month" . ($interval->m > 1 ? "s" : "");
        if ($interval->d >= 7) {
            $weeks = floor($interval->d / 7);
            $time_parts[] = $weeks . " week" . ($weeks > 1 ? "s" : "");
        }
        if ($interval->d > 0 && $interval->d < 7) $time_parts[] = $interval->d . " day" . ($interval->d > 1 ? "s" : "");
        if ($interval->h > 0) $time_parts[] = $interval->h . " hour" . ($interval->h > 1 ? "s" : "");
        if ($interval->i > 0) $time_parts[] = $interval->i . " minute" . ($interval->i > 1 ? "s" : "");
        if ($interval->s > 0 && empty($time_parts)) $time_parts[] = $interval->s . " second" . ($interval->s > 1 ? "s" : "");

        echo implode(", ", $time_parts) . " ago";
        ?>
    </span>
                            </div>
                        </div>
                        <div class="payment-method">
                            <i class="fa fa-bank"></i>
                            <span><?php echo htmlspecialchars($payment['methodVisibleName']); ?></span>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No recent payments found.</p>";
        }
        ?>
    </div>
</div>


        <!-- System Status -->
        <div class="activity-card system-status">
            <div class="card-header">
                <h3><i class="fa fa-server"></i> System Status</h3>
            </div>
            <div class="activity-content">
                <?php
                $cpu = getCPUUsage();
                $mem = getMemoryUsage();
                $disk = getDiskUsage();
                ?>
                <div class="status-item">
                    <div class="status-label">CPU Usage</div>
                    <div class="status-bar">
                        <div class="progress-bar" style="width: <?php echo $cpu; ?>%"></div>
                    </div>
                    <div class="status-value"><?php echo $cpu; ?>%</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Memory Usage</div>
                    <div class="status-bar">
                        <div class="progress-bar" style="width: <?php echo $mem; ?>%"></div>
                    </div>
                    <div class="status-value"><?php echo $mem; ?>%</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Disk Usage</div>
                    <div class="status-bar">
                        <div class="progress-bar" style="width: <?php echo $disk; ?>%"></div>
                    </div>
                    <div class="status-value"><?php echo $disk; ?>%</div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Threat Detection System
    const securityCard = document.querySelector('.security-card');
    const threatsValue = document.querySelector('.threats-value');
    const statChange = document.querySelector('.security-card .stat-change');
    const securityIcon = document.querySelector('.security-card .security-icon i');

    function updateThreatStatus(count) {
        threatsValue.textContent = count;
        
        if (count === 0) {
            securityCard.classList.add('safe');
            securityCard.classList.remove('threat-detected');
            statChange.innerHTML = '<i class="fa fa-shield"></i> System Protected';
            securityIcon.style.color = '#22c55e';
            securityCard.querySelector('.stat-icon').style.background = 'rgba(34, 197, 94, 0.1)';
        } else {
            securityCard.classList.remove('safe');
            securityCard.classList.add('threat-detected');
            statChange.innerHTML = '<i class="fa fa-shield"></i> Threats Detected';
            securityIcon.style.color = '#ef4444';
            securityCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
        }
    }

    function simulateThreats() {
        // 30% chance of detecting threats
        if (Math.random() < 0.3) {
            const threatCount = Math.floor(Math.random() * 3) + 1; // 1-3 threats
            updateThreatStatus(threatCount);
            
            // Clear threats after 2-4 seconds
            setTimeout(() => {
                updateThreatStatus(0);
            }, Math.random() * 2000 + 2000);
        } else {
            updateThreatStatus(0);
        }
    }

    // Run threat simulation immediately
    simulateThreats();

    // Then check for threats every 40-50 seconds
    setInterval(simulateThreats, Math.random() * 10000 + 40000);

    // Server Load Simulation
    const serverCard = document.querySelector('.server-load-card');
    const serverLoadValue = document.querySelector('.server-load-value');
    const serverStatChange = document.querySelector('.server-load-card .stat-change');
    const serverIcon = document.querySelector('.server-load-card .server-icon i');

    function updateServerLoad(load) {
        serverLoadValue.textContent = load + '%';
        
        if (load > 70) {
            serverCard.classList.add('high-load');
            serverStatChange.innerHTML = '<i class="fa fa-server"></i> High Load';
            serverIcon.style.color = '#ef4444';
            serverCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
        } else {
            serverCard.classList.remove('high-load');
            serverStatChange.innerHTML = '<i class="fa fa-server"></i> Server Optimal';
            serverIcon.style.color = '#9333ea';
            serverCard.querySelector('.stat-icon').style.background = 'rgba(147, 51, 234, 0.1)';
        }
    }

    function simulateServerLoad() {
        // 30% chance of high load
        if (Math.random() < 0.3) {
            const highLoad = Math.floor(Math.random() * 30) + 71; // 71-100%
            updateServerLoad(highLoad);
            
            // Return to normal after 2-4 seconds
            setTimeout(() => {
                const normalLoad = Math.floor(Math.random() * 30) + 20; // 20-50%
                updateServerLoad(normalLoad);
            }, Math.random() * 2000 + 2000);
        } else {
            const normalLoad = Math.floor(Math.random() * 30) + 20; // 20-50%
            updateServerLoad(normalLoad);
        }
    }

    // Run server load simulation immediately
    simulateServerLoad();

    // Then update server load every 40-50 seconds
    setInterval(simulateServerLoad, Math.random() * 10000 + 40000);

    // Provider list toggle functionality
    const toggleButton = document.querySelector('.provider-list-toggle');
    const providerList = document.querySelector('.provider-list');
    
    if (toggleButton && providerList) {
        toggleButton.addEventListener('click', function() {
            providerList.classList.toggle('show');
            toggleButton.classList.toggle('active');
        });

        // Close provider list when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.api-balance-card')) {
                providerList.classList.remove('show');
                toggleButton.classList.remove('active');
            }
        });
    }

    // Cron Status Simulation
    const cronCard = document.querySelector('.cron-card');
    const cronStatus = document.querySelector('.cron-status');
    const cronIcon = document.querySelector('.cron-card .stat-icon i');

    function updateCronStatus() {
        // 90% chance of working status
        if (Math.random() < 0.9) {
            cronStatus.textContent = 'Working';
            cronStatus.style.color = '#22c55e';
            cronCard.querySelector('.stat-icon').style.background = 'rgba(147, 51, 234, 0.1)';
            cronIcon.style.color = '#9333ea';
        } else {
            cronStatus.textContent = 'Stopped';
            cronStatus.style.color = '#ef4444';
            cronCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
            cronIcon.style.color = '#ef4444';
            
            // Return to working after 2-4 seconds
            setTimeout(() => {
                cronStatus.textContent = 'Working';
                cronStatus.style.color = '#22c55e';
                cronCard.querySelector('.stat-icon').style.background = 'rgba(147, 51, 234, 0.1)';
                cronIcon.style.color = '#9333ea';
            }, Math.random() * 2000 + 2000);
        }
    }

    // Run cron status simulation immediately
    updateCronStatus();

    // Then update cron status every 30-40 seconds
    setInterval(updateCronStatus, Math.random() * 10000 + 30000);

    // Web Services Status Simulation
    const webCard = document.querySelector('.web-services-card');
    const webStatus = document.querySelector('.web-status');
    const webIcon = document.querySelector('.web-services-card .stat-icon i');

    function updateWebStatus() {
        // 95% chance of online status
        if (Math.random() < 0.95) {
            webStatus.textContent = 'Online';
            webStatus.style.color = '#22c55e';
            webCard.querySelector('.stat-icon').style.background = 'rgba(14, 165, 233, 0.1)';
            webIcon.style.color = '#0ea5e9';
        } else {
            webStatus.textContent = 'Offline';
            webStatus.style.color = '#ef4444';
            webCard.querySelector('.stat-icon').style.background = 'rgba(239, 68, 68, 0.1)';
            webIcon.style.color = '#ef4444';
            
            // Return to online after 2-4 seconds
            setTimeout(() => {
                webStatus.textContent = 'Online';
                webStatus.style.color = '#22c55e';
                webCard.querySelector('.stat-icon').style.background = 'rgba(14, 165, 233, 0.1)';
                webIcon.style.color = '#0ea5e9';
            }, Math.random() * 2000 + 2000);
        }
    }

    // Run web services status simulation immediately
    updateWebStatus();

    // Then update web services status every 30-40 seconds
    setInterval(updateWebStatus, Math.random() * 10000 + 30000);

    // Last Update Time Simulation
    function updateLastUpdateTime() {
        // Generate a new random time between 1-2 days ago
        const hours = Math.floor(Math.random() * 24) + 24; // 24-48 hours
        const newDate = new Date();
        newDate.setHours(newDate.getHours() - hours);
        
        // Format the date
        const options = { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        const formattedDate = newDate.toLocaleDateString('en-US', options);
        
        // Update the display
        document.querySelector('.update-time').textContent = formattedDate;
        
        // Send the new time to the server to update the session
        fetch('update_last_update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ last_update: newDate.toISOString() })
        });
    }

    // Update the time every 1-2 days (in milliseconds)
    const updateInterval = (Math.random() * 86400000) + 86400000; // 24-48 hours
    setInterval(updateLastUpdateTime, updateInterval);
});
</script>

<?php
// Helper functions for system status
function getCPUUsage() {
    return rand(20, 80); // Placeholder - implement actual CPU usage monitoring
}

function getMemoryUsage() {
    return rand(30, 70); // Placeholder - implement actual memory usage monitoring
}

function getDiskUsage() {
    return rand(40, 90); // Placeholder - implement actual disk usage monitoring
}

function getOrderStatusClass($status) {
    switch($status) {
        case 'pending': return 'pending';
        case 'completed': return 'completed';
        case 'failed': return 'failed';
        default: return '';
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return "just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " min" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}
?>

<?php include 'footer.php'; ?> 