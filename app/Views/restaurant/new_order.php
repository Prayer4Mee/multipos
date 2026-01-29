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
                <a href="<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>" class="btn btn-secondary">
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
            <button class="btn category-btn" data-category="chicken">
                <i class="fas fa-drumstick-bite"></i> Chicken
            </button>
            <button class="btn category-btn" data-category="pasta">
                <i class="fas fa-pizza-slice"></i> Pasta
            </button>
            <button class="btn category-btn" data-category="beverages">
                <i class="fas fa-coffee"></i> Beverages
            </button>
            <button class="btn category-btn" data-category="desserts">
                <i class="fas fa-ice-cream"></i> Desserts
            </button>
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
                    <div class="table-responsive menu-table">
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
                                <tr data-category="chicken">
                                    <td>1</td>
                                    <td><strong>Chicken Joy (2 pcs)</strong></td>
                                    <td><span class="badge bg-warning">Chicken</span></td>
                                    <td><strong>₱100.00</strong></td>
                                    <td>Crispy fried chicken with rice</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(1, 'Chicken Joy (2 pcs)', 100.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                                <tr data-category="chicken">
                                    <td>2</td>
                                    <td><strong>Yum Burger</strong></td>
                                    <td><span class="badge bg-warning">Chicken</span></td>
                                    <td><strong>₱50.00</strong></td>
                                    <td>Chicken burger with special sauce</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(2, 'Yum Burger', 50.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                                <tr data-category="pasta">
                                    <td>3</td>
                                    <td><strong>Jolly Spaghetti</strong></td>
                                    <td><span class="badge bg-info">Pasta</span></td>
                                    <td><strong>₱80.00</strong></td>
                                    <td>Sweet-style spaghetti with hotdog</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(3, 'Jolly Spaghetti', 80.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                                <tr data-category="pasta">
                                    <td>4</td>
                                    <td><strong>Carbonara</strong></td>
                                    <td><span class="badge bg-info">Pasta</span></td>
                                    <td><strong>₱90.00</strong></td>
                                    <td>Creamy carbonara pasta</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(4, 'Carbonara', 90.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                                <tr data-category="beverages">
                                    <td>5</td>
                                    <td><strong>Coke</strong></td>
                                    <td><span class="badge bg-success">Beverages</span></td>
                                    <td><strong>₱25.00</strong></td>
                                    <td>Refreshing cola drink</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(5, 'Coke', 25.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                                <tr data-category="beverages">
                                    <td>6</td>
                                    <td><strong>Pineapple Juice</strong></td>
                                    <td><span class="badge bg-success">Beverages</span></td>
                                    <td><strong>₱30.00</strong></td>
                                    <td>Fresh pineapple juice</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(6, 'Pineapple Juice', 30.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                                <tr data-category="desserts">
                                    <td>7</td>
                                    <td><strong>Ice Cream</strong></td>
                                    <td><span class="badge bg-danger">Desserts</span></td>
                                    <td><strong>₱40.00</strong></td>
                                    <td>Vanilla ice cream</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(7, 'Ice Cream', 40.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                                <tr data-category="chicken">
                                    <td>8</td>
                                    <td><strong>Chicken Sandwich</strong></td>
                                    <td><span class="badge bg-warning">Chicken</span></td>
                                    <td><strong>₱60.00</strong></td>
                                    <td>Grilled chicken sandwich</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(8, 'Chicken Sandwich', 60.00)">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                    <!-- Customer Information -->
                    <div class="mb-3">
                        <label class="form-label">Table Number</label>
                        <select class="form-select" id="table-select">
                            <option value="1">Table 1</option>
                            <option value="2">Table 2</option>
                            <option value="3">Table 3</option>
                            <option value="4">Table 4</option>
                            <option value="5">Table 5</option>
                            <option value="6">Table 6</option>
                            <option value="7">Table 7</option>
                            <option value="8">Table 8</option>
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

<script>
let orderItems = [];
let currentCategory = "all";

$(document).ready(function() {
    updateItemCount();
    
    // Category filter
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        currentCategory = $(this).data('category');
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
        const itemCategory = $row.data('category');
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
    const tableId = $('#table-select').val();
    const customerName = $('#customer-name').val();
    
    if (!customerName.trim()) {
        showNotification('Please enter customer name', 'error');
        return;
    }
    
    if (orderItems.length === 0) {
        showNotification('Please add items to order', 'error');
        return;
    }
    
    $('#create-order-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Order created successfully!', 'success');
        
        // Reset form
        orderItems = [];
        $('#customer-name').val('');
        updateOrderDisplay();
        
        $('#create-order-btn').prop('disabled', false).html('<i class="fas fa-check"></i> Create Order');
        
        // Redirect to POS after 2 seconds
        setTimeout(() => {
            window.location.href = '<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>';
        }, 2000);
    }, 1500);
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