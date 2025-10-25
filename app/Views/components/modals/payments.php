<?php
// =====================================
// app/Views/components/modals/payment.php
// =====================================
?>
<!-- Payment Processing Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-credit-card"></i> Process Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Order Summary -->
                    <div class="col-md-6">
                        <h6>Order Summary</h6>
                        <div class="order-summary-card">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="payment-subtotal">₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Service Charge (10%):</span>
                                <span id="payment-service-charge">₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>VAT (12%):</span>
                                <span id="payment-vat">₱0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between total-amount">
                                <strong>Total Amount:</strong>
                                <strong id="payment-total">₱0.00</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="col-md-6">
                        <h6>Select Payment Method</h6>
                        <div class="payment-methods">
                            <div class="payment-method" data-method="cash">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Cash Payment</span>
                            </div>
                            <div class="payment-method" data-method="card">
                                <i class="fas fa-credit-card"></i>
                                <span>Credit/Debit Card</span>
                            </div>
                            <div class="payment-method" data-method="gcash">
                                <i class="fas fa-mobile-alt"></i>
                                <span>GCash</span>
                            </div>
                            <div class="payment-method" data-method="maya">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Maya</span>
                            </div>
                        </div>
                        
                        <!-- Cash Payment Details -->
                        <div class="payment-details" id="cash-details" style="display: none;">
                            <div class="form-group mt-3">
                                <label>Amount Received:</label>
                                <input type="number" class="form-control" id="cash-received" step="0.01" min="0">
                            </div>
                            <div class="change-calculation">
                                <div class="d-flex justify-content-between">
                                    <strong>Change:</strong>
                                    <strong id="change-amount">₱0.00</strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Digital Payment Details -->
                        <div class="payment-details" id="digital-details" style="display: none;">
                            <div class="qr-code-section text-center mt-3">
                                <div id="qr-code-container">
                                    <!-- QR Code will be generated here -->
                                </div>
                                <p class="text-muted">Scan QR code with your mobile app</p>
                                <div class="payment-status">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>Waiting for payment...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Payment Details -->
                        <div class="payment-details" id="card-details" style="display: none;">
                            <div class="card-terminal mt-3">
                                <div class="terminal-display text-center">
                                    <i class="fas fa-credit-card fa-3x mb-3"></i>
                                    <p>Insert or swipe card</p>
                                    <div class="terminal-status">
                                        <span class="badge bg-warning">Ready</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Information -->
                <div class="customer-section mt-3">
                    <h6>Customer Information (Optional)</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="customer-name-input" placeholder="Customer Name">
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="customer-email" placeholder="Email (for receipt)">
                        </div>
                    </div>
                </div>
                
                <!-- Receipt Options -->
                <div class="receipt-options mt-3">
                    <h6>Receipt Options</h6>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="print-receipt" checked>
                        <label class="form-check-label" for="print-receipt">Print Receipt</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="email-receipt">
                        <label class="form-check-label" for="email-receipt">Email Receipt</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="sms-receipt">
                        <label class="form-check-label" for="sms-receipt">SMS Receipt</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="process-payment-btn" onclick="processPayment()">
                    <i class="fas fa-check"></i> Complete Payment
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.order-summary-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.total-amount {
    font-size: 1.2rem;
    color: #28a745;
}

.payment-methods {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.payment-method {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method:hover {
    background: #e9ecef;
    border-color: #007bff;
}

.payment-method.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.payment-method i {
    font-size: 1.5rem;
    margin-bottom: 8px;
    display: block;
}

.change-calculation {
    background: #d4edda;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}

.qr-code-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 2px dashed #ddd;
}

.terminal-display {
    background: #2c3e50;
    color: white;
    padding: 30px;
    border-radius: 8px;
}

.payment-status {
    margin-top: 15px;
    padding: 10px;
    background: #fff3cd;
    border-radius: 5px;
}

.customer-section, .receipt-options {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}
</style>