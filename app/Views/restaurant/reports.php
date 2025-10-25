<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="reports-container">
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

    <!-- Date Range Filter -->
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

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">₱45,230</h4>
                            <p class="mb-0">Total Sales</p>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                    <small class="opacity-75">+12% from last month</small>
                </div>
            </div>
        </div>
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

    <!-- Charts Row -->
    <div class="row mb-4">
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Selling Items</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Chicken Burger</strong>
                                <br>
                                <small class="text-muted">₱89 each</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">45</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Fried Chicken</strong>
                                <br>
                                <small class="text-muted">₱75 each</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">38</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>French Fries</strong>
                                <br>
                                <small class="text-muted">₱45 each</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">32</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Coffee</strong>
                                <br>
                                <small class="text-muted">₱35 each</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">28</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="row">
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
                            <tbody>
                                <tr>
                                    <td>10:00 AM</td>
                                    <td>12</td>
                                    <td>₱1,440</td>
                                </tr>
                                <tr>
                                    <td>11:00 AM</td>
                                    <td>18</td>
                                    <td>₱2,160</td>
                                </tr>
                                <tr>
                                    <td>12:00 PM</td>
                                    <td>25</td>
                                    <td>₱3,000</td>
                                </tr>
                                <tr>
                                    <td>1:00 PM</td>
                                    <td>22</td>
                                    <td>₱2,640</td>
                                </tr>
                                <tr>
                                    <td>2:00 PM</td>
                                    <td>15</td>
                                    <td>₱1,800</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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

    <!-- Recent Orders -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Table</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#1234</td>
                                    <td>Table 2</td>
                                    <td>Chicken Burger, French Fries</td>
                                    <td>₱134</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>2:30 PM</td>
                                </tr>
                                <tr>
                                    <td>#1235</td>
                                    <td>Table 7</td>
                                    <td>Fried Chicken, Coffee</td>
                                    <td>₱110</td>
                                    <td><span class="badge bg-warning">In Progress</span></td>
                                    <td>2:25 PM</td>
                                </tr>
                                <tr>
                                    <td>#1236</td>
                                    <td>Table 1</td>
                                    <td>Beef Burger, French Fries, Coffee</td>
                                    <td>₱175</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>2:20 PM</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let salesChart, paymentChart;

$(document).ready(function() {
    initializeCharts();
    
    // Date range change handler
    $('#dateRange').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange, #customDateRange2').show();
        } else {
            $('#customDateRange, #customDateRange2').hide();
        }
    });
});

function initializeCharts() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Daily Sales',
                data: [12000, 15000, 18000, 14000, 22000, 25000, 20000],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Credit Card', 'Digital Wallet', 'Bank Transfer'],
            datasets: [{
                data: [45, 30, 20, 5],
                backgroundColor: [
                    '#28a745',
                    '#007bff',
                    '#ffc107',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function updateReports() {
    const dateRange = $('#dateRange').val();
    let fromDate, toDate;
    
    if (dateRange === 'custom') {
        fromDate = $('#fromDate').val();
        toDate = $('#toDate').val();
    } else {
        // Calculate dates based on selection
        const today = new Date();
        switch(dateRange) {
            case 'today':
                fromDate = toDate = today.toISOString().split('T')[0];
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                fromDate = toDate = yesterday.toISOString().split('T')[0];
                break;
            case 'week':
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                fromDate = weekStart.toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'month':
                const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                fromDate = monthStart.toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
        }
    }
    
    console.log('Updating reports for:', fromDate, 'to', toDate);
    
    // Simulate data update
    alert('Reports updated for the selected date range!');
}

function exportReport() {
    const dateRange = $('#dateRange').val();
    alert(`Exporting ${dateRange} report...`);
    
    // Simulate export functionality
    setTimeout(() => {
        alert('Report exported successfully!');
    }, 1000);
}

function refreshReports() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
