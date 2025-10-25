// =====================================
// app/Views/reports/sales/index.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-chart-line"></i> Sales Reports</h2>
            <p class="text-muted">Comprehensive sales analytics and reporting</p>
        </div>
        <div>
            <button class="btn btn-success me-2" onclick="exportReport()">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
            <button class="btn btn-primary" onclick="scheduleReport()">
                <i class="fas fa-clock"></i> Schedule Report
            </button>
        </div>
    </div>
    
    <!-- Report Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select class="form-select" id="date-range">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month" selected>This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="custom-date-range" style="display: none;">
                    <label class="form-label">Custom Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="start-date">
                        <input type="date" class="form-control" id="end-date">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Report Type</label>
                    <select class="form-select" id="report-type">
                        <option value="summary">Summary</option>
                        <option value="detailed">Detailed</option>
                        <option value="comparison">Comparison</option>
                        <option value="trends">Trends</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Group By</label>
                    <select class="form-select" id="group-by">
                        <option value="day">Daily</option>
                        <option value="week">Weekly</option>
                        <option value="month">Monthly</option>
                        <option value="category">Category</option>
                        <option value="payment">Payment Method</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary d-block" onclick="generateReport()">
                        <i class="fas fa-chart-bar"></i> Generate
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="metric-card revenue-card">
                <div class="metric-icon">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div class="metric-content">
                    <h3 id="total-revenue">₱<?= number_format($metrics['total_revenue'], 2) ?></h3>
                    <p>Total Revenue</p>
                    <div class="metric-change">
                        <span class="<?= $metrics['revenue_change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <i class="fas fa-arrow-<?= $metrics['revenue_change'] >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs($metrics['revenue_change']) ?>%
                        </span>
                        <small>vs previous period</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="metric-card orders-card">
                <div class="metric-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="metric-content">
                    <h3 id="total-orders"><?= number_format($metrics['total_orders']) ?></h3>
                    <p>Total Orders</p>
                    <div class="metric-change">
                        <span class="<?= $metrics['orders_change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <i class="fas fa-arrow-<?= $metrics['orders_change'] >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs($metrics['orders_change']) ?>%
                        </span>
                        <small>vs previous period</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="metric-card aov-card">
                <div class="metric-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="metric-content">
                    <h3 id="avg-order-value">₱<?= number_format($metrics['avg_order_value'], 2) ?></h3>
                    <p>Avg Order Value</p>
                    <div class="metric-change">
                        <span class="<?= $metrics['aov_change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <i class="fas fa-arrow-<?= $metrics['aov_change'] >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs($metrics['aov_change']) ?>%
                        </span>
                        <small>vs previous period</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="metric-card customers-card">
                <div class="metric-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-content">
                    <h3 id="total-customers"><?= number_format($metrics['total_customers']) ?></h3>
                    <p>Customers Served</p>
                    <div class="metric-change">
                        <span class="<?= $metrics['customers_change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <i class="fas fa-arrow-<?= $metrics['customers_change'] >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs($metrics['customers_change']) ?>%
                        </span>
                        <small>vs previous period</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Sales Trend</h5>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" data-chart="revenue">Revenue</button>
                            <button class="btn btn-outline-primary" data-chart="orders">Orders</button>
                            <button class="btn btn-outline-primary" data-chart="customers">Customers</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Payment Methods</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart" height="300"></canvas>
                    <div class="payment-breakdown mt-3">
                        <?php foreach ($payment_breakdown as $method => $data): ?>
                        <div class="payment-item">
                            <div class="payment-info">
                                <span class="payment-method">
                                    <?php
                                    $icons = ['cash' => '💵', 'card' => '💳', 'gcash' => '📱', 'maya' => '📲'];
                                    echo $icons[$method] ?? '💰';
                                    ?>
                                    <?= ucfirst($method) ?>
                                </span>
                                <span class="payment-amount">₱<?= number_format($data['amount'], 2) ?></span>
                            </div>
                            <div class="payment-percentage"><?= $data['percentage'] ?>%</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Reports Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="report-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#hourly-sales">Hourly Breakdown</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#menu-performance">Menu Performance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#staff-performance">Staff Performance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#customer-analysis">Customer Analysis</a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content">
                
                <!-- Hourly Sales -->
                <div class="tab-pane fade show active" id="hourly-sales">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Hour</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                    <th>Avg Order Value</th>
                                    <th>Top Item</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hourly_sales as $hour => $data): ?>
                                <tr>
                                    <td><strong><?= $hour ?>:00</strong></td>
                                    <td><?= $data['orders'] ?></td>
                                    <td>₱<?= number_format($data['revenue'], 2) ?></td>
                                    <td>₱<?= number_format($data['avg_order_value'], 2) ?></td>
                                    <td><?= $data['top_item'] ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" style="width: <?= $data['performance_percentage'] ?>%">
                                                <?= $data['performance_percentage'] ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Menu Performance -->
                <div class="tab-pane fade" id="menu-performance">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Menu Item</th>
                                            <th>Quantity Sold</th>
                                            <th>Revenue</th>
                                            <th>Profit Margin</th>
                                            <th>Rating</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($menu_performance as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= base_url($item['image']) ?>" alt="" class="menu-item-thumb me-2">
                                                    <strong><?= $item['name'] ?></strong>
                                                </div>
                                            </td>
                                            <td><?= $item['quantity_sold'] ?></td>
                                            <td>₱<?= number_format($item['revenue'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $item['profit_margin'] > 50 ? 'success' : ($item['profit_margin'] > 30 ? 'warning' : 'danger') ?>">
                                                    <?= $item['profit_margin'] ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <div class="rating-stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $item['avg_rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                                    <?php endfor; ?>
                                                    <small>(<?= $item['rating_count'] ?>)</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="trend <?= $item['trend'] ?>">
                                                    <i class="fas fa-arrow-<?= $item['trend'] === 'up' ? 'up text-success' : ($item['trend'] === 'down' ? 'down text-danger' : 'right text-warning') ?>"></i>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="top-performers">
                                <h6>🏆 Top Performers</h6>
                                <?php foreach (array_slice($menu_performance, 0, 5) as $index => $item): ?>
                                <div class="performer-item">
                                    <div class="rank">#<?= $index + 1 ?></div>
                                    <div class="item-info">
                                        <div class="item-name"><?= $item['name'] ?></div>
                                        <div class="item-stats">
                                            <?= $item['quantity_sold'] ?> sold | ₱<?= number_format($item['revenue'], 0) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>