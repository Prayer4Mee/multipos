<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .menu-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .order-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    .order-item:hover {
        border-left-color: #007bff;
        background-color: #f8f9fa;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-plus-circle"></i> New Order</h1>
                <div>
                    <a href="<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to POS
                    </a>
                    <button class="btn btn-success" onclick="placeOrder()" id="placeOrderBtn" disabled>
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Side: Menu Categories & Items -->
        <div class="col-md-8">
            <!-- Menu Categories -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu Categories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active" data-category="all">
                            <i class="fas fa-th-large"></i> All Items
                        </a>
                        <?php if (!empty($menu_categories)): ?>
                            <?php foreach ($menu_categories as $category): ?>
                                <a href="#" class="list-group-item list-group-item-action" data-category="<?= $category->id ?>">
                                    <i class="fas fa-utensils"></i> <?= esc($category->name) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu Items</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="menuItems">
                        <?php if (!empty($menu_items)): ?>
                            <?php foreach ($menu_items as $item): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card menu-item h-100" data-item-id="<?= $item->id ?>" data-price="<?= $item->price ?>" data-category="<?= $item->category_id ?>">
                                        <div class="card-body text-center">
                                            <i class="fas fa-utensils fa-2x text-primary mb-2"></i>
                                            <h6 class="card-title"><?= esc($item->name) ?></h6>
                                            <p class="card-text text-muted small"><?= esc($item->description) ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h6 text-primary mb-0">₱<?= number_format($item->price, 2) ?></span>
                                                <button class="btn btn-sm btn-outline-primary" onclick="addToOrder(<?= $item->id ?>, '<?= esc($item->name) ?>', <?= $item->price ?>)">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
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

        <!-- Right Side: Order Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <!-- Table Selection -->
                    <div class="mb-3">
                        <label class="form-label">Table Number</label>
                        <select class="form-select" id="tableSelect">
                            <option value="">Select Table</option>
                            <?php if (!empty($tables)): ?>
                                <?php foreach ($tables as $table): ?>
                                    <option value="<?= $table->id ?>">Table <?= $table->table_number ?> (<?= $table->capacity ?> seats)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customerName" placeholder="Enter customer name (optional)">
                    </div>
                    
                    <!-- Order Items -->
                    <div class="mb-3">
                        <h6>Order Items</h6>
                        <div id="orderItems" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted">No items selected</p>
                        </div>
                    </div>
                    
                    <!-- Order Total -->
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Subtotal:</strong>
                        <strong id="orderSubtotal">₱0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Service Charge (10%):</span>
                        <span id="serviceCharge">₱0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>VAT (12%):</span>
                        <span id="vatAmount">₱0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong id="orderTotal">₱0.00</strong>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary" id="placeOrderBtn" disabled>
                            <i class="fas fa-check"></i> Place Order
                        </button>
                        <button class="btn btn-outline-secondary" onclick="clearOrder()">
                            <i class="fas fa-trash"></i> Clear Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// New Order JavaScript Functions
let currentOrder = [];
let orderSubtotal = 0;
let serviceCharge = 0;
let vatAmount = 0;
let orderTotal = 0;

$(document).ready(function() {
    // Category filter handler
    $('.list-group-item[data-category]').click(function(e) {
        e.preventDefault();
        
        // Update active state
        $('.list-group-item').removeClass('active');
        $(this).addClass('active');
        
        // Filter menu items
        const categoryId = $(this).data('category');
        filterMenuItems(categoryId);
    });
    
    // Table selection handler
    $('#tableSelect').change(function() {
        updatePlaceOrderButton();
    });
    
    // Customer name handler
    $('#customerName').on('input', function() {
        updatePlaceOrderButton();
    });
});

function filterMenuItems(categoryId) {
    if (categoryId === 'all') {
        $('.menu-item').show();
    } else {
        $('.menu-item').hide();
        $(`.menu-item[data-category="${categoryId}"]`).show();
    }
}

function addToOrder(itemId, itemName, price) {
    const existingItem = currentOrder.find(item => item.id === itemId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        currentOrder.push({
            id: itemId,
            name: itemName,
            price: price,
            quantity: 1
        });
    }
    
    updateOrderDisplay();
}

function updateOrderDisplay() {
    const orderItemsDiv = $('#orderItems');
    orderSubtotal = 0;
    
    if (currentOrder.length === 0) {
        orderItemsDiv.html('<p class="text-muted">No items selected</p>');
        updateTotals();
        updatePlaceOrderButton();
        return;
    }
    
    let html = '';
    currentOrder.forEach(item => {
        const itemTotal = item.price * item.quantity;
        orderSubtotal += itemTotal;
        
        html += `
            <div class="order-item p-2 mb-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">${item.name}</span>
                        <br>
                        <small class="text-muted">₱${item.price.toFixed(2)} each</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="decreaseQuantity(${item.id})">-</button>
                        <span class="mx-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="increaseQuantity(${item.id})">+</button>
                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeItem(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold">₱${itemTotal.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    orderItemsDiv.html(html);
    updateTotals();
    updatePlaceOrderButton();
}

function increaseQuantity(itemId) {
    const item = currentOrder.find(item => item.id === itemId);
    if (item) {
        item.quantity += 1;
        updateOrderDisplay();
    }
}

function decreaseQuantity(itemId) {
    const item = currentOrder.find(item => item.id === itemId);
    if (item && item.quantity > 1) {
        item.quantity -= 1;
        updateOrderDisplay();
    }
}

function removeItem(itemId) {
    currentOrder = currentOrder.filter(item => item.id !== itemId);
    updateOrderDisplay();
}

function clearOrder() {
    currentOrder = [];
    $('#tableSelect').val('');
    $('#customerName').val('');
    updateOrderDisplay();
}

function updateTotals() {
    // Calculate service charge (10%)
    serviceCharge = orderSubtotal * 0.10;
    
    // Calculate VAT (12% on subtotal + service charge)
    vatAmount = (orderSubtotal + serviceCharge) * 0.12;
    
    // Calculate total
    orderTotal = orderSubtotal + serviceCharge + vatAmount;
    
    // Update display
    $('#orderSubtotal').text(`₱${orderSubtotal.toFixed(2)}`);
    $('#serviceCharge').text(`₱${serviceCharge.toFixed(2)}`);
    $('#vatAmount').text(`₱${vatAmount.toFixed(2)}`);
    $('#orderTotal').text(`₱${orderTotal.toFixed(2)}`);
}

function updatePlaceOrderButton() {
    const hasItems = currentOrder.length > 0;
    const hasTable = $('#tableSelect').val() !== '';
    $('#placeOrderBtn').prop('disabled', !(hasItems && hasTable));
}

function placeOrder() {
    if (currentOrder.length === 0) {
        alert('Please add items to the order');
        return;
    }
    
    const tableId = $('#tableSelect').val();
    if (!tableId) {
        alert('Please select a table');
        return;
    }
    
    const customerName = $('#customerName').val() || 'Walk-in Customer';
    
    const orderData = {
        table_id: tableId,
        customer_name: customerName,
        items: currentOrder.map(item => ({
            menu_item_id: item.id,
            quantity: item.quantity,
            unit_price: item.price
        })),
        subtotal: orderSubtotal,
        service_charge: serviceCharge,
        vat_amount: vatAmount,
        total_amount: orderTotal
    };
    
    $('#placeOrderBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Placing Order...');
    
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/place-order") ?>',
        type: 'POST',
        data: orderData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Order #' + response.order_number + ' placed successfully!');
                // Redirect to POS page
                window.location.href = '<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>';
            } else {
                alert('Error: ' + (response.error || 'Failed to place order'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to place order'));
        },
        complete: function() {
            $('#placeOrderBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Place Order');
        }
    });
}
</script>
<?= $this->endSection() ?>
