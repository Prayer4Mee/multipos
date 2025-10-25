<?php
// ============================================================================
// Manager Dashboard View
// app/Views/dashboard/index.php
// ============================================================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .dashboard-card {
        transition: transform 0.2s;
    }
    .dashboard-card:hover {
        transform: translateY(-2px);
    }
    .metric-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-color);
    }
    .metric-change {
        font-size: 0.9rem;
    }
    .metric-change.positive {
        color: #28a745;
    }
    .metric-change.negative {
        color: #dc3545;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard Overview</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickActionModal">
                <i class="fas fa-plus"></i> Quick Action
            </button>
        </div>
    </div>
    
    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <div class="metric-icon mb-2">
                        <i class="fas fa-peso-sign text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title text-muted">Today's Sales</h6>
                    <div class="metric-value">₱<?= number_format($dashboard_stats['today_sales'], 2) ?></div>
                    <div class="metric-change positive">
                        <i class="fas fa-arrow-up"></i> +12% vs yesterday
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <div class="metric-icon mb-2">
                        <i class="fas fa-receipt text-info" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title text-muted">Orders Today</h6>
                    <div class="metric-value"><?= $dashboard_stats['orders_count'] ?></div>
                    <div class="metric-change positive">
                        <i class="fas fa-arrow-up"></i> +8% vs yesterday
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <div class="metric-icon mb-2">
                        <i class="fas fa-users text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title text-muted">Staff On Duty</h6>
                    <div class="metric-value"><?= $dashboard_stats['staff_on_duty'] ?></div>
                    <div class="metric-change">
                        <i class="fas fa-clock"></i> Current shift
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <div class="metric-icon mb-2">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title text-muted">Low Stock Items</h6>
                    <div class="metric-value"><?= $dashboard_stats['low_stock_count'] ?></div>
                    <div class="metric-change">
                        <i class="fas fa-boxes"></i> Requires attention
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Details Row -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock text-primary"></i> Recent Orders
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Table</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboard_stats['recent_orders'] as $order): ?>
                                <tr>
                                    <td><strong><?= $order['order_number'] ?></strong></td>
                                    <td><?= $order['table_number'] ?? 'N/A' ?></td>
                                    <td>
                                        <span class="badge bg-<?= getStatusColor($order['status']) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                    <td><?= date('H:i', strtotime($order['ordered_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Low Stock Alert -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle text-warning"></i> Low Stock Alert
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($dashboard_stats['low_stock_items'])): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>All items are well stocked!</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($dashboard_stats['low_stock_items'] as $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= $item['name'] ?></strong>
                                    <br>
                                    <small class="text-muted">Current: <?= $item['current_stock'] ?> | Reorder: <?= $item['reorder_level'] ?></small>
                                </div>
                                <span class="badge bg-danger">Low</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-refresh dashboard every 30 seconds
    let dashboardInterval;
    
    function startDashboardRefresh() {
        dashboardInterval = setInterval(refreshDashboardStats, 30000);
    }
    
    function refreshDashboard() {
        refreshDashboardStats();
        location.reload();
    }
    
    function refreshDashboardStats() {
        $.ajax({
            url: '<?= $base_url ?>/dashboard/ajax-stats',
            method: 'GET',
            success: function(response) {
                // Update metric values without full page reload
                console.log('Dashboard stats updated', response);
            },
            error: function() {
                console.error('Failed to refresh dashboard stats');
            }
        });
    }
    
    // Start auto-refresh when page loads
    $(document).ready(function() {
        startDashboardRefresh();
    });
    
    // Stop refresh when page is hidden (optimization)
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            clearInterval(dashboardInterval);
        } else {
            startDashboardRefresh();
        }
    });
</script>
<?= $this->endSection() ?>

<?php
// Helper function for status colors
function getStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'preparing': return 'info';
        case 'ready': return 'success';
        case 'completed': return 'primary';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>