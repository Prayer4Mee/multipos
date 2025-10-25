<?php
// =====================================
// app/Views/dashboard/widgets/staff_status.php
// =====================================
?>
<div class="col-lg-3 col-md-6">
    <div class="stats-card staff-card">
        <div class="stats-icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="stats-content">
            <h3><?= $staff_on_duty ?>/<?= $total_staff ?></h3>
            <p>Staff On Duty</p>
            <div class="staff-breakdown">
                <div class="d-flex justify-content-between">
                    <small>Cashiers: <?= $cashiers_on_duty ?></small>
                    <small>Kitchen: <?= $kitchen_on_duty ?></small>
                </div>
                <div class="d-flex justify-content-between">
                    <small>Waiters: <?= $waiters_on_duty ?></small>
                    <small>Managers: <?= $managers_on_duty ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
    margin-bottom: 20px;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-icon {
    float: right;
    font-size: 2.5rem;
    color: #667eea;
    opacity: 0.3;
}

.stats-content h3 {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
    margin: 0;
}

.stats-content p {
    color: #666;
    margin-bottom: 10px;
}

.sales-card { border-left-color: #28a745; }
.sales-card .stats-icon { color: #28a745; }

.orders-card { border-left-color: #007bff; }
.orders-card .stats-icon { color: #007bff; }

.customers-card { border-left-color: #6f42c1; }
.customers-card .stats-icon { color: #6f42c1; }

.staff-card { border-left-color: #fd7e14; }
.staff-card .stats-icon { color: #fd7e14; }

.stats-breakdown, .stats-detail, .staff-breakdown {
    margin-top: 10px;
}

.breakdown-item {
    margin-bottom: 5px;
}

.badge {
    font-size: 0.7rem;
}
</style>