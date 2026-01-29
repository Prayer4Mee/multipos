<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    
    .category-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .category-btn {
        margin: 5px;
        border-radius: 20px;
        padding: 8px 16px;
        border: 2px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .category-btn:hover, .category-btn.active {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.6);
        transform: translateY(-2px);
    }
    .menu-table {
        max-height: 70vh;
        overflow-y: auto;
    }
    .order-summary {
        max-height: 80vh;
        overflow-y: auto;
        padding-bottom: 20px;
    }
    .order-summary .mb-3:first-child {
    margin-top: 0;
    }
    .add-btn {
        border-radius: 20px;
        padding: 5px 15px;
    }
    .order-item {
        border-left: 4px solid #007bff;
        margin-bottom: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-plus-circle"></i> New Order</h1>
                <a href="<?= base_url("restaurant/{$tenant_slug}/pos") ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to POS
                </a>
            </div>
        </div>
    </div>

    <!-- Category Filter Section -->
    <div class="category-filter">
        <h5 class="text-white mb-3"><i class="fas fa-filter"></i> Menu Categories</h5>
        <div class="d-flex flex-wrap">
            <button class="btn category-btn active" data-category="all">
                <i class="fas fa-hamburger"></i> All Items
            </button>
            <?php if (!empty($menu_categories)): ?>
                <?php foreach ($menu_categories as $category): ?>
                    <button class="btn category-btn" data-category="<?= $category->id ?>">
                        <i class="fas fa-list"></i> <?= esc($category->name) ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Left Side: Menu Items Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-utensils"></i> Menu Items</h5>
                    <div class="d-flex align-items-center">
                        <input type="text" class="form-control form-control-sm me-2" id="menu-search" placeholder="Search menu items..." style="width: 200px;">
                        <span class="badge bg-primary" id="item-count">0 items</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    
                    <div class="menu-table">
                        <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Item Name</th>
                                    <th width="15%">Category</th>
                                    <th width="15%">Price</th>
                                    <th width="20%">Description</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="menu-items-table">
                                <!-- Sample menu items -->
                                <!-- Not Anymore -->
                                <?php if (!empty($menu_items)): ?>
                                    <?php $index = 1; ?>
                                    <?php foreach ($menu_items as $item): ?>
                                        <tr data-category="<?= $item->category_id ?>" data-item-id="<?= $item->id ?>">
                                            <td><?= $index++ ?></td>
                                            <td><strong><?= esc($item->name) ?></strong></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php 
                                                    $categoryName = '';
                                                    foreach ($menu_categories as $cat) {
                                                        if ($cat->id == $item->category_id) {
                                                            $categoryName = $cat->name;
                                                            break;
                                                        }
                                                    }
                                                    echo esc($categoryName);
                                                    ?>
                                                </span>
                                            </td>
                                            <td><strong>₱<?= number_format($item->price, 2) ?></strong></td>
                                            <td><?= esc($item->description ?? 'N/A') ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(<?= $item->id ?>, '<?= addslashes(esc($item->name)) ?>', <?= $item->price ?>)">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox"></i> No menu items available
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

        <!-- Right Side: Order Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-shopping-cart"></i> Order Summary</h5>
                </div>
                <div class="card-body order-summary">
                    <!-- Order Type -->
                    <div class="mb-3">
                        <label class="form-label">Order Type</label>
                        <select class="form-select" id="order-type">
                            <option value="dine_in" selected>Dine In</option>
                            <option value="takeout">Takeout</option>
                            <option value="delivery">Delivery</option>
                            <option value="drive_through">Drive Through</option>
                        </select>
                    </div>
                    <!-- Number of Guests -->
                    <div class="mb-3">
                        <label class="form-label">Number of Guests</label>
                        <input type="number" class="form-control" id="guests-count" min="1" max="50" value="1">
                    </div>
                    <!-- Customer Information -->
                    <div class="mb-3">
                        <label class="form-label">Table Number</label>
                        <select class="form-select" id="table-select">
                            <option value="" disabled selected>Select a Table</option>
                            <?php if (!empty($tables)): ?>
                                <?php foreach ($tables as $table): ?>
                                    <?php
                                        // Apply same colors as the old static one
                                        switch ($table->status) {
                                            case 'available':
                                                $emoji = '✅'; // bg-success
                                                $disabled = '';
                                                break;

                                            case 'occupied':
                                                $emoji = '⛔'; // bg-warning
                                                $disabled = 'disabled';
                                                break;

                                            case 'reserved':
                                                $emoji = '📅'; // bg-danger
                                                $disabled = 'disabled';
                                                break;

                                            case 'cleaning':
                                                $emoji = '🧹'; // bg-secondary
                                                $disabled = 'disabled';
                                                break;

                                            default:
                                                $emoji = '🚫'; // bg-dark
                                                $disabled = 'disabled';
                                                break;
                                        }
                                    ?>
                                    <option value="<?= $table->id?>" <?= $disabled ?>>
                                        <?= $emoji ?>Table <?= $table->table_number ?> (<?= ucfirst($table->status) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No tables available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer-name" placeholder="Enter customer name">
                    </div>

                    <!-- Order Items -->
                    <div class="order-items mb-3">
                        <h6>Order Items:</h6>
                        <div id="order-items-list" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-muted text-center py-3">
                                <i class="fas fa-shopping-cart"></i><br>
                                No items added yet
                            </div>
                        </div>
                    </div>

                    <!-- Order Total -->
                    <div class="order-total mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Subtotal:</strong>
                            <span id="subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Service Charge (10%):</span>
                            <span id="service-charge">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>VAT (12%):</span>
                            <span id="vat">₱0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong id="total">₱0.00</strong>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" id="create-order-btn" onclick="createOrder()" disabled>
                            <i class="fas fa-check"></i> Create Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    
let orderItems = [];
let currentCategory = "all";

$(document).ready(function() {
    updateItemCount();
    
    
    // Category filter
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        currentCategory = String($(this).data('category')); // Now converts to string
        filterMenuItems();
    });
    
    // Search functionality
    $('#menu-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterMenuItems(searchTerm);
    });
});

function filterMenuItems(searchTerm = '') {
    $('#menu-items-table tr').each(function() {
        const $row = $(this);
        const itemCategory = String($row.data('category')); // Now converts to string
        const itemName = $row.find('td:nth-child(2)').text().toLowerCase();
        
        const categoryMatch = currentCategory === 'all' || itemCategory === currentCategory;
        const searchMatch = searchTerm === '' || itemName.includes(searchTerm);
        
        if (categoryMatch && searchMatch) {
            $row.show();
        } else {
            $row.hide();
        }
    });
    
    updateItemCount();
}

function updateItemCount() {
    const visibleItems = $('#menu-items-table tr:visible').length;
    $('#item-count').text(visibleItems + ' items');
}

function addToOrder(id, name, price) {
    const existingItem = orderItems.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        orderItems.push({
            id: id,
            name: name,
            price: price,
            quantity: 1
        });
    }
    
    updateOrderDisplay();
    showNotification('Item added to order', 'success');
}

function removeFromOrder(id) {
    orderItems = orderItems.filter(item => item.id !== id);
    updateOrderDisplay();
}

function updateQuantity(id, newQuantity) {
    const item = orderItems.find(item => item.id === id);
    if (item) {
        if (newQuantity <= 0) {
            removeFromOrder(id);
        } else {
            item.quantity = newQuantity;
            updateOrderDisplay();
        }
    }
}

function updateOrderDisplay() {
    const orderList = $('#order-items-list');
    orderList.empty();
    
    if (orderItems.length === 0) {
        orderList.html(`
            <div class="text-muted text-center py-3">
                <i class="fas fa-shopping-cart"></i><br>
                No items added yet
            </div>
        `);
        $('#create-order-btn').prop('disabled', true);
        $('#order-item-count').text('0');  // Update item count
    } else {
        orderItems.forEach(item => {
            const itemHtml = `
                <div class="order-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="fw-bold">${item.name}</div>
                            <small class="text-muted">₱${item.price.toFixed(2)} each</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary me-1" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="mx-2 fw-bold">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary me-2" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="fw-bold text-primary">₱${(item.price * item.quantity).toFixed(2)}</span>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromOrder(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            orderList.append(itemHtml);
        });
        
        $('#create-order-btn').prop('disabled', false);
        $('#order-item-count').text(orderItems.length);  // ✅ Update item count
    }
    
    updateTotals();
}

function updateTotals() {
    const subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const serviceCharge = subtotal * 0.10;
    const vat = (subtotal + serviceCharge) * 0.12;
    const total = subtotal + serviceCharge + vat;
    
    $('#subtotal').text('₱' + subtotal.toFixed(2));
    $('#service-charge').text('₱' + serviceCharge.toFixed(2));
    $('#vat').text('₱' + vat.toFixed(2));
    $('#total').text('₱' + total.toFixed(2));
}

function createOrder() {
     // Get all form values
    const orderType = $('#order-type').val();
    const guestCount = $('#guests-count').val();
    const tableId = $('#table-select').val();
    const customerName = $('#customer-name').val();

    // Validation
    if (!customerName.trim()) {
        showNotification('Please enter customer name', 'error');
        return;
    }
    if (orderItems.length === 0) {
        showNotification('Please add items to order', 'error');
        return;
    }
    
    $('#create-order-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating order...');
    
    // Get VAT and service charge rates from tenant config (or use defaults)
    // These should be passed from the view as data attributes or global variables
    const vatRate = window.tenantConfig?.vat_rate || 0.12;
    const serviceChargeRate = window.tenantConfig?.service_charge_rate || 0.10;
    
    // Calculate totals
    const subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const serviceCharge = subtotal * serviceChargeRate;
    const vat = (subtotal + serviceCharge) * vatRate;
    const total = subtotal + serviceCharge + vat;

    console.log('Sending order with:', {
        order_type: orderType,
        table_id: tableId,
        guest_count: guestCount,
        customer_name: customerName,
        items: orderItems.length
    });
    
    // Send to backend
    fetch('<?= base_url("restaurant/{$tenant_slug}/create-order") ?>', {
        method: 'POST',
        headers: {
        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        },
        body: new URLSearchParams({
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>',  // ← ADD THIS
            order_type: orderType,
            table_id: tableId,
            guest_count: guestCount,
            customer_name: customerName,
            special_instructions: '',
            subtotal: subtotal.toFixed(2),
            service_charge: serviceCharge.toFixed(2),
            vat_amount: vat.toFixed(2),
            total_amount: total.toFixed(2),
            items: JSON.stringify(orderItems.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: item.quantity
            })))
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            showNotification('Order #' + data.order_number + ' created successfully!', 'success');
            
            // Reset form
            orderItems = [];
            $('#customer-name').val('');
            $('#guests-count').val('1');
            updateOrderDisplay();;
            
            // Redirect to POS after 2 seconds
            setTimeout(() => {
                window.location.href = '<?= base_url("restaurant/{$tenant_slug}/pos") ?>';
            }, 2000);
        } else {
            const errorMsg = typeof data.error === 'object' 
                ? Object.values(data.error).join(', ')
                : data.error || 'Unknown error';
            showNotification('Error: ' + errorMsg, 'error');
            $('#create-order-btn').prop('disabled', false).html('<i class="fas fa-check"></i> Create Order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error: ' + error.message, 'error');
        $('#create-order-btn').prop('disabled', false).html('<i class="fas fa-check"></i> Create Order');
    });
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.alert('close');
    }, 3000);
}
</script>
<?= $this->endSection() ?>