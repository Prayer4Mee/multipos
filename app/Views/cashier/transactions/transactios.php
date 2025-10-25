<?php
// =====================================
// app/Views/cashier/transactions.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>💳 Transaction History</h2>
            <p class="text-muted">Cashier: <?= $current_user['full_name'] ?> | Shift: <?= $shift_info['start_time'] ?></p>
        </div>
        <div>
            <button class="btn btn-outline-primary me-2" onclick="refreshTransactions()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-success" onclick="exportTransactions()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="date" class="form-control" id="date_from" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" class="form-control" id="date_to" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" id="payment_method">
                        <option value="">All Methods</option>
                        <option value="cash">💵 Cash</option>
                        <option value="card">💳 Card</option>
                        <option value="gcash">📱 GCash</option>
                        <option value="maya">📲 Maya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="status">
                        <option value="">All Status</option>
                        <option value="completed">✅ Completed</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="failed">❌ Failed</option>
                        <option value="refunded">🔄 Refunded</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button class="btn btn-primary" onclick="filterTransactions()">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button class="btn btn-secondary" onclick="clearFilters()">
                        <i class="fas fa-eraser"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-peso-sign fa-2x text-primary mb-2"></i>
                    <h5>Total Sales</h5>
                    <h3 class="text-primary" id="total-sales">₱<?= number_format($summary['total_sales'], 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-receipt fa-2x text-success mb-2"></i>
                    <h5>Transactions</h5>
                    <h3 class="text-success" id="total-transactions"><?= $summary['total_transactions'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                    <h5>Avg. Order</h5>
                    <h3 class="text-info" id="avg-order">₱<?= number_format($summary['avg_order'], 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h5>Pending</h5>
                    <h3 class="text-warning" id="pending-count"><?= $summary['pending_transactions'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Method Breakdown -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-credit-card"></i> Payment Method Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($payment_breakdown as $method => $data): ?>
                <div class="col-md-3">
                    <div class="payment-method-card">
                        <div class="d-flex align-items-center">
                            <div class="method-icon me-3">
                                <?php
                                $icons = [
                                    'cash' => '💵',
                                    'card' => '💳',
                                    'gcash' => '📱',
                                    'maya' => '📲'
                                ];
                                echo $icons[$method] ?? '💰';
                                ?>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= ucfirst($method) ?></h6>
                                <h5 class="text-primary mb-0">₱<?= number_format($data['amount'], 2) ?></h5>
                                <small class="text-muted"><?= $data['count'] ?> transactions (<?= $data['percentage'] ?>%)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Recent Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="transactions-table">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td>
                                <strong><?= $transaction['receipt_number'] ?></strong>
                            </td>
                            <td>
                                <small><?= date('H:i:s', strtotime($transaction['created_at'])) ?></small>
                            </td>
                            <td>
                                <?= $transaction['customer_name'] ?: 'Walk-in Customer' ?>
                                <?php if ($transaction['table_number']): ?>
                                    <br><small class="text-muted">Table <?= $transaction['table_number'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $transaction['item_count'] ?> items</span>
                            </td>
                            <td>
                                <strong>₱<?= number_format($transaction['total_amount'], 2) ?></strong>
                                <?php if ($transaction['vat_amount'] > 0): ?>
                                    <br><small class="text-muted">VAT: ₱<?= number_format($transaction['vat_amount'], 2) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $payment_badges = [
                                    'cash' => '<span class="badge bg-success">💵 Cash</span>',
                                    'card' => '<span class="badge bg-primary">💳 Card</span>',
                                    'gcash' => '<span class="badge bg-info">📱 GCash</span>',
                                    'maya' => '<span class="badge bg-warning">📲 Maya</span>'
                                ];
                                echo $payment_badges[$transaction['payment_method']] ?? '<span class="badge bg-secondary">Unknown</span>';
                                ?>
                                <?php if ($transaction['reference_number']): ?>
                                    <br><small class="text-muted">Ref: <?= substr($transaction['reference_number'], -8) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_badges = [
                                    'completed' => '<span class="badge bg-success">✅ Completed</span>',
                                    'pending' => '<span class="badge bg-warning">⏳ Pending</span>',
                                    'failed' => '<span class="badge bg-danger">❌ Failed</span>',
                                    'refunded' => '<span class="badge bg-info">🔄 Refunded</span>'
                                ];
                                echo $status_badges[$transaction['status']] ?? '<span class="badge bg-secondary">Unknown</span>';
                                ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewTransaction('<?= $transaction['id'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="printReceipt('<?= $transaction['id'] ?>')">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <?php if ($transaction['status'] === 'completed' && $transaction['payment_method'] !== 'cash'): ?>
                                    <button class="btn btn-outline-warning" onclick="refundTransaction('<?= $transaction['id'] ?>')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?= $pager->links() ?>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transaction-details">
                <!-- Transaction details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentTransactionId = null;

function refreshTransactions() {
    location.reload();
}

function filterTransactions() {
    const filters = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        payment_method: $('#payment_method').val(),
        status: $('#status').val()
    };
    
    const params = new URLSearchParams(filters);
    window.location.href = '?' + params.toString();
}

function clearFilters() {
    $('#date_from').val('<?= date('Y-m-d') ?>');
    $('#date_to').val('<?= date('Y-m-d') ?>');
    $('#payment_method').val('');
    $('#status').val('');
}

function viewTransaction(transactionId) {
    currentTransactionId = transactionId;
    
    $.ajax({
        url: '<?= base_url('api/transactions/') ?>' + transactionId,
        method: 'GET',
        beforeSend: function() {
            $('#transaction-details').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        },
        success: function(response) {
            $('#transaction-details').html(response.html);
            $('#transactionModal').modal('show');
        },
        error: function() {
            alert('Error loading transaction details');
        }
    });
}

function printReceipt(transactionId = null) {
    const id = transactionId || currentTransactionId;
    if (!id) return;
    
    $.ajax({
        url: '<?= base_url('api/transactions/') ?>' + id + '/print',
        method: 'POST',
        success: function(response) {
            if (response.success) {
                alert('Receipt sent to printer');
            } else {
                alert('Error printing receipt: ' + response.message);
            }
        },
        error: function() {
            alert('Error printing receipt');
        }
    });
}

function refundTransaction(transactionId) {
    if (!confirm('Are you sure you want to refund this transaction?')) return;
    
    $.ajax({
        url: '<?= base_url('api/transactions/') ?>' + transactionId + '/refund',
        method: 'POST',
        success: function(response) {
            if (response.success) {
                alert('Transaction refunded successfully');
                location.reload();
            } else {
                alert('Error processing refund: ' + response.message);
            }
        },
        error: function() {
            alert('Error processing refund');
        }
    });
}

function exportTransactions() {
    const filters = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        payment_method: $('#payment_method').val(),
        status: $('#status').val()
    };
    
    const params = new URLSearchParams(filters);
    window.open('<?= base_url('cashier/transactions/export') ?>?' + params.toString());
}

// Auto-refresh every 30 seconds
setInterval(function() {
    // Only refresh if no modal is open
    if (!$('.modal').hasClass('show')) {
        refreshTransactions();
    }
}, 30000);
</script>

<style>
.payment-method-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    border-left: 4px solid #007bff;
}

.method-icon {
    font-size: 2rem;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.badge {
    font-size: 0.8em;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
}
</style>
<?= $this->endSection() ?>