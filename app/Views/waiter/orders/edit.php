<?php
// =============================================================================
// WAITER/ORDERS/EDIT.PHP - Edit Order
// =============================================================================

// File: app/Views/waiter/orders/edit.php
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Order #<?= esc($order['order_number']) ?> - <?= esc($tenant_config['restaurant_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Editing Order</h5>
                <p>You are editing Order <strong>#<?= esc($order['order_number']) ?></strong> for Table <?= $order['table_number'] ?></p>
                <p><strong>Warning:</strong> Changes will be sent to the kitchen immediately upon saving.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Menu Section -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">🍽️ Add Items to Order</h4>
                </div>
                
                <div class="card-body">
                    <!-- Menu Categories -->
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-category="all">All</button>
                        <?php if (isset($menu_categories)): ?>
                            <?php foreach ($menu_categories as $category): ?>
                            <button type="button" class="btn btn-outline-primary" data-category="<?= esc($category['slug']) ?>">
                                <?= esc($category['name']) ?>
                            </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Menu Items -->
                    <div class="row" id="menu_items_container">
                        <?php if (isset($menu_items)): ?>
                            <?php foreach ($menu_items as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-3 menu-item" data-category="<?= esc($item['category_slug']) ?>">
                                <div class="card menu-item-card h-100 <?= !$item['available'] ? 'unavailable' : '' ?>">
                                    <img src="<?= $item['image'] ?: base_url('assets/images/no-image.png') ?>" 
                                         class="card-img-top" alt="<?= esc($item['name']) ?>" style="height: 120px; object-fit: cover;">
                                    
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title"><?= esc($item['name']) ?></h6>
                                        <p class="card-text text-muted small flex-grow-1"><?= esc($item['description']) ?></p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="text-primary">₱<?= number_format($item['price'], 2) ?></strong>
                                            
                                            <?php if ($item['available']): ?>
                                                <button class="btn btn-sm btn-outline-primary add-item" 
                                                        data-item-id="<?= $item['id'] ?>"
                                                        data-item-name="<?= esc($item['name']) ?>"
                                                        data-item-price="<?= $item['price'] ?>">
                                                    Add <i class="fas fa-plus"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Not Available</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Edit Panel -->
        <div class="col-md-4">
            <div class="card sticky-top">
                <div class="card-header">
                    <h4 class="card-title">✏️ Edit Order #<?= esc($order['order_number']) ?></h4>
                </div>
                
                <div class="card-body">
                    <!-- Order Info -->
                    <div class="order-info mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Table:</span>
                            <span class="badge badge-secondary">T<?= $order['table_number'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Customer:</span>
                            <span><?= esc($order['customer_name'] ?: 'Walk-in') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Status:</span>
                            <span class="badge badge-<?= getOrderStatusColor($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Created:</span>
                            <span><?= date('H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                    </div>

                    <hr>

                    <!-- Current Order Items -->
                    <h6>Current Items:</h6>
                    <div id="current_items_container">
                        <?php if (isset($order_items)): ?>
                            <?php foreach ($order_items as $item): ?>
                            <div class="order-item mb-2" data-item-id="<?= $item['id'] ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= esc($item['menu_item_name']) ?></strong>
                                        <br>
                                        <small>₱<?= number_format($item['unit_price'], 2) ?> each</small>
                                        <?php if ($item['special_instructions']): ?>
                                            <br><small class="text-info"><?= esc($item['special_instructions']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="quantity-controls mr-2">
                                            <button class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(<?= $item['id'] ?>, -1)">-</button>
                                            <span class="mx-2" id="qty_<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(<?= $item['id'] ?>, 1)">+</button>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(<?= $item['id'] ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <strong>₱<span id="total_<?= $item['id'] ?>"><?= number_format($item['total_price'], 2) ?></span></strong>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <!-- New Items to Add -->
                    <h6>New Items to Add:</h6>
                    <div id="new_items_container">
                        <p class="text-muted">No new items selected</p>
                    </div>

                    <hr>

                    <!-- Order Summary -->
                    <div id="order_summary">
                        <div class="d-flex justify-content-between">
                            <span>Current Subtotal:</span>
                            <span id="current_subtotal">₱<?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>New Items:</span>
                            <span id="new_items_total">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Service Charge:</span>
                            <span id="service_charge">₱<?= number_format($order['service_charge'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>VAT:</span>
                            <span id="vat_amount">₱<?= number_format($order['vat_amount'], 2) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>New Total:</span>
                            <span id="new_total">₱<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>

                    <hr>

                    <!-- Special Instructions -->
                    <div class="form-group">
                        <label for="additional_notes">Additional Notes</label>
                        <textarea id="additional_notes" class="form-control" rows="3" 
                                  placeholder="Any additional instructions for the kitchen"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-group-vertical w-100">
                        <button id="save_changes_btn" class="btn btn-primary" onclick="saveChanges()">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button id="send_to_kitchen_btn" class="btn btn-success" onclick="sendChangesToKitchen()" disabled>
                            <i class="fas fa-paper-plane"></i> Send Changes to Kitchen
                        </button>
                        <button class="btn btn-secondary" onclick="goBack()">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-item {
    background: #f8f9fa;
    border-radius: 5px;
    padding: 10px;
    border-left: 3px solid #007bff;
}

.quantity-controls button {
    width: 25px;
    height: 25px;
    padding: 0;
    line-height: 1;
}

.menu-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.menu-item-card.unavailable {
    opacity: 0.6;
    background: #f8f9fa;
}

.sticky-top {
    top: 20px;
}

@media (max-width: 768px) {
    .sticky-top {
        position: relative !important;
        top: auto !important;
    }
}
</style>

<script>
// Edit Order JavaScript
let newItems = [];
let originalOrderData = <?= json_encode($order) ?>;
let orderItems = <?= json_encode($order_items) ?>;

const TAX_RATE = <?= $tenant_config['tax_rate'] ?>;
const SERVICE_CHARGE_RATE = <?= $tenant_config['service_charge'] ?>;

$(document).ready(function() {
    // Menu category filters
    $('.btn-group button[data-category]').click(function() {
        const category = $(this).data('category');
        $(this).addClass('active').siblings().removeClass('active');
        filterMenuItems(category);
    });

    // Add item buttons
    $('.add-item').click(function() {
        const itemData = {
            id: $(this).data('item-id'),
            name: $(this).data('item-name'),
            price: parseFloat($(this).data('item-price')),
            quantity: 1
        };
        addNewItem(itemData);
    });

    updateOrderSummary();
});

function filterMenuItems(category) {
    $('.menu-item').each(function() {
        const itemCategory = $(this).data('category');
        const show = category === 'all' || itemCategory === category;
        $(this).toggle(show);
    });
}

function addNewItem(itemData) {
    const existingIndex = newItems.findIndex(item => item.id === itemData.id);
    
    if (existingIndex >= 0) {
        newItems[existingIndex].quantity++;
    } else {
        newItems.push(itemData);
    }
    
    updateNewItemsDisplay();
    updateOrderSummary();
    
    // Enable send to kitchen button
    $('#send_to_kitchen_btn').prop('disabled', false);
}

function removeNewItem(index) {
    newItems.splice(index, 1);
    updateNewItemsDisplay();
    updateOrderSummary();
    
    if (newItems.length === 0) {
        $('#send_to_kitchen_btn').prop('disabled', true);
    }
}

function changeNewItemQuantity(index, change) {
    newItems[index].quantity += change;
    if (newItems[index].quantity <= 0) {
        removeNewItem(index);
    } else {
        updateNewItemsDisplay();
        updateOrderSummary();
    }
}

function updateNewItemsDisplay() {
    const container = $('#new_items_container');
    
    if (newItems.length === 0) {
        container.html('<p class="text-muted">No new items selected</p>');
    } else {
        let html = '';
        newItems.forEach((item, index) => {
            html += `
                <div class="order-item mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.name}</strong>
                            <br><small>₱${item.price.toFixed(2)} each</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="quantity-controls mr-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick="changeNewItemQuantity(${index}, -1)">-</button>
                                <span class="mx-2">${item.quantity}</span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="changeNewItemQuantity(${index}, 1)">+</button>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeNewItem(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-right">
                        <strong>₱${(item.price * item.quantity).toFixed(2)}</strong>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }
}

function changeQuantity(itemId, change) {
    const item = orderItems.find(item => item.id === itemId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            if (confirm('Remove this item from the order?')) {
                removeItem(itemId);
            } else {
                item.quantity -= change; // Revert change
            }
        } else {
            item.total_price = item.quantity * item.unit_price;
            $('#qty_' + itemId).text(item.quantity);
            $('#total_' + itemId).text(item.total_price.toFixed(2));
            updateOrderSummary();
        }
    }
}

function removeItem(itemId) {
    if (confirm('Remove this item from the order?')) {
        const index = orderItems.findIndex(item => item.id === itemId);
        if (index >= 0) {
            orderItems.splice(index, 1);
            $(`.order-item[data-item-id="${itemId}"]`).remove();
            updateOrderSummary();
        }
    }
}

function updateOrderSummary() {
    // Calculate current items total
    const currentSubtotal = orderItems.reduce((sum, item) => sum + item.total_price, 0);
    
    // Calculate new items total
    const newItemsTotal = newItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Calculate totals
    const totalSubtotal = currentSubtotal + newItemsTotal;
    const serviceCharge = totalSubtotal * SERVICE_CHARGE_RATE;
    const vatAmount = (totalSubtotal + serviceCharge) * TAX_RATE;
    const grandTotal = totalSubtotal + serviceCharge + vatAmount;
    
    // Update display
    $('#current_subtotal').text('₱' + currentSubtotal.toFixed(2));
    $('#new_items_total').text('₱' + newItemsTotal.toFixed(2));
    $('#service_charge').text('₱' + serviceCharge.toFixed(2));
    $('#vat_amount').text('₱' + vatAmount.toFixed(2));
    $('#new_total').text('₱' + grandTotal.toFixed(2));
}

function saveChanges() {
    const changes = {
        order_id: <?= $order['id'] ?>,
        modified_items: orderItems,
        new_items: newItems,
        additional_notes: $('#additional_notes').val()
    };
    
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/save-changes") ?>',
        method: 'POST',
        data: JSON.stringify(changes),
        contentType: 'application/json',
        dataType: 'json',
        beforeSend: function() {
            $('#save_changes_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        },
        success: function(response) {
            if (response.success) {
                showNotification('Changes saved successfully!', 'success');
                $('#send_to_kitchen_btn').prop('disabled', false);
            } else {
                showNotification('Error saving changes: ' + response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error saving changes. Please try again.', 'error');
        },
        complete: function() {
            $('#save_changes_btn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
        }
    });
}

function sendChangesToKitchen() {
    if (confirm('Send changes to kitchen? This will notify the kitchen staff of the order modifications.')) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/send-changes-to-kitchen") ?>',
            method: 'POST',
            data: {
                order_id: <?= $order['id'] ?>,
                additional_notes: $('#additional_notes').val()
            },
            dataType: 'json',
            beforeSend: function() {
                $('#send_to_kitchen_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Changes sent to kitchen successfully!', 'success');
                    setTimeout(() => {
                        goBack();
                    }, 2000);
                } else {
                    showNotification('Error sending changes: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error sending changes. Please try again.', 'error');
            },
            complete: function() {
                $('#send_to_kitchen_btn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Changes to Kitchen');
            }
        });
    }
}

function goBack() {
    window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/orders") ?>';
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
</script>
<?= $this->endSection() ?>