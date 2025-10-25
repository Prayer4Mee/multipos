<?php
// =====================================
// app/Views/dashboard/widgets/customers_served.php
// =====================================
?>
<div class="col-lg-3 col-md-6">
    <div class="stats-card customers-card">
        <div class="stats-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stats-content">
            <h3><?= $customers_served ?></h3>
            <p>Customers Served</p>
            <div class="stats-detail">
                <small class="text-muted">
                    Avg. Order: ₱<?= number_format($avg_order_value, 2) ?>
                </small>
            </div>
        </div>
    </div>
</div>
