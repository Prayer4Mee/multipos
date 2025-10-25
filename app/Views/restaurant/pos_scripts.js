// POS Terminal JavaScript Functions
let currentOrder = [];
let orderTotal = 0;
let newOrderItems = [];
let newOrderTotal = 0;
let selectedOrderId = null;
let currentOrders = [];

$(document).ready(function() {
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
        orderItemsDiv.html('<p class="text-muted">No items selected</p>');
        $('#orderTotal').text('₱0.00');
        updatePlaceOrderButton();
        return;
    }
    
    let html = '';
    currentOrder.forEach(item => {
        const itemTotal = item.price * item.quantity;
        orderTotal += itemTotal;
        
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2">
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
        `;
    });
    
    orderItemsDiv.html(html);
    $('#orderTotal').text(`₱${orderTotal.toFixed(2)}`);
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
        items: currentOrder.map(item => ({
            menu_item_id: item.id,
            quantity: item.quantity,
            unit_price: item.price
        }))
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
                clearOrder();
                loadCurrentOrders(); // Refresh orders list
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

function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.alert('close');
    }, 3000);
}
