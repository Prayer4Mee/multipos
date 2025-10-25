<?php
/**
 * TouchPoint POS - Additional Waiter Module Files
 * NTEKSYSTEMS Inc.
 * BIR-Certified Multi-Tenant Restaurant Management System
 */

// =============================================================================
// WAITER/ORDERS/INDEX.PHP - Orders List/Management
// =============================================================================

// File: app/Views/waiter/orders/index.php
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Order Management - <?= esc($tenant_config['restaurant_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 Order Management</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button class="btn btn-success" onclick="newOrder()">
                                <i class="fas fa-plus"></i> New Order
                            </button>
                            <button class="btn btn-info" onclick="refreshOrders()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Order Filters -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <select id="status_filter" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="preparing">Preparing</option>
                                <option value="ready">Ready</option>
                                <option value="served">Served</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="table_filter" class="form-control">
                                <option value="">All Tables</option>
                                <?php if (isset($tables)): ?>
                                    <?php foreach ($tables as $table): ?>
                                    <option value="<?= $table['id'] ?>">Table <?= $table['number'] ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="payment_filter" class="form-control">
                                <option value="">All Payments</option>
                                <option value="pending">Payment Pending</option>
                                <option value="partial">Partially Paid</option>
                                <option value="paid">Fully Paid</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="search_orders" class="form-control" placeholder="Search orders...">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="date" id="date_filter" class="form-control" value="<?= date('Y-m-d') ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" onclick="applyFilters()">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending</span>
                                    <span class="info-box-number"><?= $order_stats['pending'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-utensils"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Preparing</span>
                                    <span class="info-box-number"><?= $order_stats['preparing'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ready</span>
                                    <span class="info-box-number"><?= $order_stats['ready'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-smile"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Served</span>
                                    <span class="info-box-number"><?= $order_stats['served'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Completed</span>
                                    <span class="info-box-number"><?= $order_stats['completed'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cancelled</span>
                                    <span class="info-box-number"><?= $order_stats['cancelled'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table id="orders_table" class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Order #</th>
                                    <th>Table</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Order Status</th>
                                    <th>Payment Status</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($orders) && !empty($orders)): ?>
                                    <?php foreach ($orders as $order): ?>
                                    <tr data-order-id="<?= $order['id'] ?>" class="order-row">
                                        <td>
                                            <strong>#<?= esc($order['order_number']) ?></strong>
                                            <?php if ($order['is_priority']): ?>
                                                <span class="badge badge-warning">PRIORITY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">T<?= $order['table_number'] ?></span>
                                            <br><small><?= $order['party_size'] ?> guests</small>
                                        </td>
                                        <td>
                                            <?= esc($order['customer_name'] ?: 'Walk-in') ?>
                                            <?php if ($order['customer_phone']): ?>
                                                <br><small><?= esc($order['customer_phone']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= $order['item_count'] ?> items</span>
                                            <br><small>₱<?= number_format($order['subtotal'], 2) ?></small>
                                        </td>
                                        <td>
                                            <strong>₱<?= number_format($order['total_amount'], 2) ?></strong>
                                            <?php if ($order['discount_amount'] > 0): ?>
                                                <br><small class="text-success">-₱<?= number_format($order['discount_amount'], 2) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= getOrderStatusColor($order['status']) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                            <?php if ($order['estimated_time']): ?>
                                                <br><small>ETA: <?= $order['estimated_time'] ?>m</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= getPaymentStatusColor($order['payment_status']) ?>">
                                                <?= ucfirst($order['payment_status']) ?>
                                            </span>
                                            <?php if ($order['payment_method']): ?>
                                                <br><small><?= ucfirst($order['payment_method']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('H:i', strtotime($order['created_at'])) ?>
                                                <br><?= time_elapsed_string($order['created_at']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical">
                                                <button class="btn btn-sm btn-info" onclick="viewOrder(<?= $order['id'] ?>)" 
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editOrder(<?= $order['id'] ?>)" 
                                                            title="Edit Order">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($order['status'] === 'ready'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="markServed(<?= $order['id'] ?>)" 
                                                            title="Mark as Served">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($order['payment_status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-primary" onclick="processPayment(<?= $order['id'] ?>)" 
                                                            title="Process Payment">
                                                        <i class="fas fa-credit-card"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="cancelOrder(<?= $order['id'] ?>)" 
                                                            title="Cancel Order">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printOrder()">Print Order</button>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box .info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 35px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box .info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.info-box .info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 13px;
}

.info-box .info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.order-row:hover {
    background-color: #f8f9fa !important;
}

.btn-group-vertical .btn {
    margin-bottom: 2px;
}

@media (max-width: 768px) {
    .btn-group-vertical {
        width: 100%;
    }
    
    .info-box .info-box-icon {
        width: 70px;
        height: 70px;
        font-size: 25px;
        line-height: 70px;
    }
    
    .info-box .info-box-content {
        margin-left: 70px;
    }
}
</style>

<script>
// Order Management JavaScript
$(document).ready(function() {
    // Initialize DataTable
    $('#orders_table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[7, 'desc']], // Order by time
        columnDefs: [
            { orderable: false, targets: [8] } // Actions column
        ]
    });

    // Auto-refresh every 30 seconds
    setInterval(refreshOrders, 30000);

    // Filter change handlers
    $('#status_filter, #table_filter, #payment_filter').change(applyFilters);
    $('#search_orders').on('input', debounce(applyFilters, 500));
});

function newOrder() {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders/new") ?>';
}

function refreshOrders() {
    location.reload();
}

function applyFilters() {
    const filters = {
        status: $('#status_filter').val(),
        table: $('#table_filter').val(),
        payment: $('#payment_filter').val(),
        search: $('#search_orders').val(),
        date: $('#date_filter').val()
    };

    // Apply filters to DataTable
    const table = $('#orders_table').DataTable();
    
    // Custom filter function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        // Implement filtering logic here
        return true; // For now, show all
    });
    
    table.draw();
}

function viewOrder(orderId) {
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/details/") ?>' + orderId,
        method: 'GET',
        beforeSend: function() {
            $('#orderDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            $('#orderDetailsModal').modal('show');
        },
        success: function(response) {
            $('#orderDetailsContent').html(response);
        },
        error: function() {
            $('#orderDetailsContent').html('<div class="alert alert-danger">Error loading order details.</div>');
        }
    });
}

function editOrder(orderId) {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders/edit/") ?>' + orderId;
}

function markServed(orderId) {
    if (confirm('Mark this order as served?')) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/mark-served") ?>',
            method: 'POST',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Order marked as served!', 'success');
                    refreshOrders();
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error updating order status.', 'error');
            }
        });
    }
}

function processPayment(orderId) {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/cashier/payment/") ?>' + orderId;
}

function cancelOrder(orderId) {
    const reason = prompt('Reason for cancellation:');
    if (reason) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/cancel") ?>',
            method: 'POST',
            data: { 
                order_id: orderId,
                reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Order cancelled successfully!', 'success');
                    refreshOrders();
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error cancelling order.', 'error');
            }
        });
    }
}

function printOrder() {
    const orderContent = document.getElementById('orderDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Order Details</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>${orderContent}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('body').append(notification);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

<?php
function getOrderStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'preparing' => 'primary',
        'ready' => 'success',
        'served' => 'success',
        'completed' => 'secondary',
        'cancelled' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

function getPaymentStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'partial' => 'info',
        'paid' => 'success',
        'refunded' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

function time_elapsed_string($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return $time . 's ago';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    return floor($time/86400) . 'd ago';
}
?>
</script>
<?= $this->endSection() ?>