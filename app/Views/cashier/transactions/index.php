<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="col-md-9 col-lg-10">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-receipt"></i> Transactions</h2>
                <p class="text-muted">View and manage all transactions</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button class="btn btn-success" onclick="exportTransactions()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Total Sales</h4>
                                <h2>₱<?= number_format($totalSales ?? 0, 2) ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Today's Transactions</h4>
                                <h2><?= $todayTransactions ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-receipt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Average Order</h4>
                                <h2>₱<?= number_format($averageOrder ?? 0, 2) ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calculator fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Refunds</h4>
                                <h2><?= $refunds ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-undo fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Transaction History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($transactions) && !empty($transactions)): ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= $transaction['transaction_id'] ?></strong>
                                        </td>
                                        <td>
                                            <?= date('M d, Y', strtotime($transaction['created_at'])) ?><br>
                                            <small class="text-muted"><?= date('h:i A', strtotime($transaction['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <?= esc($transaction['customer_name'] ?? 'Walk-in') ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $transaction['item_count'] ?> items</span>
                                        </td>
                                        <td>
                                            <strong>₱<?= number_format($transaction['total_amount'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $transaction['payment_method'] === 'cash' ? 'success' : 'primary' ?>">
                                                <?= ucfirst($transaction['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $transaction['status'] === 'completed' ? 'success' : ($transaction['status'] === 'refunded' ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($transaction['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction(<?= $transaction['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="printReceipt(<?= $transaction['id'] ?>)">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <?php if ($transaction['status'] === 'completed'): ?>
                                                    <button class="btn btn-sm btn-outline-warning" onclick="refundTransaction(<?= $transaction['id'] ?>)">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No transactions found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Transactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="dateFrom" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="dateFrom" name="date_from">
                        </div>
                        <div class="col-md-6">
                            <label for="dateTo" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="dateTo" name="date_to">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" name="payment_method">
                                <option value="">All Methods</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="digital">Digital</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="refunded">Refunded</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyFilter()">Apply Filter</button>
            </div>
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
            <div class="modal-body" id="transactionDetails">
                <!-- Transaction details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewTransaction(transactionId) {
    // Load transaction details via AJAX
    fetch(`<?= base_url('restaurant/' . $tenant['tenant_slug'] . '/transactions/') ?>${transactionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('transactionDetails').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('transactionModal')).show();
        })
        .catch(error => console.error('Error:', error));
}

function printReceipt(transactionId) {
    // Open print window for receipt
    window.open(`<?= base_url('restaurant/' . $tenant['tenant_slug'] . '/transactions/') ?>${transactionId}/receipt`, '_blank');
}

function refundTransaction(transactionId) {
    if (confirm('Are you sure you want to refund this transaction?')) {
        // Process refund via AJAX
        fetch(`<?= base_url('restaurant/' . $tenant['tenant_slug'] . '/transactions/') ?>${transactionId}/refund`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error processing refund: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function applyFilter() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Reload page with filter parameters
    const params = new URLSearchParams(formData);
    window.location.href = `<?= base_url('restaurant/' . $tenant['tenant_slug'] . '/transactions') ?>?${params.toString()}`;
}

function exportTransactions() {
    // Export transactions to CSV/Excel
    window.location.href = `<?= base_url('restaurant/' . $tenant['tenant_slug'] . '/transactions/export') ?>`;
}
</script>
<?= $this->endSection() ?>
