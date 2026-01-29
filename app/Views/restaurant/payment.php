<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .payment-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .order-summary {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    .discount-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: bold;
    }
    .discount-applied {
        background-color: #d4edda;
        color: #155724;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container payment-container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
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
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="order-summary">
                        <h6 class="mb-3"><strong>Order #<?= $order->order_number ?></strong></h6>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <p><strong>Table:</strong> <?= $order->table_number ?? 'N/A' ?></p>
                                <p><strong>Customer:</strong> <?= $order->customer_name ?? 'Walk-in' ?></p>
                            </div>
                            <div class="col-6 text-end">
                                <p><strong>Status:</strong> <span class="badge bg-warning"><?= ucfirst($order->status) ?></span></p>
                                <p><strong>Time:</strong> <?= date('H:i', strtotime($order->ordered_at)) ?></p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3"><strong>Order Items:</strong></h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?= $item->menu_item_name ?? $item->item_name ?></td>
                                            <td class="text-center"><?= $item->quantity ?></td>
                                            <td class="text-end">₱<?= number_format($item->unit_price, 2) ?></td>
                                            <td class="text-end">₱<?= number_format($item->total_price, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Subtotal:</strong>
                            <strong id="subtotalDisplay">₱<?= number_format($order->subtotal, 2) ?></strong>
                        </div>
                        
                        <?php if ($order->service_charge > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Service Charge:</strong>
                                <strong>₱<?= number_format($order->service_charge, 2) ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order->vat > 0): ?>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>VAT:</strong>
                                <strong>₱<?= number_format($order->vat, 2) ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Discount Display -->
                        <div id="discountDisplay" style="display: none;">
                            <div class="d-flex justify-content-between mb-2 text-danger">
                                <strong>Discount (<span id="discountTypeDisplay">-</span>):</strong>
                                <strong>-₱<span id="discountAmountDisplay">0.00</span></strong>
                            </div>
                        </div>
                        
                        <hr class="my-2">
                        
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0"><strong>Total Amount:</strong></h5>
                            <h5 class="mb-0 text-primary"><strong>₱<span id="finalTotalDisplay"><?= number_format($order->total_amount, 2) ?></span></strong></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <form id="paymentForm">
                        <input type="hidden" id="orderId" value="<?= $order->id ?>">
                        <input type="hidden" id="originalTotal" value="<?= $order->total_amount ?>">
                        <input type="hidden" id="totalAmount" value="<?= $order->total_amount ?>">
                        
                        <!-- Discount Section -->
                        <div class="mb-3">
                            <label class="form-label"><strong>Discount Type</strong></label>
                            <select class="form-select form-select-sm" id="discountSelect" name="discount">
                                <option value="none" selected>No Discount</option>
                                
                                <optgroup label="Statutory Discounts (20%)">
                                    <option value="senior">👴 Senior Citizen (20%)</option>
                                    <option value="pwd">♿ PWD - Persons with Disability (20%)</option>
                                </optgroup>
                                
                                <optgroup label="Promotional Discounts">
                                    <option value="general_promo">🎉 General Promo (10%)</option>
                                    <option value="time_promo">⏰ Time-Based Promo (15%)</option>
                                    <option value="event_loyalty">🎁 Event & Loyalty Promo (12%)</option>
                                    <option value="partnership">🤝 Partnership & Digital Promo (8%)</option>
                                </optgroup>
                                
                                <optgroup label="Other">
                                    <option value="voucher">🎟️ Voucher Code</option>
                                </optgroup>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <span id="discountInfo">Select discount type if applicable</span>
                            </small>
                        </div>
                        
                        <!-- Voucher Code Input -->
                        <div class="mb-3" id="voucherCodeSection" style="display: none;">
                            <label class="form-label"><strong>Voucher Code</strong></label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="voucherCode" 
                                       placeholder="Enter voucher code" autocomplete="off">
                                <button class="btn btn-primary btn-sm" type="button" id="applyVoucherBtn">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <span id="voucherStatus"></span>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Amount Received</strong></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control form-control-sm" id="amountReceived" 
                                       placeholder="0.00" step="0.01" min="0" required>
                            </div>
                            <small class="text-muted">Minimum: ₱<span id="minimumAmount"><?= number_format($order->total_amount, 2) ?></span></small>
                            
                            <div class="mt-2 d-grid gap-1" style="grid-template-columns: repeat(3, 1fr);">
                                <button type="button" class="btn btn-sm btn-outline-primary quick-amount" data-amount="20">₱20</button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-amount" data-amount="50">₱50</button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-amount" data-amount="100">₱100</button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-amount" data-amount="200">₱200</button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-amount" data-amount="500">₱500</button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-amount" data-amount="1000">₱1000</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Payment Method</strong></label>
                            <select class="form-select form-select-sm" id="paymentMethod" name="payment_method" required>
                                <option value="" selected disabled>Select a payment method</option>
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Card</option>
                                <option value="gcash">📱 GCash</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Change</strong></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control form-control-sm" id="changeAmount" 
                                       value="0.00" readonly style="background-color: #f0f0f0;">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check-circle"></i> Complete Payment
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="printReceipt()">
                                <i class="fas fa-print"></i> Print Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Success Modal -->
<div class="modal fade" id="paymentSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Payment Successful
                </h5>
            </div>
            <div class="modal-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h4 class="mb-2">Payment Completed Successfully!</h4>
                <p class="text-muted mb-4">Order #<strong><?= $order->order_number ?></strong> has been paid</p>
                
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <small class="text-muted">Total Amount</small>
                                <p class="mb-0"><strong>₱<span id="successTotalAmount"><?= number_format($order->total_amount, 2) ?></span></strong></p>
                            </div>
                            <div class="col-6 text-start">
                                <small class="text-muted">Amount Received</small>
                                <p class="mb-0"><strong>₱<span id="successAmountReceived">0.00</span></strong></p>
                            </div>
                        </div>
                        
                        <div id="successDiscountRow" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-6 text-start">
                                    <small class="text-muted">Discount Applied</small>
                                    <p class="mb-0 text-warning"><strong><span id="successDiscountType">-</span> (20%)</strong></p>
                                </div>
                                <div class="col-6 text-start">
                                    <small class="text-muted">Discount Amount</small>
                                    <p class="mb-0 text-danger"><strong>-₱<span id="successDiscountAmount">0.00</span></strong></p>
                                </div>
                            </div>
                            <hr class="my-2">
                        </div>
                        
                        <div class="row">
                            <div class="col-6 text-start">
                                <small class="text-muted">Change</small>
                                <p class="mb-0 text-success"><strong>₱<span id="successChangeAmount">0.00</span></strong></p>
                            </div>
                            <div class="col-6 text-start">
                                <small class="text-muted">Payment Method</small>
                                <p class="mb-0"><strong><span id="successPaymentMethod">-</span></strong></p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-muted small">Redirecting to POS Terminal in <span id="countdownTimer">3</span> seconds...</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-success w-100" id="backToPosBtn">
                    <i class="fas fa-arrow-left"></i> Back to POS
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    const originalTotal = parseFloat($('#originalTotal').val());
    let currentDiscount = 'none';
    let discountAmount = 0;
    let countdownInterval;
    let voucherApplied = false;
    let voucherDiscount = 0;
    
    // Discount types with their percentages
    const discountTypes = {
        'none': { label: 'No Discount', percent: 0 },
        'senior': { label: 'Senior Citizen', percent: 20 },
        'pwd': { label: 'PWD', percent: 20 },
        'general_promo': { label: 'General Promo', percent: 10 },
        'time_promo': { label: 'Time-Based Promo', percent: 15 },
        'event_loyalty': { label: 'Event & Loyalty Promo', percent: 12 },
        'partnership': { label: 'Partnership & Digital Promo', percent: 8 },
        'voucher': { label: 'Voucher Code', percent: 0 }
    };
    
    // Discount dropdown change
    $('#discountSelect').on('change', function() {
        currentDiscount = $(this).val();
        
        if (currentDiscount === 'voucher') {
            $('#voucherCodeSection').show();
            $('#voucherCode').focus();
            voucherApplied = false;
            voucherDiscount = 0;
            $('#discountDisplay').hide();
        } else {
            $('#voucherCodeSection').hide();
            voucherApplied = false;
            voucherDiscount = 0;
            applyDiscount(currentDiscount);
        }
    });
    
    // Apply Voucher Button
    $('#applyVoucherBtn').on('click', function() {
        const voucherCode = $('#voucherCode').val().trim();
        
        if (!voucherCode) {
            showVoucherStatus('Please enter a voucher code', 'warning');
            return;
        }
        
        // Simulate voucher validation (in real app, validate against backend)
        validateVoucher(voucherCode);
    });
    
    // Quick amount buttons - accumulate amounts
    $('.quick-amount').on('click', function(e) {
        e.preventDefault();
        const amount = parseFloat($(this).data('amount'));
        const currentAmount = parseFloat($('#amountReceived').val()) || 0;
        const newAmount = currentAmount + amount;
        
        $('#amountReceived').val(newAmount.toFixed(2));
        calculateChange();
    });
    
    // Manual amount input - calculate change
    $('#amountReceived').on('input', function() {
        calculateChange();
    });
    
    // Form submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const finalTotal = parseFloat($('#totalAmount').val());
        const amountReceived = parseFloat($('#amountReceived').val());
        const paymentMethod = $('#paymentMethod').val();
        const orderId = $('#orderId').val();
        
        if (!paymentMethod) {
            showAlert('Please select a payment method', 'warning');
            return;
        }
        
        if (currentDiscount === 'voucher' && !voucherApplied) {
            showAlert('Please apply a valid voucher code', 'warning');
            return;
        }
        
        if (amountReceived < finalTotal) {
            showAlert('Amount received must be at least ₱' + finalTotal.toFixed(2), 'warning');
            return;
        }
        
        // Disable submit button
        $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Simulate payment processing
        setTimeout(() => {
            const changeAmount = amountReceived - finalTotal;
            const originalTotal = parseFloat($('#originalTotal').val());
            const currentDiscountAmount = originalTotal - finalTotal;
            
            // Update modal with payment details
            $('#successTotalAmount').text(finalTotal.toFixed(2));
            $('#successAmountReceived').text(amountReceived.toFixed(2));
            $('#successChangeAmount').text(changeAmount.toFixed(2));
            $('#successPaymentMethod').text(capitalizeFirst(paymentMethod));
            
            // Show discount info if applied
            if (currentDiscount !== 'none') {
                $('#successDiscountRow').show();
                let discountTypeLabel = discountTypes[currentDiscount].label;
                
                if (currentDiscount === 'voucher') {
                    discountTypeLabel = 'Voucher Code: ' + $('#voucherCode').val();
                }
                
                $('#successDiscountType').text(discountTypeLabel);
                $('#successDiscountAmount').text(currentDiscountAmount.toFixed(2));
            } else {
                $('#successDiscountRow').hide();
            }
            
            // Show success modal
            const modal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'), {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
            
            // Start countdown
            let countdown = 3;
            $('#countdownTimer').text(countdown);
            
            countdownInterval = setInterval(() => {
                countdown--;
                $('#countdownTimer').text(countdown);
                
                if (countdown === 0) {
                    clearInterval(countdownInterval);
                    redirectToPOS();
                }
            }, 1000);
            
            // Re-enable submit button
            $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Complete Payment');
        }, 1000);
    });
    
    // Back to POS button in modal
    $('#backToPosBtn').on('click', function() {
        clearInterval(countdownInterval);
        redirectToPOS();
    });
});

function validateVoucher(code) {
    // Dummy voucher validation
    // In real app, this would call backend API
    const validVouchers = {
        'SAVE10': 10,
        'SAVE20': 20,
        'SAVE50': 50,
        'WELCOME': 15,
        'PROMO2024': 25
    };
    
    const discountAmount = validVouchers[code.toUpperCase()];
    
    if (discountAmount) {
        voucherDiscount = discountAmount;
        voucherApplied = true;
        
        const originalTotal = parseFloat($('#originalTotal').val());
        const finalTotal = originalTotal - discountAmount;
        
        $('#totalAmount').val(finalTotal.toFixed(2));
        $('#finalTotalDisplay').text(finalTotal.toFixed(2));
        $('#minimumAmount').text(finalTotal.toFixed(2));
        
        $('#discountDisplay').show();
        $('#discountTypeDisplay').text('Voucher: ' + code.toUpperCase());
        $('#discountAmountDisplay').text(discountAmount.toFixed(2));
        
        showVoucherStatus('✓ Voucher applied successfully! Discount: ₱' + discountAmount.toFixed(2), 'success');
        
        $('#amountReceived').val('');
        calculateChange();
    } else {
        voucherApplied = false;
        voucherDiscount = 0;
        showVoucherStatus('✗ Invalid voucher code', 'danger');
        $('#discountDisplay').hide();
    }
}

function applyDiscount(discountType) {
    const originalTotal = parseFloat($('#originalTotal').val());
    let finalTotal = originalTotal;
    let discountAmount = 0;
    
    if (discountType !== 'none') {
        const discountPercent = discountTypes[discountType]?.percent || 0;
        discountAmount = originalTotal * (discountPercent / 100);
        finalTotal = originalTotal - discountAmount;
        
        $('#discountDisplay').show();
        $('#discountTypeDisplay').text(discountTypes[discountType].label);
        $('#discountAmountDisplay').text(discountAmount.toFixed(2));
        $('#discountInfo').text(`${discountTypes[discountType].percent}% discount applied - Save ₱${discountAmount.toFixed(2)}`);
    } else {
        $('#discountDisplay').hide();
        $('#discountInfo').text('Select discount type if applicable');
    }
    
    // Update totals
    $('#totalAmount').val(finalTotal.toFixed(2));
    $('#finalTotalDisplay').text(finalTotal.toFixed(2));
    $('#minimumAmount').text(finalTotal.toFixed(2));
    
    // Clear and recalculate
    $('#amountReceived').val('');
    calculateChange();
}

function calculateChange() {
    const totalAmount = parseFloat($('#totalAmount').val());
    const received = parseFloat($('#amountReceived').val()) || 0;
    const change = received - totalAmount;
    $('#changeAmount').val(change.toFixed(2));
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function showAlert(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'warning' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#paymentForm').prepend(alertHtml);

    setTimeout(() => {
        $('.alert').fadeOut('slow', function() { $(this).remove(); });
    }, 4000);
}

function showVoucherStatus(message, type = 'info') {
    const statusClass = type === 'success' ? 'text-success' : 
                       type === 'warning' ? 'text-warning' : 'text-danger';
    
    $('#voucherStatus').html(`<span class="${statusClass}">${message}</span>`);
    
    if (type === 'danger' || type === 'warning') {
        setTimeout(() => {
            $('#voucherStatus').html('');
        }, 4000);
    }
}

function printReceipt() {
    const orderId = $('#orderId').val();
    window.open('<?= base_url("restaurant/{$tenant->tenant_slug}/print-receipt") ?>/' + orderId, '_blank');
}

function redirectToPOS() {
    window.location.href = '<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>';
}
</script>
<?= $this->endSection() ?>