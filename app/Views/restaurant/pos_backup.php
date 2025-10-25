<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="pos-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-cash-register"></i> POS Terminal</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshPOS()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                <i class="fas fa-plus"></i> New Order
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Left Side: Current Orders & Menu Categories -->
        <div class="col-md-4">
            <!-- Current Orders -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Orders</h5>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <div id="currentOrdersList">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                            <p>Loading orders...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Categories -->
            <div class="card">
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
        </div>

        <!-- Center: Menu Items -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu Items</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="menuItems">
                        <?php if (!empty($menu_items)): ?>
                            <?php foreach ($menu_items as $item): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card menu-item" data-item-id="<?= $item->id ?>" data-price="<?= $item->price ?>" data-category="<?= $item->category_id ?>">
                                        <div class="card-body text-center">
                                            <i class="fas fa-utensils fa-2x text-primary mb-2"></i>
                                            <h6><?= esc($item->name) ?></h6>
                                            <p class="text-muted small"><?= esc($item->description) ?></p>
                                            <span class="badge bg-primary">₱<?= number_format($item->price, 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-muted text-center">No menu items available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Order Details & Order Summary -->
        <div class="col-md-3">
            <!-- Order Details -->
            <div id="orderDetailsSection" class="mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Details</h5>
                    </div>
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-hand-pointer fa-3x mb-3"></i>
                        <p>Select an order from the list to view details</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
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
                    
                    <div id="orderItems">
                        <p class="text-muted text-center">No items selected</p>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong id="orderTotal">₱0.00</strong>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary" id="placeOrderBtn" disabled>
                            <i class="fas fa-check"></i> Place Order
                        </button>
                        <button class="btn btn-outline-secondary" id="clearOrderBtn">
                            <i class="fas fa-trash"></i> Clear Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Order Modal -->
<div class="modal fade" id="newOrderModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Order Details -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Order Details</h6>
                            </div>
                            <div class="card-body">
                                <form id="newOrderForm">
                                    <div class="mb-3">
                                        <label class="form-label">Table Number</label>
                                        <select class="form-select" id="newOrderTableId" name="table_id" required>
                                            <option value="">Select Table</option>
                                            <?php if (!empty($tables)): ?>
                                                <?php foreach ($tables as $table): ?>
                                                    <option value="<?= $table->id ?>">Table <?= $table->table_number ?> (<?= $table->capacity ?> seats)</option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Customer Name</label>
                                        <input type="text" class="form-control" id="newOrderCustomerName" name="customer_name" placeholder="Optional">
                                    </div>
                                </form>
                                
                                <!-- Order Summary -->
                                <div class="mt-4">
                                    <h6>Order Summary</h6>
                                    <div id="newOrderItems" class="mb-3" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 10px; min-height: 200px;">
                                        <p class="text-muted">No items added yet</p>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong>Total:</strong>
                                        <strong id="newOrderTotal">₱0.00</strong>
                                    </div>
                                    <button type="button" class="btn btn-success w-100" id="createNewOrderBtn" onclick="createNewOrder()" disabled>
                                        <i class="fas fa-check"></i> Create Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Menu Selection -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Select Menu Items</h6>
                            </div>
                            <div class="card-body">
                                <!-- Menu Categories -->
                                <div class="mb-3">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary active" onclick="filterNewOrderMenu('all')">All</button>
                                        <?php if (!empty($menu_categories)): ?>
                                            <?php foreach ($menu_categories as $category): ?>
                                                <button type="button" class="btn btn-outline-primary" onclick="filterNewOrderMenu(<?= $category->id ?>)">
                                                    <?= esc($category->name) ?>
                                                </button>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Menu Items -->
                                <div class="row" id="newOrderMenuItems">
                                    <?php if (!empty($menu_items)): ?>
                                        <?php foreach ($menu_items as $item): ?>
                                            <div class="col-md-6 col-lg-4 mb-3 menu-item-new" data-category="<?= $item->category_id ?>" data-item-id="<?= $item->id ?>" data-price="<?= $item->price ?>" data-name="<?= esc($item->name) ?>">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?= esc($item->name) ?></h6>
                                                        <p class="card-text text-muted small"><?= esc($item->description) ?></p>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="h6 text-primary mb-0">₱<?= number_format($item->price, 2) ?></span>
                                                            <button class="btn btn-sm btn-primary add-to-order-btn" data-item-id="<?= $item->id ?>" data-item-name="<?= esc($item->name) ?>" data-item-price="<?= $item->price ?>">
                                                                <i class="fas fa-plus"></i> Add
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <p class="text-muted text-center">No menu items available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="cancelOrderBtn" onclick="cancelOrder()" style="display: none;">
                    <i class="fas fa-times"></i> Cancel Order
                </button>
                <button type="button" class="btn btn-info" id="updateStatusBtn" onclick="updateOrderStatusFromModal()" style="display: none;">
                    <i class="fas fa-edit"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="paymentOrderId">
                    <div class="mb-3">
                        <label class="form-label">Order #</label>
                        <input type="text" class="form-control" id="paymentOrderNumber" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Table</label>
                        <input type="text" class="form-control" id="paymentTableNumber" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order Total</label>
                        <input type="text" class="form-control" id="paymentOrderTotal" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" required>
                            <option value="">Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="digital">Digital Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount Received</label>
                        <input type="number" class="form-control" id="paymentAmountReceived" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Change</label>
                        <input type="text" class="form-control" id="paymentChangeAmount" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="completePayment()">Complete Payment</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('app/Views/restaurant/pos_scripts.js') ?>"></script>
    console.log('Document ready, initializing POS...');
    console.log('Tenant slug:', '<?= $tenant->tenant_slug ?>');
    console.log('Base URL:', '<?= base_url() ?>');
    
    // Load current orders
    console.log('Loading initial orders...');
    loadCurrentOrders();
    
    // Set interval to refresh orders every 30 seconds
    setInterval(loadCurrentOrders, 30000);
    
    // Menu item click handler
    $('.menu-item').click(function() {
        const itemId = $(this).data('item-id');
        const itemName = $(this).find('h6').text();
        const itemPrice = parseFloat($(this).data('price'));
        
        addToOrder(itemId, itemName, itemPrice);
    });
    
    // New Order Modal - Add to order button handler
    $(document).on('click', '.add-to-order-btn', function() {
        const itemId = $(this).data('item-id');
        const itemName = $(this).data('item-name');
        const itemPrice = parseFloat($(this).data('item-price'));
        
        addToNewOrder(itemId, itemName, itemPrice);
    });
    
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
    
    // Clear order handler
    $('#clearOrderBtn').click(function() {
        clearOrder();
    });
    
    // Place order handler
    $('#placeOrderBtn').click(function() {
        placeOrder();
    });
    
    // Payment amount change handler
    $('#paymentAmountReceived').on('input', function() {
        calculatePaymentChange();
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
    orderTotal = 0;
    
    if (currentOrder.length === 0) {
        orderItemsDiv.html('<p class="text-muted text-center">No items selected</p>');
    } else {
        let html = '';
        currentOrder.forEach(item => {
            const itemTotal = item.price * item.quantity;
            orderTotal += itemTotal;
            
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <small>${item.name}</small><br>
                        <small class="text-muted">₱${item.price} x ${item.quantity}</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-outline-secondary" onclick="decreaseQuantity(${item.id})">-</button>
                        <span class="badge bg-primary">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="increaseQuantity(${item.id})">+</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        orderItemsDiv.html(html);
    }
    
    $('#orderTotal').text('₱' + orderTotal.toFixed(2));
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
    updateOrderDisplay();
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
    
    const orderData = {
        table_id: tableId,
        items: JSON.stringify(currentOrder),
        total_amount: orderTotal,
        customer_name: $('#customerName').val() || '',
        csrf_test_name: '<?= csrf_hash() ?>'
    };
    
    // Show loading
    $('#placeOrderBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/create-order") ?>',
        type: 'POST',
        data: orderData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Order #' + response.order_number + ' placed successfully!');
                clearOrder();
                $('#tableSelect').val('');
                $('#customerName').val('');
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

function startNewOrder() {
    $('#newOrderModal').modal('hide');
    // Additional logic for starting new order
}

function loadCurrentOrders() {
    console.log('Loading current orders...');
    
    // 임시로 하드코딩된 데이터 표시 (디버깅용)
    const testOrders = [
        {
            id: 1,
            order_number: 'ORD20241201001',
            table_numbers: '1',
            status: 'pending',
            total_amount: 150.00,
            item_count: 2
        },
        {
            id: 4,
            order_number: 'ORD20241201003',
            table_numbers: '2',
            status: 'preparing',
            total_amount: 150.00,
            item_count: 1
        },
        {
            id: 5,
            order_number: 'ORD20241201004',
            table_numbers: '3',
            status: 'preparing',
            total_amount: 200.00,
            item_count: 1
        }
    ];
    
    console.log('Using test data for debugging');
    renderCurrentOrders(testOrders);
    
    // 실제 DB 데이터를 AJAX로 가져오기 (주석 처리)
    /*
    $.ajax({
        url: `<?= base_url("restaurant/{$tenant->tenant_slug}/current-orders") ?>`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('AJAX Success:', response);
            if (response.success) {
                console.log('Orders received:', response.orders);
                renderCurrentOrders(response.orders);
            } else {
                console.error('Failed to load orders:', response.error);
                $('#currentOrdersList').html('<div class="text-center text-muted py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Failed to load orders</p></div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', xhr, status, error);
            console.error('Response text:', xhr.responseText);
            $('#currentOrdersList').html('<div class="text-center text-muted py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error loading orders</p></div>');
        }
    });
    */
}

function renderCurrentOrders(orders) {
    console.log('Rendering orders:', orders);
    const container = $('#currentOrdersList');
    container.empty();
    
    if (!orders || orders.length === 0) {
        console.log('No orders to display');
        container.html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                <p>No current orders</p>
                <small>Orders will appear here when placed</small>
            </div>
        `);
        return;
    }
    
    console.log('Displaying', orders.length, 'orders');
    orders.forEach((order, index) => {
        console.log('Creating order item', index, order);
        const orderItem = createOrderListItem(order);
        container.append(orderItem);
    });
}

function createOrderListItem(order) {
    console.log('Creating order list item for:', order);
    const statusClass = getStatusClass(order.status);
    const statusText = getStatusText(order.status);
    
    const html = `
        <div class="order-item p-3 border-bottom" data-order-id="${order.id}" onclick="selectOrder(${order.id})" style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">#${order.order_number}</h6>
                    <p class="mb-1 text-muted small">Table ${order.table_numbers || 'N/A'}</p>
                    <p class="mb-0 text-muted small">${order.item_count || 0} items</p>
                </div>
                <div class="text-end">
                    <span class="badge ${statusClass} mb-1">${statusText}</span>
                    <p class="mb-0 text-primary fw-bold">₱${parseFloat(order.total_amount || 0).toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;
    
    console.log('Generated HTML:', html);
    return html;
}

function createOrderCard(order) {
    const statusClass = getStatusClass(order.status);
    const statusText = getStatusText(order.status);
    
    return `
        <div class="order-card mb-3" data-order-id="${order.id}">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">#${order.order_number}</h6>
                    <span class="badge ${statusClass}">${statusText}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <p class="mb-1"><strong>${order.table_numbers || 'No Table'}</strong></p>
                            <p class="mb-1 text-muted small">${order.item_count || 0} items</p>
                            <p class="mb-1 text-muted small">${formatTime(order.ordered_at)}</p>
                        </div>
                        <div class="col-4 text-end">
                            <h6 class="text-primary mb-0">₱${parseFloat(order.total_amount || 0).toFixed(2)}</h6>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${order.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getStatusClass(status) {
    const statusClasses = {
        'created': 'bg-secondary',
        'pending': 'bg-warning',
        'confirmed': 'bg-info',
        'preparing': 'bg-primary',
        'ready': 'bg-success',
        'served': 'bg-dark',
        'paid': 'bg-success',
        'completed': 'bg-secondary',
        'cancelled': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

function getStatusText(status) {
    const statusTexts = {
        'created': 'Created',
        'pending': 'Pending',
        'confirmed': 'Confirmed',
        'preparing': 'Preparing',
        'ready': 'Ready',
        'served': 'Served',
        'paid': 'Paid',
        'completed': 'Completed',
        'cancelled': 'Cancelled'
    };
    return statusTexts[status] || status;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
    return `${Math.floor(diffMins / 1440)}d ago`;
}

// 기존 하드코딩된 데이터 (제거 예정)
const mockOrders = [
        {
            id: 5,
            order_number: 'ORD202510181008',
            order_type: 'dine_in',
            order_source: 'pos',
            table_id: 3,
            table_number: 'Table 3',
            customer_name: 'Walk-in Customer',
            customer_phone: null,
            customer_email: null,
            waiter_id: null,
            cashier_id: null,
            subtotal: 360.00,
            service_charge: 0.00,
            discount_amount: 0.00,
            discount_type: null,
            vat_amount: 0.00,
            total_amount: 360.00,
            status: 'pending',
            kitchen_status: 'pending',
            priority_level: 'normal',
            payment_status: 'pending',
            payment_method: 'cash',
            special_instructions: null,
            kitchen_notes: null,
            internal_notes: null,
            ordered_at: '2025-10-18 11:46:55',
            estimated_ready_at: null,
            ready_at: null,
            served_at: null,
            completed_at: null,
            created_at: '2025-10-18 11:46:55',
            updated_at: '2025-10-18 11:46:55',
            amount_received: null,
            change_amount: null,
            item_count: 2
        },
        {
            id: 4,
            order_number: 'ORD202510181048',
            order_type: 'dine_in',
            order_source: 'pos',
            table_id: 8,
            table_number: 'Table 8',
            customer_name: 'Walk-in Customer',
            customer_phone: null,
            customer_email: null,
            waiter_id: null,
            cashier_id: null,
            subtotal: 320.00,
            service_charge: 0.00,
            discount_amount: 0.00,
            discount_type: null,
            vat_amount: 0.00,
            total_amount: 320.00,
            status: 'preparing',
            kitchen_status: 'pending',
            priority_level: 'normal',
            payment_status: 'pending',
            payment_method: 'cash',
            special_instructions: null,
            kitchen_notes: null,
            internal_notes: null,
            ordered_at: '2025-10-18 11:16:36',
            estimated_ready_at: null,
            ready_at: null,
            served_at: null,
            completed_at: null,
            created_at: '2025-10-18 11:16:36',
            updated_at: '2025-10-18 11:26:00',
            amount_received: null,
            change_amount: null,
            item_count: 3
        },
        {
            id: 3,
            order_number: 'ORD202510180116',
            order_type: 'dine_in',
            order_source: 'pos',
            table_id: 4,
            table_number: 'Table 4',
            customer_name: 'Walk-in Customer',
            customer_phone: null,
            customer_email: null,
            waiter_id: null,
            cashier_id: null,
            subtotal: 2800.00,
            service_charge: 0.00,
            discount_amount: 0.00,
            discount_type: null,
            vat_amount: 0.00,
            total_amount: 2800.00,
            status: 'pending',
            kitchen_status: 'pending',
            priority_level: 'normal',
            payment_status: 'pending',
            payment_method: 'cash',
            special_instructions: null,
            kitchen_notes: null,
            internal_notes: null,
            ordered_at: '2025-10-18 11:05:59',
            estimated_ready_at: null,
            ready_at: null,
            served_at: null,
            completed_at: null,
            created_at: '2025-10-18 11:05:59',
            updated_at: '2025-10-18 11:05:59',
            amount_received: null,
            change_amount: null,
            item_count: 5
        },
        {
            id: 2,
            order_number: 'ORD202510186033',
            order_type: 'dine_in',
            order_source: 'pos',
            table_id: 1,
            table_number: 'Table 1',
            customer_name: 'Walk-in Customer',
            customer_phone: null,
            customer_email: null,
            waiter_id: null,
            cashier_id: null,
            subtotal: 1530.00,
            service_charge: 0.00,
            discount_amount: 0.00,
            discount_type: null,
            vat_amount: 0.00,
            total_amount: 1530.00,
            status: 'pending',
            kitchen_status: 'pending',
            priority_level: 'normal',
            payment_status: 'pending',
            payment_method: 'cash',
            special_instructions: null,
            kitchen_notes: null,
            internal_notes: null,
            ordered_at: '2025-10-18 10:55:46',
            estimated_ready_at: null,
            ready_at: null,
            served_at: null,
            completed_at: null,
            created_at: '2025-10-18 10:55:46',
            updated_at: '2025-10-18 10:55:46',
            amount_received: null,
            change_amount: null,
            item_count: 4
        },
        {
            id: 1,
            order_number: 'ORD20241201003',
            order_type: 'dine_in',
            order_source: 'pos',
            table_id: 1,
            table_number: 'Table 1',
            customer_name: 'Test Customer',
            customer_phone: null,
            customer_email: null,
            waiter_id: null,
            cashier_id: null,
            subtotal: 250.00,
            service_charge: 0.00,
            discount_amount: 0.00,
            discount_type: null,
            vat_amount: 0.00,
            total_amount: 250.00,
            status: 'completed',
            kitchen_status: 'pending',
            priority_level: 'normal',
            payment_status: 'pending',
            payment_method: 'cash',
            special_instructions: null,
            kitchen_notes: null,
            internal_notes: null,
            ordered_at: '2025-10-18 10:14:40',
            estimated_ready_at: null,
            ready_at: null,
            served_at: null,
            completed_at: '2025-10-18 10:30:21',
            created_at: '2025-10-18 10:14:40',
            updated_at: '2025-10-18 10:30:21',
            amount_received: 300.00,
            change_amount: 50.00,
            item_count: 2
        }
    ];
    
    // 실제 DB 데이터 로드
    loadCurrentOrders();
    
    // 기존 하드코딩된 데이터 사용 중단
    /*
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/current-orders") ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayCurrentOrders(response.orders);
            }
        },
        error: function(xhr) {
            console.log('Failed to load current orders');
        }
    });
    */
}

function displayCurrentOrders(orders) {
    const ordersList = $('#currentOrdersList');
    
    if (orders.length === 0) {
        ordersList.html('<p class="text-muted text-center p-3">No active orders</p>');
        return;
    }
    
    let html = '';
    orders.forEach(function(order) {
        const statusClass = getStatusClass(order.status);
        const timeAgo = getTimeAgo(order.ordered_at || order.created_at);
        
        html += `
            <div class="border-bottom p-3 order-item" data-order-id="${order.id}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">#${order.order_number}</h6>
                    <span class="badge ${statusClass}">${order.status}</span>
                </div>
                <p class="mb-1"><strong>Table:</strong> ${order.table_number || 'N/A'}</p>
                <p class="mb-1"><strong>Items:</strong> ${order.item_count || 'N/A'}</p>
                <p class="mb-1"><strong>Total:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>
                <p class="mb-2 text-muted small">${timeAgo}</p>
                <div class="d-flex gap-1">
                    ${order.status === 'ready' ? 
                        `<button class="btn btn-sm btn-success" onclick="processPayment(${order.id}, '${order.order_number}', '${order.table_number || 'N/A'}', ${order.total_amount})">
                            <i class="fas fa-credit-card"></i> Pay
                        </button>` : 
                        `<button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${order.id})">
                            <i class="fas fa-eye"></i> View
                        </button>`
                    }
                </div>
            </div>
        `;
    });
    
    ordersList.html(html);
}

function getStatusClass(status) {
    switch(status) {
        case 'pending': return 'bg-warning';
        case 'preparing': return 'bg-info';
        case 'ready': return 'bg-success';
        case 'completed': return 'bg-primary';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function getTimeAgo(dateString) {
    const now = new Date();
    const orderTime = new Date(dateString);
    const diffMs = now - orderTime;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays}d ago`;
}

function processPayment(orderId, orderNumber, tableNumber, totalAmount) {
    $('#paymentOrderId').val(orderId);
    $('#paymentOrderNumber').val(orderNumber);
    $('#paymentTableNumber').val(tableNumber);
    $('#paymentOrderTotal').val('₱' + parseFloat(totalAmount).toFixed(2));
    $('#paymentAmountReceived').val('');
    $('#paymentChangeAmount').val('');
    $('#paymentModal').modal('show');
}

function calculatePaymentChange() {
    const total = parseFloat($('#paymentOrderTotal').val().replace('₱', ''));
    const received = parseFloat($('#paymentAmountReceived').val()) || 0;
    const change = received - total;
    
    if (change >= 0) {
        $('#paymentChangeAmount').val('₱' + change.toFixed(2));
    } else {
        $('#paymentChangeAmount').val('₱0.00');
    }
}

function completePayment() {
    const orderId = $('#paymentOrderId').val();
    const paymentMethod = $('#paymentMethod').val();
    const amountReceived = parseFloat($('#paymentAmountReceived').val());
    const orderTotal = parseFloat($('#paymentOrderTotal').val().replace('₱', ''));
    
    if (!paymentMethod) {
        alert('Please select a payment method');
        return;
    }
    
    if (amountReceived < orderTotal) {
        alert('Amount received is less than order total');
        return;
    }
    
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/complete-payment") ?>',
        type: 'POST',
        data: {
            order_id: orderId,
            payment_method: paymentMethod,
            amount_received: amountReceived,
            csrf_test_name: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Payment completed successfully!');
                $('#paymentModal').modal('hide');
                loadCurrentOrders(); // Refresh orders list
            } else {
                alert('Error: ' + (response.error || 'Failed to complete payment'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to complete payment'));
        }
    });
}

let selectedOrderId = null;
let currentOrders = [];

function selectOrder(orderId) {
    // Remove previous selection
    $('.order-item').removeClass('bg-light border-primary');
    
    // Add selection to clicked item
    $(`.order-item[data-order-id="${orderId}"]`).addClass('bg-light border-primary');
    
    selectedOrderId = orderId;
    
    // Load order details
    loadOrderDetails(orderId);
}

function loadOrderDetails(orderId) {
    $.ajax({
        url: `<?= base_url("restaurant/{$tenant->tenant_slug}/order-details") ?>/${orderId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayOrderDetails(response.order);
            } else {
                console.error('Failed to load order details:', response.error);
                showNotification('Failed to load order details', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            showNotification('Failed to load order details', 'error');
        }
    });
}

function displayOrderDetails(order) {
    // Create order details modal or update existing section
    const orderDetailsHtml = `
        <div class="order-details-section">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Details - #${order.order_number}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Table:</strong> ${order.table_numbers || 'N/A'}</p>
                            <p><strong>Customer:</strong> ${order.customer_name || 'Walk-in'}</p>
                            <p><strong>Status:</strong> <span class="badge ${getStatusClass(order.status)}">${getStatusText(order.status)}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Time:</strong> ${formatDateTime(order.ordered_at)}</p>
                            <p><strong>Items:</strong> ${order.item_count || 0}</p>
                            <p><strong>Total:</strong> ₱${parseFloat(order.total_amount || 0).toFixed(2)}</p>
                        </div>
                    </div>
                    
                    <div class="order-items mb-3">
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
                                    ${order.items ? order.items.map(item => `
                                        <tr>
                                            <td>${item.item_name}</td>
                                            <td>${item.quantity}</td>
                                            <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                            <td>₱${parseFloat(item.total_price).toFixed(2)}</td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="4" class="text-center">No items</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" onclick="proceedToPayment(${order.id})">
                            <i class="fas fa-credit-card"></i> Proceed to Payment
                        </button>
                        <button class="btn btn-outline-secondary" onclick="printReceipt(${order.id})">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                        <button class="btn btn-outline-info" onclick="editOrder(${order.id})">
                            <i class="fas fa-edit"></i> Edit Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Update the order details section
    $('#orderDetailsSection').html(orderDetailsHtml);
}

function proceedToPayment(orderId) {
    // Redirect to payment page
    window.location.href = `<?= base_url("restaurant/{$tenant->tenant_slug}/payment") ?>/${orderId}`;
}

function printReceipt(orderId) {
    // Print receipt functionality
    window.open(`<?= base_url("restaurant/{$tenant->tenant_slug}/print-receipt") ?>/${orderId}`, '_blank');
}

function editOrder(orderId) {
    // Edit order functionality
    showNotification('Edit order functionality coming soon', 'info');
}

function formatDateTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function viewOrder(orderId) {
    selectOrder(orderId);
}

function displayOrderDetails(order) {
    let html = `
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Order Information</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Order #:</strong> ${order.order_number}</p>
                        <p><strong>Table:</strong> ${order.table_number || 'N/A'}</p>
                        <p><strong>Customer:</strong> ${order.customer_name || 'Walk-in'}</p>
                        <p><strong>Status:</strong> <span class="badge ${getStatusClass(order.status)}">${order.status}</span></p>
                        <p><strong>Created:</strong> ${formatDateTime(order.created_at)}</p>
                        ${order.completed_at ? `<p><strong>Completed:</strong> ${formatDateTime(order.completed_at)}</p>` : ''}
                        ${order.payment_method ? `<p><strong>Payment:</strong> ${order.payment_method}</p>` : ''}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Order Items</h6>
                    </div>
                    <div class="card-body">
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
    `;
    
    order.items.forEach(function(item) {
        // Use unit_price from order_items table, fallback to current_price from menu_items
        const unitPrice = parseFloat(item.unit_price || item.current_price || item.price || 0);
        const quantity = parseInt(item.quantity || 1);
        const totalPrice = unitPrice * quantity;
        
        html += `
            <tr>
                <td>${item.menu_item_name || 'Unknown Item'}</td>
                <td>${quantity}</td>
                <td>₱${unitPrice.toFixed(2)}</td>
                <td>₱${totalPrice.toFixed(2)}</td>
            </tr>
        `;
    });
    
    html += `
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total:</th>
                                        <th>₱${parseFloat(order.total_amount).toFixed(2)}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#orderDetailContent').html(html);
    
    // Show/hide action buttons based on order status
    if (order.status === 'pending' || order.status === 'preparing') {
        $('#cancelOrderBtn').show();
        $('#updateStatusBtn').show();
    } else if (order.status === 'ready') {
        $('#cancelOrderBtn').hide();
        $('#updateStatusBtn').show();
    } else {
        $('#cancelOrderBtn').hide();
        $('#updateStatusBtn').hide();
    }
    
    // Store current order ID for actions
    $('#cancelOrderBtn').data('order-id', order.id);
    $('#updateStatusBtn').data('order-id', order.id);
    $('#updateStatusBtn').data('current-status', order.status);
}

function getStatusClass(status) {
    switch(status) {
        case 'pending': return 'bg-warning';
        case 'preparing': return 'bg-info';
        case 'ready': return 'bg-success';
        case 'completed': return 'bg-primary';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function cancelOrder() {
    const orderId = $('#cancelOrderBtn').data('order-id');
    
    if (confirm('Are you sure you want to cancel this order?')) {
        // Show loading
        const cancelBtn = $('#cancelOrderBtn');
        cancelBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cancelling...');
        
        // Send AJAX request to cancel order
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant->tenant_slug}/update-order-status") ?>',
            type: 'POST',
            data: {
                order_id: orderId,
                status: 'cancelled'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Order cancelled successfully!');
                    $('#orderDetailModal').modal('hide');
                    location.reload(); // Refresh the page to show updated order list
                } else {
                    alert('Error: ' + (response.error || 'Failed to cancel order'));
                }
            },
            error: function(xhr) {
                console.error('Cancel Order Error:', xhr);
                const response = xhr.responseJSON;
                alert('Error: ' + (response?.error || 'Failed to cancel order. Status: ' + xhr.status));
            },
            complete: function() {
                cancelBtn.prop('disabled', false).html('<i class="fas fa-times"></i> Cancel Order');
            }
        });
    }
}

function updateOrderStatusFromModal() {
    const orderId = $('#updateStatusBtn').data('order-id');
    const currentStatus = $('#updateStatusBtn').data('current-status');
    
    let newStatus;
    let confirmMessage;
    
    switch(currentStatus) {
        case 'pending':
        case 'confirmed':
            newStatus = 'preparing';
            confirmMessage = 'Start preparing this order?';
            break;
        case 'preparing':
            newStatus = 'ready';
            confirmMessage = 'Mark this order as ready?';
            break;
        case 'ready':
            newStatus = 'completed';
            confirmMessage = 'Complete this order?';
            break;
        default:
            alert('Cannot update status for this order');
            return;
    }
    
    if (confirm(confirmMessage)) {
        // Show loading
        const updateBtn = $('#updateStatusBtn');
        updateBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        // Send AJAX request to update order status
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant->tenant_slug}/update-order-status") ?>',
            type: 'POST',
            data: {
                order_id: orderId,
                status: newStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Order status updated successfully!');
                    $('#orderDetailModal').modal('hide');
                    location.reload(); // Refresh the page to show updated order list
                } else {
                    alert('Error: ' + (response.error || 'Failed to update order status'));
                }
            },
            error: function(xhr) {
                console.error('Update Status Error:', xhr);
                const response = xhr.responseJSON;
                alert('Error: ' + (response?.error || 'Failed to update order status. Status: ' + xhr.status));
            },
            complete: function() {
                updateBtn.prop('disabled', false).html('<i class="fas fa-edit"></i> Update Status');
            }
        });
    }
}

function refreshPOS() {
    location.reload();
}

// New Order Modal Functions
function filterNewOrderMenu(categoryId) {
    // Update active button
    $('.btn-group .btn').removeClass('active');
    event.target.classList.add('active');
    
    // Filter menu items
    if (categoryId === 'all') {
        $('.menu-item-new').show();
    } else {
        $('.menu-item-new').hide();
        $(`.menu-item-new[data-category="${categoryId}"]`).show();
    }
}

function addToNewOrder(itemId, itemName, price) {
    // Ensure price is a number
    const numericPrice = parseFloat(price);
    
    // Check if item already exists
    const existingItemIndex = newOrderItems.findIndex(item => item.id === itemId);
    
    if (existingItemIndex !== -1) {
        // Item exists, increase quantity
        newOrderItems[existingItemIndex].quantity += 1;
    } else {
        // Item doesn't exist, add new item
        const newItem = {
            id: itemId,
            name: itemName,
            price: numericPrice,
            quantity: 1
        };
        newOrderItems.push(newItem);
    }
    
    updateNewOrderDisplay();
}

function removeFromNewOrder(itemId) {
    newOrderItems = newOrderItems.filter(item => item.id !== itemId);
    updateNewOrderDisplay();
}

function updateNewOrderQuantity(itemId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromNewOrder(itemId);
        return;
    }
    
    const item = newOrderItems.find(item => item.id === itemId);
    if (item) {
        item.quantity = parseInt(newQuantity);
        updateNewOrderDisplay();
    }
}

function updateNewOrderDisplay() {
    const orderItemsDiv = $('#newOrderItems');
    newOrderTotal = 0;
    
    if (newOrderItems.length === 0) {
        orderItemsDiv.html('<p class="text-muted">No items added yet</p>');
        $('#createNewOrderBtn').prop('disabled', true);
    } else {
        let html = '';
        newOrderItems.forEach(function(item, index) {
            // Ensure both price and quantity are numbers
            const price = parseFloat(item.price) || 0;
            const quantity = parseInt(item.quantity) || 0;
            const itemTotal = price * quantity;
            newOrderTotal += itemTotal;
            
            // Create HTML for each item
            const itemHtml = `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">₱${price.toFixed(2)} each</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateNewOrderQuantity(${item.id}, ${quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="mx-2">${quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateNewOrderQuantity(${item.id}, ${quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromNewOrder(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="text-end">
                        <strong>₱${itemTotal.toFixed(2)}</strong>
                    </div>
                </div>
            `;
            
            html += itemHtml;
        });
        
        orderItemsDiv.html(html);
        $('#createNewOrderBtn').prop('disabled', false);
        
        // Auto scroll to bottom to show newest items
        orderItemsDiv.scrollTop(orderItemsDiv[0].scrollHeight);
    }
    
    $('#newOrderTotal').text('₱' + newOrderTotal.toFixed(2));
}

function createNewOrder() {
    const tableId = $('#newOrderTableId').val();
    const customerName = $('#newOrderCustomerName').val();
    
    if (!tableId) {
        alert('Please select a table');
        return;
    }
    
    if (newOrderItems.length === 0) {
        alert('Please add at least one item to the order');
        return;
    }
    
    // Show loading
    const createBtn = $('#createNewOrderBtn');
    createBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
    
    // Prepare order data
    const orderData = {
        table_id: tableId,
        customer_name: customerName || '',
        items: JSON.stringify(newOrderItems),
        total_amount: parseFloat(newOrderTotal).toFixed(2),
        csrf_test_name: '<?= csrf_hash() ?>'
    };
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/create-order") ?>',
        type: 'POST',
        data: orderData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Order created successfully!');
                $('#newOrderModal').modal('hide');
                
                // Reset form
                $('#newOrderForm')[0].reset();
                newOrderItems = [];
                newOrderTotal = 0;
                updateNewOrderDisplay();
                
                // Refresh current orders
                loadCurrentOrders();
            } else {
                alert('Error: ' + (response.error || 'Failed to create order'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to create order: ' + xhr.responseText));
        },
        complete: function() {
            createBtn.prop('disabled', false).html('<i class="fas fa-check"></i> Create Order');
        }
    });
}
</script>
<?= $this->endSection() ?>
