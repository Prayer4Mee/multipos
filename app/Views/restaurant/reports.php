<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="reports-container">
    <!---------------------------------- 
    Contains Reports and Analytics 
    ----------------------------------->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-line"></i> Reports & Analytics</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshReports()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-success" onclick="exportReport()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    <!---------------------------------- 
    Ends: Contains Reports and Analytics 
    ----------------------------------->

    <!----------------------------------
    Date Range Filter 
    ----------------------------------->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select class="form-select" id="dateRange" onchange="updateReports()">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="customDateRange" style="display: none;">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" id="fromDate">
                </div>
                <div class="col-md-3" id="customDateRange2" style="display: none;">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" id="toDate">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" onclick="updateReports()">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!----------------------------------
    End: Date Range Filter 
    ----------------------------------->

    <!----------------------------------
    Key Metrics: Cards of Total Sales, Total Orders, Avg Order Value, Customer Rating 
    ----------------------------------->
     <!-- Total Sales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">₱<?= number_format($metrics->total_sales ?? 0, 2) ?></h4>
                            <p class="mb-0">Total Sales</p>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                    <small class="opacity-75">+12% from last month</small>
                </div>
            </div>
        </div>
        <!-- Total Orders -->
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">342</h4>
                            <p class="mb-0">Total Orders</p>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                    <small class="opacity-75">+8% from last month</small>
                </div>
            </div>
        </div>
        <!-- Avg Order Value -->
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">₱132</h4>
                            <p class="mb-0">Avg Order Value</p>
                        </div>
                        <i class="fas fa-calculator fa-2x"></i>
                    </div>
                    <small class="opacity-75">+5% from last month</small>
                </div>
            </div>
        </div>
        <!-- Customer Rating -->
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">4.2</h4>
                            <p class="mb-0">Customer Rating</p>
                        </div>
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                    <small class="opacity-75">Based on 156 reviews</small>
                </div>
            </div>
        </div>
    </div>
    <!----------------------------------
    End: Key Metrics: Cards of Total Sales, Total Orders, Avg Order Value, Customer Rating  
    ----------------------------------->

    <!----------------------------------
    Charts Row 
    ----------------------------------->
    <div class="row mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sales Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <!-- Top Selling Items -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Selling Items</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($popular_items)): ?>
                            <?php foreach ($popular_items as $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= $item->name ?></strong>
                                    
                                    <br>
                                    <small class="text-muted">₱<?= number_format($item->price, 2) ?></small>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?= $item->orders_count ?> sold</span>
                            </div>
                        <?php endforeach; ?>
                        <?php else: ?>
        <div class="list-group-item">No data available for this month.</div>
    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <!----------------------------------
    End: Charts Row 
    ----------------------------------->

    <!----------------------------------  
    Detailed Reports 
    ----------------------------------->
    <div class="row">
        <!-- Hourly Sales -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hourly Sales</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Hour</th>
                                    <th>Orders</th>
                                    <th>Sales</th>
                                </tr>
                            </thead>
                            <!-- Add your php code here -->
                            <tbody>
                                <?php if (!empty($hourly_sales)):?>
                                    <?php foreach ($hourly_sales as $hour_data): ?>
                                        <tr>
                                            <td><?= str_pad($hour_data->hour, 2, '0', STR_PAD_LEFT) ?>:00 - <?= str_pad($hour_data->hour, 2, '0', STR_PAD_LEFT) ?>:59</td>
                                            <td><?= $hour_data->total_orders ?? 0 ?></td>
                                            <td>₱<?= number_format($hour_data->total_sales ?? 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No hourly sales data available for today.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Payment Methods Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Methods</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="200"></canvas>
                </div>
            </div>
        </div>

    </div>
    <!----------------------------------  
    End: Detailed Reports 
    ----------------------------------->

    <!----------------------------------
    Recent Orders 
    ----------------------------------->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <!-- Main Header: Recent Orders -->
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                </div>
                <!-- List -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Table</th>
                                    <th>Items</th>
                                    <th>Payment</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <!-- Input your php code here -->
                            <tbody>
                                <?php if (!empty($recent_orders)): ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <?php 
                                            $badgeClass = match($order->status) {
                                                'completed' => 'bg-success',
                                                'pending' => 'bg-warning',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <tr>
                                            <td>#<?= $order->id ?></td>
                                            <td>Table <?= $order->table_number ?? 'N/A' ?></td>
                                            <td><?= $order->items ?? 'N/A' ?></td>
                                            <td><?= ucfirst($order->payment_method ?? 'N/A') ?></td>
                                            <td>₱<?= number_format($order->total_amount, 2) ?></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($order->status) ?></span></td>
                                            <td><?= date('g:i A', strtotime($order->ordered_at)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No recent orders found.</td>
                                        </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!----------------------------------
End: Recent Orders 
----------------------------------->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let salesChart, paymentChart;

// Helper function to detect which preset a date range matches
function detectPreset(fromDate, toDate) {
    if (fromDate === toDate) {
        const today = formatDateISO(new Date());
        if (fromDate === today) return 'today';
        
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        const yesterdayStr = formatDateISO(yesterday);
        if (fromDate === yesterdayStr) return 'yesterday';
    }
    return 'custom';
}

// Global date formatting function
function formatDateISO(d) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Global preset dates function
function getPresetDates(preset) {
    const today = new Date();
    
    switch(preset) {
        case 'today': {
            const dateStr = formatDateISO(today);
            return { from: dateStr, to: dateStr };
        }
        case 'yesterday': {
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);
            const dateStr = formatDateISO(yesterday);
            return { from: dateStr, to: dateStr };
        }
        case 'week': {
            const weekStart = new Date(today);
            const day = weekStart.getDay();
            const diff = weekStart.getDate() - day;
            weekStart.setDate(diff);
            return { 
                from: formatDateISO(weekStart), 
                to: formatDateISO(today) 
            };
        }
        case 'month': {
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            return { 
                from: formatDateISO(monthStart), 
                to: formatDateISO(today) 
            };
        }
        default:
            return null;
    }
}

function handleDateRangeChange() {
    const dateRange = $('#dateRange').val();
    
    // Show/hide custom date inputs
    if (dateRange === 'custom') {
        $('#customDateRange, #customDateRange2').show();
    } else {
        $('#customDateRange, #customDateRange2').hide();
        // Update the date inputs with preset values
        const dates = getPresetDates(dateRange);
        if (dates) {
            $('#fromDate').val(dates.from);
            $('#toDate').val(dates.to);
        }
    }
    
    // Reinitialize charts immediately
    initializeCharts();
}

function initializeCharts() {
    try {
        // Check what data we should use based on the current range
        const dateRange = $('#dateRange').val();
        const isHourly = (dateRange === 'today' || dateRange === 'yesterday');
        
        let chartLabels = [];
        let chartData = [];
        let chartTitle = 'Sales Trend';
        
        if (isHourly) {
            // Use hourly data
            const hourlyData = <?= json_encode($hourly_sales ?? []) ?>;
            
            // Create labels and data for all 24 hours
            for (let i = 0; i < 24; i++) {
                const hour = String(i).padStart(2, '0');
                chartLabels.push(hour + ':00');
                
                // Find data for this hour
                const hourData = hourlyData.find(item => parseInt(item.hour) === i);
                chartData.push(hourData ? parseFloat(hourData.total_sales) : 0);
            }
            
            chartTitle = dateRange === 'today' ? 'Hourly Sales - Today' : 'Hourly Sales - Yesterday';
        } else {
            // Use daily data
            const salesData = <?= json_encode($daily_sales ?? []) ?>;
            chartLabels = salesData.map(item => item.sale_date);
            chartData = salesData.map(item => parseFloat(item.total_daily_sales));
            chartTitle = 'Daily Sales Trend';
        }
        
        // Update the chart title
        $('#chartTitle').text(chartTitle);
        
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (salesChart) {
            salesChart.destroy();
        }
        
        salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: chartTitle,
                    data: chartData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(75, 192, 192)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString('en-US', {minimumFractionDigits: 0});
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });

        // Initialize Payment Methods Chart
        const paymentData = <?= json_encode($payment_methods ?? []) ?>;
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        
        // Destroy existing payment chart if it exists
        if (paymentChart) {
            paymentChart.destroy();
        }
        
        paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: paymentData.map(p => p.payment_method.charAt(0).toUpperCase() + p.payment_method.slice(1)),
                datasets: [{
                    data: paymentData.map(p => parseFloat(p.total_amount)),
                    backgroundColor: [
                        '#28a745',
                        '#007bff',
                        '#ffc107',
                        '#6c757d',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const fromDateParam = urlParams.get('fromDate');
    const toDateParam = urlParams.get('toDate');
    const rangeTypeParam = urlParams.get('rangeType');

    // 1. Check if we have dates in the URL first
    if (fromDateParam && toDateParam) {
        $('#fromDate').val(fromDateParam);
        $('#toDate').val(toDateParam);

        // 2. Decide what the dropdown should show
        if (rangeTypeParam) {
            $('#dateRange').val(rangeTypeParam);
        } else {
            const detected = detectPreset(fromDateParam, toDateParam);
            $('#dateRange').val(detected);
        }

        // 3. Show/Hide custom inputs based on the selection
        if ($('#dateRange').val() === 'custom') {
            $('#customDateRange, #customDateRange2').show();
        } else {
            $('#customDateRange, #customDateRange2').hide();
        }
    } else {
        // DEFAULT: Use "This Month"
        const defaultDates = getPresetDates('month');
        $('#dateRange').val('month');
        $('#fromDate').val(defaultDates.from);
        $('#toDate').val(defaultDates.to);
        $('#customDateRange, #customDateRange2').hide();
    }

    // Initialize charts
    initializeCharts();
});

function updateReports() {
    const dateRange = $('#dateRange').val();
    let fromDate, toDate;

    if (dateRange === 'custom') {
        fromDate = $('#fromDate').val();
        toDate = $('#toDate').val();

        if (!fromDate || !toDate) {
            alert('Please select both from and to dates');
            return;
        }

        if (!/^\d{4}-\d{2}-\d{2}$/.test(fromDate) || !/^\d{4}-\d{2}-\d{2}$/.test(toDate)) {
            alert('Invalid date format');
            return;
        }

        if (new Date(fromDate) > new Date(toDate)) {
            alert('From date must be before or equal to to date');
            return;
        }
    } else {
        const dates = getPresetDates(dateRange);
        if (dates) {
            fromDate = dates.from;
            toDate = dates.to;
        } else {
            alert('Invalid date range selection');
            return;
        }
    }

    const tenantSlug = '<?= $tenant_slug ?>';
    const url = `<?= base_url("restaurant/") ?>${tenantSlug}/reports?fromDate=${fromDate}&toDate=${toDate}&rangeType=${dateRange}`;
    window.location.href = url;
}

function exportReport() {
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();
    
    const baseUrl = "<?= base_url("restaurant/{$tenant_slug}/reports/export") ?>";
    window.location.href = `${baseUrl}?fromDate=${fromDate}&toDate=${toDate}`;
}

function refreshReports() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
