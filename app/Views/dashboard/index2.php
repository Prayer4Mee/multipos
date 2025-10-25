<?php
// =====================================
// app/Views/dashboard/index.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Welcome back, <?= $current_user['full_name'] ?>! 👋</h2>
                        <p class="text-muted">
                            <?= date('l, F j, Y') ?> | <?= $tenant_config['restaurant_name'] ?>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-certificate"></i> BIR Certified
                            </span>
                        </p>
                    </div>
                    <div class="weather-widget">
                        <div class="weather-info">
                            <i class="fas fa-sun text-warning"></i>
                            <span>32°C Manila</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <?= $this->include('dashboard/widgets/sales_summary') ?>
        <?= $this->include('dashboard/widgets/orders_today') ?>
        <?= $this->include('dashboard/widgets/customers_served') ?>
        <?= $this->include('dashboard/widgets/staff_status') ?>
    </div>
    
    <!-- Main Dashboard Content -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Sales Chart -->
            <?= $this->include('dashboard/widgets/sales_chart') ?>
            
            <!-- Recent Orders -->
            <?= $this->include('dashboard/widgets/recent_orders') ?>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Performance Metrics -->
            <?= $this->include('dashboard/widgets/performance_metrics') ?>
            
            <!-- Top Menu Items -->
            <?= $this->include('dashboard/widgets/top_menu_items') ?>
            
            <!-- Alerts & Notifications -->
            <?= $this->include('dashboard/widgets/alerts_notifications') ?>
        </div>
    </div>
    
    <!-- Additional Widgets for Manager/Owner -->
    <?php if (in_array($current_user['role'], ['manager', 'owner'])): ?>
    <div class="row mt-4">
        <div class="col-lg-6">
            <?= $this->include('dashboard/widgets/inventory_alerts') ?>
        </div>
        <div class="col-lg-6">
            <?= $this->include('dashboard/widgets/financial_summary') ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.weather-widget {
    background: rgba(255,255,255,0.2);
    padding: 10px 15px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.weather-info {
    font-weight: 600;
}
</style>
<?= $this->endSection() ?>