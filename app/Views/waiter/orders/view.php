<?php
// =============================================================================
// WAITER/ORDERS/VIEW.PHP - View Order Details
// =============================================================================

// File: app/Views/waiter/orders/view.php
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Order Details #<?= esc($order['order_number']) ?> - <?= esc($tenant_config['restaurant_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 Order Details #<?= esc($order['order_number']) ?></h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button class="btn btn-info" onclick="printOrder()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-secondary" onclick="goBack()">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Order Header Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header"><strong>Order Information</strong></div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Order Number:</strong></td>
                                            <td>#<?= esc($order['order_number']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Table:</strong></td>
                                            <td>Table <?= $order['table_number'] ?> (<?= $order['party_size'] ?> guests)</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Customer:</strong></td>
                                            <td><?= esc($order['customer_name'] ?: 'Walk-in Customer') ?></td>
                                        </tr>
                                        <?php if ($order['customer_phone']): ?>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td><?= esc($order['customer_phone']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong>Order Status:</strong></td>
                                            <td>
                                                <span class="badge badge-<?= getOrderStatusColor($order['status']) ?> badge-lg">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payment Status:</strong></td>
                                            <td>
                                                <span class="badge badge-<?= getPaymentStatusColor($order['payment_status']) ?> badge-lg">
                                                    <?= ucfirst($order['payment_status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header"><strong>Timing Information</strong></div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Order Created:</strong></td>
                                            <td><?= date('F j, Y H:i:s', strtotime($order['created_at'])) ?></td>
                                        </tr>
                                        <?php if ($order['confirmed_at']): ?>
                                        <tr>
                                            <td><strong>Confirmed:</strong></td>
                                            <td><?= date('F j, Y H:i:s', strtotime($order['confirmed_at'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($order['kitchen_received_at']): ?>
                                        <tr>
                                            <td><strong>Kitchen Received:</strong></td>
                                            <td><?= date('F j, Y H:i:s', strtotime($order['kitchen_received_at'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($order['ready_at']): ?>
                                        <tr>
                                            <td><strong>Ready:</strong></td>
                                            <td><?= date('F j, Y H:i:s', strtotime($order['ready_at'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($order['served_at']): ?>
                                        <tr>
                                            <td><strong>Served:</strong></td>
                                            <td><?= date('F j, Y H:i:s', strtotime($order['served_at'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong>Total Time:</strong></td>
                                            <td><?= calculateTotalTime($order['created_at'], $order['served_at']) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><strong>Order Items</strong></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Quantity</th>
                                                    <th>Unit Price</th>
                                                    <th>Total Price</th>
                                                    <th>Special Instructions</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($order_items)): ?>
                                                    <?php foreach ($order_items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= esc($item['menu_item_name']) ?></strong>
                                                            <?php if ($item['category']): ?>
                                                                <br><small class="text-muted"><?= esc($item['category']) ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center"><?= $item['quantity'] ?></td>
                                                        <td class="text-right">₱<?= number_format($item['unit_price'], 2) ?></td>
                                                        <td class="text-right">₱<?= number_format($item['total_price'], 2) ?></td>
                                                        <td>
                                                            <?php if ($item['special_instructions']): ?>
                                                                <small class="text-info"><?= esc($item['special_instructions']) ?></small>
                                                            <?php else: ?>
                                                                <span class="text-muted">None</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-<?= getItemStatusColor($item['status']) ?>">
                                                                <?= ucfirst($item['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light">
                                                    <th colspan="3">Subtotal:</th>
                                                    <th class="text-right">₱<?= number_format($order['subtotal'], 2) ?></th>
                                                    <th colspan="2"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><strong>Order Summary</strong></div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td class="text-right">₱<?= number_format($order['subtotal'], 2) ?></td>
                                        </tr>
                                        <?php if ($order['discount_amount'] > 0): ?>
                                        <tr>
                                            <td>Discount (<?= esc($order['discount_type']) ?>):</td>
                                            <td class="text-right text-success">-₱<?= number_format($order['discount_amount'], 2) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td>Service Charge (<?= $tenant_config['service_charge'] * 100 ?>%):</td>
                                            <td class="text-right">₱<?= number_format($order['service_charge'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td>VAT (<?= $tenant_config['tax_rate'] * 100 ?>%):</td>
                                            <td class="text-right">₱<?= number_format($order['vat_amount'], 2) ?></td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><strong>Total Amount:</strong></td>
                                            <td class="text-right"><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><strong>Payment Information</strong></div>
                                <div class="card-body">
                                    <?php if (isset($payments) && !empty($payments)): ?>
                                        <table class="table table-sm">
                                            <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?= ucfirst($payment['payment_method']) ?>:</td>
                                                <td class="text-right">₱<?= number_format($payment['amount'], 2) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= getPaymentStatusColor($payment['status']) ?>">
                                                        <?= ucfirst($payment['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-light">
                                                <td><strong>Total Paid:</strong></td>
                                                <td class="text-right"><strong>₱<?= number_format($order['paid_amount'], 2) ?></strong></td>
                                                <td></td>
                                            </tr>
                                            <?php if ($order['balance_due'] > 0): ?>
                                            <tr class="table-danger">
                                                <td><strong>Balance Due:</strong></td>
                                                <td class="text-right"><strong>₱<?= number_format($order['balance_due'], 2) ?></strong></td>
                                                <td></td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No payments recorded yet
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Instructions & Notes -->
                    <?php if ($order['special_instructions'] || $order['notes']): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><strong>Special Instructions & Notes</strong></div>
                                <div class="card-body">
                                    <?php if ($order['special_instructions']): ?>
                                        <div class="mb-3">
                                            <h6>Special Instructions:</h6>
                                            <p class="text-info"><?= nl2br(esc($order['special_instructions'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['notes']): ?>
                                        <div>
                                            <h6>Internal Notes:</h6>
                                            <p class="text-muted"><?= nl2br(esc($order['notes'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Order History/Timeline -->
                    <?php if (isset($order_history)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><strong>Order Timeline</strong></div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <?php foreach ($order_history as $history): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-<?= getHistoryStatusColor($history['action']) ?>"></div>
                                            <div class="timeline-content">
                                                <h6><?= esc($history['action_description']) ?></h6>
                                                <p class="text-muted">
                                                    <?= date('F j, Y H:i:s', strtotime($history['created_at'])) ?>
                                                    <?php if ($history['user_name']): ?>
                                                        by <?= esc($history['user_name']) ?>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if ($history['notes']): ?>
                                                    <p><?= esc($history['notes']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><strong>Actions</strong></div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                            <button class="btn btn-warning" onclick="editOrder()">
                                                <i class="fas fa-edit"></i> Edit Order
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($order['status'] === 'ready'): ?>
                                            <button class="btn btn-success" onclick="markServed()">
                                                <i class="fas fa-check"></i> Mark as Served
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($order['payment_status'] === 'pending'): ?>
                                            <button class="btn btn-primary" onclick="processPayment()">
                                                <i class="fas fa-credit-card"></i> Process Payment
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-info" onclick="printReceipt()">
                                            <i class="fas fa-receipt"></i> Print Receipt
                                        </button>
                                        
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-danger" onclick="cancelOrder()">
                                                <i class="fas fa-times"></i> Cancel Order
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 3px solid #007bff;
}

.badge-lg {
    font-size: 0.9em;
    padding: 6px 12px;
}

@media print {
    .card-tools, .btn {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<script>
function printOrder() {
    window.print();
}

function goBack() {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders") ?>';
}

function editOrder() {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders/edit/{$order['id']}") ?>';
}

function markServed() {
    if (confirm('Mark this order as served?')) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/mark-served") ?>',
            method: 'POST',
            data: { order_id: <?= $order['id'] ?> },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Order marked as served!', 'success');
                    location.reload();
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

function processPayment() {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/cashier/payment/{$order['id']}") ?>';
}

function printReceipt() {
    window.open('<?= base_url("restaurant/{$tenant_id}/orders/print-receipt/{$order['id']}") ?>', '_blank');
}

function cancelOrder() {
    const reason = prompt('Reason for cancellation:');
    if (reason) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/cancel") ?>',
            method: 'POST',
            data: { 
                order_id: <?= $order['id'] ?>,
                reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Order cancelled successfully!', 'success');
                    location.reload();
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
function calculateTotalTime($created_at, $served_at) {
    if (!$served_at) return 'In Progress';
    
    $start = new DateTime($created_at);
    $end = new DateTime($served_at);
    $diff = $end->diff($start);
    
    $hours = $diff->h;
    $minutes = $diff->i;
    
    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'm';
    } else {
        return $minutes . 'm';
    }
}

function getItemStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'preparing' => 'info',
        'ready' => 'success',
        'served' => 'secondary'
    ];
    return $colors[$status] ?? 'secondary';
}

function getHistoryStatusColor($action) {
    $colors = [
        'created' => 'primary',
        'confirmed' => 'info',
        'kitchen_received' => 'warning',
        'preparing' => 'warning',
        'ready' => 'success',
        'served' => 'success',
        'paid' => 'success',
        'cancelled' => 'danger',
        'modified' => 'info'
    ];
    return $colors[$action] ?? 'secondary';
}
?>
</script>
<?= $this->endSection() ?>