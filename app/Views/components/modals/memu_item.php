<?php
// =====================================
// app/Views/components/modals/menu_item.php
// =====================================
?>
<!-- Menu Item Details Modal -->
<div class="modal fade" id="menuItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-utensils"></i> Menu Item Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="menu-item-image">
                            <img id="menu-item-img" src="" alt="Menu Item" class="img-fluid rounded">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 id="menu-item-name"></h4>
                        <p class="text-muted" id="menu-item-category"></p>
                        <p id="menu-item-description"></p>
                        
                        <div class="price-section">
                            <h3 class="text-primary" id="menu-item-price"></h3>
                        </div>
                        
                        <div class="availability-section mb-3">
                            <span class="badge" id="availability-badge"></span>
                        </div>
                        
                        <!-- Customization Options -->
                        <div class="customization-section">
                            <h6>Customization Options</h6>
                            
                            <!-- Size Options -->
                            <div class="option-group mb-3" id="size-options" style="display: none;">
                                <label class="form-label">Size:</label>
                                <div class="btn-group" role="group">
                                    <!-- Size buttons will be added dynamically -->
                                </div>
                            </div>
                            
                            <!-- Add-ons -->
                            <div class="option-group mb-3" id="addon-options" style="display: none;">
                                <label class="form-label">Add-ons:</label>
                                <div class="addon-list">
                                    <!-- Add-on checkboxes will be added dynamically -->
                                </div>
                            </div>
                            
                            <!-- Special Instructions -->
                            <div class="form-group mb-3">
                                <label for="special-instructions">Special Instructions:</label>
                                <textarea class="form-control" id="special-instructions" rows="3" placeholder="Any special requests or modifications..."></textarea>
                            </div>
                            
                            <!-- Quantity -->
                            <div class="quantity-section">
                                <label class="form-label">Quantity:</label>
                                <div class="input-group quantity-input">
                                    <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">-</button>
                                    <input type="number" class="form-control text-center" id="item-quantity" value="1" min="1">
                                    <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">+</button>
                                </div>
                            </div>
                            
                            <!-- Total Price -->
                            <div class="total-price-section mt-3">
                                <h5>Total: <span class="text-success" id="item-total-price">₱0.00</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Nutritional Information -->
                <div class="nutritional-info mt-3">
                    <h6>Nutritional Information</h6>
                    <div class="row" id="nutrition-facts">
                        <!-- Nutrition facts will be loaded here -->
                    </div>
                </div>
                
                <!-- Allergen Information -->
                <div class="allergen-info mt-3">
                    <h6>Allergen Information</h6>
                    <div id="allergen-list">
                        <!-- Allergen tags will be added here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addToOrder()">
                    <i class="fas fa-cart-plus"></i> Add to Order
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.menu-item-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.price-section h3 {
    font-weight: bold;
    margin: 15px 0;
}

.option-group .btn-group {
    width: 100%;
}

.addon-list {
    max-height: 150px;
    overflow-y: auto;
}

.addon-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.quantity-input {
    width: 120px;
}

.total-price-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.nutritional-info .nutrition-item {
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    margin-bottom: 10px;
}

.allergen-tag {
    display: inline-block;
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    margin: 2px;
}
</style>

<script>
// Modal JavaScript functions
function showOrderDetails(orderId) {
    // Load order details via AJAX
    $.get(`<?= base_url('api/orders/') ?>${orderId}`, function(data) {
        // Populate modal with order data
        $('#order-number').text(data.order_number);
        $('#order-datetime').text(data.created_at);
        $('#order-status').html(`<span class="badge bg-${data.status_color}">${data.status}</span>`);
        // ... populate other fields
        $('#orderDetailsModal').modal('show');
    });
}

function showPaymentModal(orderData) {
    // Populate payment modal with order data
    $('#payment-subtotal').text(`₱${orderData.subtotal}`);
    $('#payment-service-charge').text(`₱${orderData.service_charge}`);
    $('#payment-vat').text(`₱${orderData.vat}`);
    $('#payment-total').text(`₱${orderData.total}`);
    
    $('#paymentModal').modal('show');
}

function processPayment() {
    const selectedMethod = $('.payment-method.active').data('method');
    // Process payment based on selected method
    // Implementation depends on payment method
}

function saveCustomerInfo() {
    const customerData = {
        name: $('#customer-full-name').val(),
        phone: $('#customer-phone').val(),
        email: $('#customer-email-modal').val(),
        birthday: $('#customer-birthday').val(),
        address: $('#customer-address').val(),
        loyalty: $('#join-loyalty').is(':checked'),
        newsletter: $('#newsletter-subscribe').is(':checked')
    };
    
    $.post('<?= base_url('api/customers') ?>', customerData, function(response) {
        if (response.success) {
            $('#customerInfoModal').modal('hide');
            alert('Customer information saved successfully');
        }
    });
}

// Payment method selection
$(document).on('click', '.payment-method', function() {
    $('.payment-method').removeClass('active');
    $(this).addClass('active');
    
    const method = $(this).data('method');
    $('.payment-details').hide();
    
    if (method === 'cash') {
        $('#cash-details').show();
    } else if (method === 'gcash' || method === 'maya') {
        $('#digital-details').show();
        generateQRCode(method);
    } else if (method === 'card') {
        $('#card-details').show();
    }
});

// Calculate change for cash payments
$(document).on('input', '#cash-received', function() {
    const received = parseFloat($(this).val()) || 0;
    const total = parseFloat($('#payment-total').text().replace('₱', '')) || 0;
    const change = received - total;
    
    $('#change-amount').text(`₱${change.toFixed(2)}`);
    
    if (change >= 0) {
        $('#change-amount').removeClass('text-danger').addClass('text-success');
        $('#process-payment-btn').prop('disabled', false);
    } else {
        $('#change-amount').removeClass('text-success').addClass('text-danger');
        $('#process-payment-btn').prop('disabled', true);
    }
});

function generateQRCode(method) {
    // Generate QR code for digital payments
    const amount = $('#payment-total').text().replace('₱', '');
    
    $.post('<?= base_url('api/payment/qr') ?>', {
        method: method,
        amount: amount,
        order_id: currentOrderId
    }, function(response) {
        if (response.success) {
            $('#qr-code-container').html(`<img src="${response.qr_code}" alt="QR Code" class="img-fluid">`);
            startPaymentPolling(response.transaction_id);
        }
    });
}

function startPaymentPolling(transactionId) {
    const pollInterval = setInterval(function() {
        $.get(`<?= base_url('api/payment/status/') ?>${transactionId}`, function(response) {
            if (response.status === 'completed') {
                clearInterval(pollInterval);
                $('.payment-status').html('<i class="fas fa-check-circle text-success"></i> Payment received!');
                setTimeout(function() {
                    $('#paymentModal').modal('hide');
                    location.reload();
                }, 2000);
            }
        });
    }, 3000);
    
    // Stop polling after 5 minutes
    setTimeout(function() {
        clearInterval(pollInterval);
    }, 300000);
}
</script>