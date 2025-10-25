<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .payment-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .order-summary {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    .payment-method {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .payment-method:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    .payment-method.selected {
        border-color: #007bff;
        background-color: #e3f2fd;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container payment-container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-credit-card"></i> Payment</h1>
                <a href="<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to POS
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="order-summary">
                        <h6>Order #ORD20241201004</h6>
                        <p><strong>Table:</strong> 3</p>
                        <p><strong>Customer:</strong> New Customer</p>
                        <p><strong>Status:</strong> <span class="badge bg-warning">Preparing</span></p>
                        <hr>
                        
                        <h6>Order Items:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Yum Burger</td>
                                        <td>2</td>
                                        <td>₱100.00</td>
                                        <td>₱200.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount:</strong>
                            <strong class="text-primary">₱200.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <form id="paymentForm">
                        <div class="mb-3">
                            <label class="form-label">Amount Received</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="amountReceived" 
                                       placeholder="0.00" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="payment-methods">
                                <div class="payment-method" data-method="cash">
                                    <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                    <div>Cash</div>
                                </div>
                                <div class="payment-method" data-method="card">
                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                    <div>Card</div>
                                </div>
                                <div class="payment-method" data-method="gcash">
                                    <i class="fas fa-mobile-alt fa-2x text-info mb-2"></i>
                                    <div>GCash</div>
                                </div>
                                <div class="payment-method" data-method="maya">
                                    <i class="fas fa-wallet fa-2x text-warning mb-2"></i>
                                    <div>Maya</div>
                                </div>
                            </div>
                            <input type="hidden" id="paymentMethod" name="payment_method" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Change</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control" id="changeAmount" 
                                       value="0.00" readonly>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Complete Payment
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="printReceipt()">
                                <i class="fas fa-print"></i> Print Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    const totalAmount = 200.00;
    
    // Payment method selection
    $('.payment-method').click(function() {
        $('.payment-method').removeClass('selected');
        $(this).addClass('selected');
        $('#paymentMethod').val($(this).data('method'));
    });
    
    // Calculate change
    $('#amountReceived').on('input', function() {
        const received = parseFloat($(this).val()) || 0;
        const change = received - totalAmount;
        $('#changeAmount').val(change.toFixed(2));
    });
    
    // Form submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const amountReceived = parseFloat($('#amountReceived').val());
        const paymentMethod = $('#paymentMethod').val();
        
        if (!paymentMethod) {
            alert('Please select a payment method');
            return;
        }
        
        if (amountReceived < totalAmount) {
            alert('Amount received is less than total amount');
            return;
        }
        
        // Process payment
        alert('Payment completed successfully!');
        window.location.href = '<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>';
    });
});

function printReceipt() {
    window.open('<?= base_url("restaurant/{$tenant->tenant_slug}/print-receipt/5") ?>', '_blank');
}
</script>
<?= $this->endSection() ?>