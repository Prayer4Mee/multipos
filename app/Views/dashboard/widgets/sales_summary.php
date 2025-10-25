<?php
// =====================================
// app/Views/dashboard/widgets/sales_summary.php
// =====================================
?>
<div class="col-lg-3 col-md-6">
    <div class="stats-card sales-card">
        <div class="stats-icon">
            <i class="fas fa-peso-sign"></i>
        </div>
        <div class="stats-content">
            <h3>₱<?= number_format($today_sales, 2) ?></h3>
            <p>Today's Sales</p>
            <div class="stats-trend">
                <?php 
                $trend_class = $sales_trend >= 0 ? 'text-success' : 'text-danger';
                $trend_icon = $sales_trend >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                ?>
                <span class="<?= $trend_class ?>">
                    <i class="fas <?= $trend_icon ?>"></i> 
                    <?= abs($sales_trend) ?>%
                </span>
                <small class="text-muted">vs yesterday</small>
            </div>
        </div>
    </div>
</div>