<?php
// =============================================================================
// WAITER/ORDERS/ - Order Taking Views
// =============================================================================

// File: app/Views/waiter/orders/new.php
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>New Order - <?= esc($tenant_config['restaurant_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <!-- Menu Section -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">🍽️ Menu</h4>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" data-category="all">All</button>
                            <?php if (isset($menu_categories)): ?>
                                <?php foreach ($menu_categories as $category): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-category="<?= esc($category['slug']) ?>">
                                    <?= esc($category['name']) ?>
                                </button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Search Bar -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="menu_search" class="form-control" placeholder="Search menu items...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="show_available_only" class="form-check-input" checked>
                                <label for="show_available_only" class="form-check-label">Available only</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="show_specials" class="form-check-input">
                                <label for="show_specials" class="form-check-label">Today's specials</label>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items Grid -->
                    <div class="row" id="menu_items_container">
                        <?php if (isset($menu_items) && !empty($menu_items)): ?>
                            <?php foreach ($menu_items as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-3 menu-item" data-category="<?= esc($item['category_slug']) ?>" data-item-id="<?= $item['id'] ?>">
                                <div class="card menu-item-card h-100 <?= !$item['available'] ? 'unavailable' : '' ?>">
                                    <div class="card-img-top-wrapper">
                                        <img src="<?= $item['image'] ?: base_url('assets/images/no-image.png') ?>" 
                                             class="card-img-top" 
                                             alt="<?= esc($item['name']) ?>"
                                             style="height: 150px; object-fit: cover;">
                                        
                                        <?php if ($item['is_special']): ?>
                                            <span class="badge badge-warning special-badge">Today's Special</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!$item['available']): ?>
                                            <div class="unavailable-overlay">
                                                <span class="badge badge-danger">Not Available</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title"><?= esc($item['name']) ?></h6>
                                        <p class="card-text text-muted small flex-grow-1"><?= esc($item['description']) ?></p>
                                        
                                        <div class="menu-item-footer">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="text-primary">₱<?= number_format($item['price'], 2) ?></strong>
                                                
                                                <?php if ($item['available']): ?>
                                                    <button class="btn btn-sm btn-outline-primary add-to-order" 
                                                            data-item-id="<?= $item['id'] ?>"
                                                            data-item-name="<?= esc($item['name']) ?>"
                                                            data-item-price="<?= $item['price'] ?>"
                                                            data-item-category="<?= esc($item['category_name']) ?>">
                                                        Add <i class="fas fa-plus"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($item['allergens'])): ?>
                                                <div class="mt-2">
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-triangle"></i> 
                                                        Contains: <?= esc(implode(', ', $item['allergens'])) ?>
                                                    </small>
                                                </div>
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

        <!-- Order Panel -->
        <div class="col-md-4">
            <div class="card sticky-top">
                <div class="card-header">
                    <h4 class="card-title">🛒 Current Order</h4>
                    <div class="card-tools">
                        <span id="table-info" class="badge badge-info">Select Table</span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Table Selection -->
                    <div class="form-group">
                        <label for="table_select">Table</label>
                        <select id="table_select" class="form-control" required>
                            <option value="">Select a table</option>
                            <?php if (isset($available_tables)): ?>
                                <?php foreach ($available_tables as $table): ?>
                                <option value="<?= $table['id'] ?>" data-capacity="<?= $table['capacity'] ?>">
                                    Table <?= $table['number'] ?> (<?= $table['capacity'] ?> seats)
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Customer Information -->
                    <div class="form-group">
                        <label for="customer_name">Customer Name (Optional)</label>
                        <input type="text" id="customer_name" class="form-control" placeholder="Enter customer name">
                    </div>

                    <div class="form-group">
                        <label for="party_size">Party Size</label>
                        <select id="party_size" class="form-control" required>
                            <option value="">Select party size</option>
                            <option value="1">1 Person</option>
                            <option value="2">2 People</option>
                            <option value="3">3 People</option>
                            <option value="4">4 People</option>
                            <option value="5">5 People</option>
                            <option value="6">6 People</option>
                            <option value="7">7 People</option>
                            <option value="8">8+ People</option>
                        </select>
                    </div>

                    <hr>

                    <!-- Order Items -->
                    <div id="order_items_container">
                        <p class="text-muted text-center">No items added yet</p>
                    </div>

                    <hr>

                    <!-- Order Summary -->
                    <div id="order_summary">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Service Charge (<?= $tenant_config['service_charge'] * 100 ?>%):</span>
                            <span id="service_charge">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>VAT (<?= $tenant_config['tax_rate'] * 100 ?>%):</span>
                            <span id="vat_amount">₱0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>Total:</span>
                            <span id="total_amount">₱0.00</span>
                        </div>
                    </div>

                    <hr>

                    <!-- Order Notes -->
                    <div class="form-group">
                        <label for="order_notes">Special Instructions</label>
                        <textarea id="order_notes" class="form-control" rows="3" placeholder="Any special requests or dietary restrictions"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-group-vertical w-100">
                        <button id="save_order_btn" class="btn btn-success" disabled>
                            <i class="fas fa-save"></i> Save Order
                        </button>
                        <button id="send_to_kitchen_btn" class="btn btn-primary" disabled>
                            <i class="fas fa-paper-plane"></i> Send to Kitchen
                        </button>
                        <button id="clear_order_btn" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i> Clear Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Customization Modal -->
<div class="modal fade" id="itemCustomizationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customization_item_name">Customize Item</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="itemCustomizationForm">
                    <input type="hidden" id="customization_item_id">
                    
                    <div class="form-group">
                        <label for="item_quantity">Quantity</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(-1)">-</button>
                            </div>
                            <input type="number" id="item_quantity" class="form-control text-center" value="1" min="1" max="20">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cooking_preference">Cooking Preference</label>
                        <select id="cooking_preference" class="form-control">
                            <option value="">Default</option>
                            <option value="rare">Rare</option>
                            <option value="medium_rare">Medium Rare</option>
                            <option value="medium">Medium</option>
                            <option value="medium_well">Medium Well</option>
                            <option value="well_done">Well Done</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="spice_level">Spice Level</label>
                        <select id="spice_level" class="form-control">
                            <option value="">Default</option>
                            <option value="mild">Mild</option>
                            <option value="medium">Medium</option>
                            <option value="spicy">Spicy</option>
                            <option value="extra_spicy">Extra Spicy</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_instructions">Special Instructions</label>
                        <textarea id="special_instructions" class="form-control" rows="3" placeholder="Any special requests for this item"></textarea>
                    </div>
                    
                    <!-- Add-ons/Extras -->
                    <div class="form-group">
                        <label>Add-ons</label>
                        <div id="item_addons">
                            <!-- Add-ons will be loaded dynamically -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmItemCustomization()">
                    Add to Order
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.menu-item-card {
    transition: transform 0.2s ease;
    cursor: pointer;
}

.menu-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.menu-item-card.unavailable {
    opacity: 0.6;
}

.card-img-top-wrapper {
    position: relative;
    overflow: hidden;
}

.special-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
}

.unavailable-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

.order-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    border-left: 4px solid #007bff;
}

.order-item-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 5px;
}

.order-item-details {
    font-size: 0.9em;
    color: #6c757d;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-controls button {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    border: 1px solid #ddd;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
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
// Order Management JavaScript
let currentOrder = {
    items: [],
    table_id: null,
    customer_name: '',
    party_size: 0,
    notes: ''
};

const TAX_RATE = <?= $tenant_config['tax_rate'] ?>;
const SERVICE_CHARGE_RATE = <?= $tenant_config['service_charge'] ?>;

$(document).ready(function() {
    // Menu category filters
    $('.btn-group button[data-category]').click(function() {
        const category = $(this).data('category');
        $(this).addClass('active').siblings().removeClass('active');
        filterMenuItems(category);
    });

    // Menu search
    $('#menu_search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterMenuItems(null, searchTerm);
    });

    // Add to order buttons
    $('.add-to-order').click(function() {
        const itemData = {
            id: $(this).data('item-id'),
            name: $(this).data('item-name'),
            price: parseFloat($(this).data('item-price')),
            category: $(this).data('item-category')
        };
        
        // Show customization modal for complex items
        if (shouldShowCustomization(itemData.id)) {
            showItemCustomization(itemData);
        } else {
            addItemToOrder(itemData);
        }
    });

    // Table selection
    $('#table_select').change(function() {
        currentOrder.table_id = $(this).val();
        updateTableInfo();
        validateOrder();
    });

    // Party size
    $('#party_size').change(function() {
        currentOrder.party_size = parseInt($(this).val());
        validateOrder();
    });

    // Customer name
    $('#customer_name').on('input', function() {
        currentOrder.customer_name = $(this).val();
    });

    // Order notes
    $('#order_notes').on('input', function() {
        currentOrder.notes = $(this).val();
    });

    // Action buttons
    $('#save_order_btn').click(saveOrder);
    $('#send_to_kitchen_btn').click(sendToKitchen);
    $('#clear_order_btn').click(clearOrder);

    // Initialize
    validateOrder();
});

function filterMenuItems(category = null, searchTerm = '') {
    $('.menu-item').each(function() {
        const $item = $(this);
        const itemCategory = $item.data('category');
        const itemName = $item.find('.card-title').text().toLowerCase();
        const itemDescription = $item.find('.card-text').text().toLowerCase();
        
        let showCategory = !category || category === 'all' || itemCategory === category;
        let showSearch = !searchTerm || itemName.includes(searchTerm) || itemDescription.includes(searchTerm);
        
        $item.toggle(showCategory && showSearch);
    });
}

function shouldShowCustomization(itemId) {
    // Check if item has customization options (addons, cooking preferences, etc.)
    // This would be determined by item configuration
    return false; // For now, simplified
}

function showItemCustomization(itemData) {
    $('#customization_item_id').val(itemData.id);
    $('#customization_item_name').text(itemData.name);
    $('#item_quantity').val(1);
    
    // Load item-specific addons/options
    loadItemAddons(itemData.id);
    
    $('#itemCustomizationModal').modal('show');
}

function loadItemAddons(itemId) {
    // Load addons for specific item
    $('#item_addons').html('<p class="text-muted">No addons available</p>');
}

function confirmItemCustomization() {
    const itemData = {
        id: $('#customization_item_id').val(),
        name: $('#customization_item_name').text(),
        quantity: parseInt($('#item_quantity').val()),
        cooking_preference: $('#cooking_preference').val(),
        spice_level: $('#spice_level').val(),
        special_instructions: $('#special_instructions').val(),
        // Add addon data here
    };
    
    addItemToOrder(itemData);
    $('#itemCustomizationModal').modal('hide');
}

function addItemToOrder(itemData) {
    const quantity = itemData.quantity || 1;
    const existingItemIndex = currentOrder.items.findIndex(item => 
        item.id === itemData.id && 
        JSON.stringify(item.customizations || {}) === JSON.stringify(itemData.customizations || {})
    );
    
    if (existingItemIndex >= 0) {
        currentOrder.items[existingItemIndex].quantity += quantity;
    } else {
        currentOrder.items.push({
            id: itemData.id,
            name: itemData.name,
            price: itemData.price,
            quantity: quantity,
            customizations: {
                cooking_preference: itemData.cooking_preference,
                spice_level: itemData.spice_level,
                special_instructions: itemData.special_instructions
            }
        });
    }
    
    updateOrderDisplay();
    validateOrder();
    
    // Show feedback
    showNotification(`${itemData.name} added to order!`, 'success');
}

function updateOrderDisplay() {
    const container = $('#order_items_container');
    
    if (currentOrder.items.length === 0) {
        container.html('<p class="text-muted text-center">No items added yet</p>');
    } else {
        let html = '';
        currentOrder.items.forEach((item, index) => {
            html += `
                <div class="order-item">
                    <div class="order-item-header">
                        <strong>${item.name}</strong>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeOrderItem(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="order-item-details">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="quantity-controls">
                                <button onclick="changeOrderItemQuantity(${index}, -1)">-</button>
                                <span class="mx-2">${item.quantity}</span>
                                <button onclick="changeOrderItemQuantity(${index}, 1)">+</button>
                            </div>
                            <span class="font-weight-bold">₱${(item.price * item.quantity).toFixed(2)}</span>
                        </div>
                        ${item.customizations && Object.values(item.customizations).some(v => v) ? 
                            `<small class="text-info">Customized</small>` : ''
                        }
                    </div>
                </div>
            `;
        });
        container.html(html);
    }
    
    updateOrderSummary();
}

function changeOrderItemQuantity(index, change) {
    currentOrder.items[index].quantity += change;
    if (currentOrder.items[index].quantity <= 0) {
        currentOrder.items.splice(index, 1);
    }
    updateOrderDisplay();
}

function removeOrderItem(index) {
    currentOrder.items.splice(index, 1);
    updateOrderDisplay();
    validateOrder();
}

function updateOrderSummary() {
    const subtotal = currentOrder.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const serviceCharge = subtotal * SERVICE_CHARGE_RATE;
    const vatAmount = (subtotal + serviceCharge) * TAX_RATE;
    const total = subtotal + serviceCharge + vatAmount;
    
    $('#subtotal').text('₱' + subtotal.toFixed(2));
    $('#service_charge').text('₱' + serviceCharge.toFixed(2));
    $('#vat_amount').text('₱' + vatAmount.toFixed(2));
    $('#total_amount').text('₱' + total.toFixed(2));
}

function updateTableInfo() {
    const selectedOption = $('#table_select option:selected');
    if (selectedOption.val()) {
        const tableNumber = selectedOption.text().split(' ')[1]; // Extract table number
        $('#table-info').text(`Table ${tableNumber}`);
    } else {
        $('#table-info').text('Select Table');
    }
}

function validateOrder() {
    const hasTable = currentOrder.table_id !== null && currentOrder.table_id !== '';
    const hasPartySize = currentOrder.party_size > 0;
    const hasItems = currentOrder.items.length > 0;
    
    const isValid = hasTable && hasPartySize && hasItems;
    
    $('#save_order_btn, #send_to_kitchen_btn').prop('disabled', !isValid);
}

function saveOrder() {
    const orderData = {
        ...currentOrder,
        status: 'draft',
        customer_name: $('#customer_name').val(),
        notes: $('#order_notes').val()
    };
    
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/save") ?>',
        method: 'POST',
        data: JSON.stringify(orderData),
        contentType: 'application/json',
        dataType: 'json',
        beforeSend: function() {
            $('#save_order_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        },
        success: function(response) {
            if (response.success) {
                showNotification('Order saved successfully!', 'success');
                // Optionally redirect to orders list
            } else {
                showNotification('Error saving order: ' + response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error saving order. Please try again.', 'error');
        },
        complete: function() {
            $('#save_order_btn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Order');
        }
    });
}

function sendToKitchen() {
    if (confirm('Send this order to the kitchen? This action cannot be undone.')) {
        const orderData = {
            ...currentOrder,
            status: 'confirmed',
            customer_name: $('#customer_name').val(),
            notes: $('#order_notes').val()
        };
        
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_id}/waiter/orders/submit") ?>',
            method: 'POST',
            data: JSON.stringify(orderData),
            contentType: 'application/json',
            dataType: 'json',
            beforeSend: function() {
                $('#send_to_kitchen_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Order sent to kitchen successfully!', 'success');
                    
                    // Clear current order and redirect
                    setTimeout(() => {
                        window.location.href = '<?= base_url("restaurant/{$tenant_id}/waiter/tables") ?>';
                    }, 2000);
                } else {
                    showNotification('Error sending order: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error sending order. Please try again.', 'error');
            },
            complete: function() {
                $('#send_to_kitchen_btn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send to Kitchen');
            }
        });
    }
}

function clearOrder() {
    if (confirm('Clear the current order? This will remove all items.')) {
        currentOrder.items = [];
        $('#customer_name').val('');
        $('#order_notes').val('');
        updateOrderDisplay();
        validateOrder();
        showNotification('Order cleared', 'info');
    }
}

function changeQuantity(change) {
    const currentQty = parseInt($('#item_quantity').val());
    const newQty = Math.max(1, Math.min(20, currentQty + change));
    $('#item_quantity').val(newQty);
}

function showNotification(message, type) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'info': 'alert-info',
        'warning': 'alert-warning'
    }[type] || 'alert-info';

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