<?php
// =====================================
// app/Views/dashboard/widgets/orders_today.php
// =====================================
?>
<div class="col-lg-3 col-md-6">
    <div class="stats-card orders-card">
        <div class="stats-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stats-content">
            <h3><?= $orders_today ?></h3>
            <p>Orders Today</p>
            <div class="stats-breakdown">
                <div class="breakdown-item">
                    <span class="badge bg-success"><?= $completed_orders ?> Completed</span>
                </div>
                <div class="breakdown-item">
                    <span class="badge bg-warning"><?= $pending_orders ?> Pending</span>
                </div>
            </div>
        </div>
    </div>
</div>
