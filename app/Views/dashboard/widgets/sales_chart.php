<?php
// =====================================
// app/Views/dashboard/widgets/sales_chart.php
// =====================================
?>
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-chart-line"></i> Sales Overview</h5>
        <div class="card-tools">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary active" data-period="today">Today</button>
                <button class="btn btn-outline-primary" data-period="week">This Week</button>
                <button class="btn btn-outline-primary" data-period="month">This Month</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="300"></canvas>
    </div>
</div>

<script>
// Sales Chart Implementation
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Sales (₱)',
            data: <?= json_encode($chart_data) ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
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

// Period selection handlers
$('.btn-group button').click(function() {
    $('.btn-group button').removeClass('active');
    $(this).addClass('active');
    
    const period = $(this).data('period');
    updateSalesChart(period);
});

function updateSalesChart(period) {
    $.get(`<?= base_url('api/dashboard/sales-chart') ?>?period=${period}`, function(data) {
        salesChart.data.labels = data.labels;
        salesChart.data.datasets[0].data = data.data;
        salesChart.update();
    });
}
</script>