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
    
    
    // Set up CSRF token for all AJAX requests BEFORE loading other scripts
    $(document).ready(function() {
        $.ajaxSetup({
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }
        });
        console.log('CSRF token configured for all AJAX requests');
    });



    // Load current orders
    console.log('Loading initial orders...');
    loadCurrentOrders();
    
    // Set interval to refresh orders every 30 seconds
    setInterval(loadCurrentOrders, 30000);
    
    // Refresh orders handler
    window.refreshOrders = function() {
        loadCurrentOrders();
    };
});


function loadCurrentOrders() {
    console.log('Loading current orders...');
    
    // 실제 DB 데이터를 AJAX로 가져오기
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
    $('.order-item').removeClass('selected');
    
    // Add selection to clicked item
    $(`.order-item[data-order-id="${orderId}"]`).addClass('selected');
    
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
                            <p><strong>Table:</strong> ${order.table_number ? 'Table ' + order.table_number : 'N/A'}</p>
                        <p><strong>Customer:</strong> ${order.customer_name || 'Walk-in'}</p>
                            <p><strong>Status:</strong> <span class="badge ${getStatusClass(order.status)}">${getStatusText(order.status)}</span></p>
            </div>
            <div class="col-md-6">
                            <p><strong>Order Time:</strong> ${formatDateTime(order.ordered_at)}</p>
                            <p><strong>Items:</strong> ${order.items ? order.items.length : 0}</p>
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
                                    ${order.items && order.items.length > 0 ? order.items.map(item => `
                                        <tr>
                                            <td>${item.item_name || item.menu_item_name || 'Unknown Item'}</td>
                                            <td>${item.quantity}</td>
                                            <td>₱${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                                            <td>₱${parseFloat(item.total_price || (item.unit_price * item.quantity) || 0).toFixed(2)}</td>
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


function editOrder(orderId) {
    // Edit order functionality - 주문 수정 모달 표시
    $.ajax({
        url: `<?= base_url("restaurant/{$tenant->tenant_slug}/order-details") ?>/${orderId}`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Edit order response:', response);
            if (response.success) {
                showEditOrderModal(response.order);
            } else {
                showNotification('Failed to load order details: ' + (response.error || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', xhr, status, error);
            console.error('Response text:', xhr.responseText);
            showNotification('Failed to load order details: ' + error, 'error');
        }
    });
}

function showEditOrderModal(order) {
    const modalHtml = `
        <div class="modal fade" id="editOrderModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Order - #${order.order_number}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p><strong>Table:</strong> ${order.table_number ? 'Table ' + order.table_number : 'N/A'}</p>
                                <p><strong>Customer:</strong> ${order.customer_name || 'Walk-in'}</p>
                                <p><strong>Status:</strong> <span class="badge ${getStatusClass(order.status)}">${getStatusText(order.status)}</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Order Items</h6>
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
                                            <td>${item.item_name || item.menu_item_name || 'Unknown Item'}</td>
                                            <td>${item.quantity}</td>
                                            <td>₱${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                                            <td>₱${parseFloat(item.total_price || (item.unit_price * item.quantity) || 0).toFixed(2)}</td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="4" class="text-center">No items</td></tr>'}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Update Order Status</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-warning" onclick="updateOrderStatus(${order.id}, 'pending')">Pending</button>
                                <button type="button" class="btn btn-outline-info" onclick="updateOrderStatus(${order.id}, 'preparing')">Preparing</button>
                                <button type="button" class="btn btn-outline-success" onclick="updateOrderStatus(${order.id}, 'ready')">Ready</button>
                                <button type="button" class="btn btn-outline-primary" onclick="updateOrderStatus(${order.id}, 'served')">Served</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" onclick="cancelOrder(${order.id})">Cancel Order</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#editOrderModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#editOrderModal').modal('show');
}

function updateOrderStatus(orderId, status) {
    $.ajax({
        url: `<?= base_url("restaurant/{$tenant->tenant_slug}/update-order-status") ?>`,
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        data: {
            order_id: orderId,
            status: status
        },
        success: function(response) {
            console.log('Update status response:', response);
            if (response.success) {
                showNotification('Order status updated successfully', 'success');
                $('#editOrderModal').modal('hide');
                loadCurrentOrders(); // Refresh orders list
            } else {
                showNotification('Failed to update order status: ' + (response.error || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', xhr, status, error);
            console.error('Response text:', xhr.responseText);
            showNotification('Failed to update order status: ' + error, 'error');
        }
    });
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        updateOrderStatus(orderId, 'cancelled');
    }
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
// ==========================================
// PRINT PAID RECEIPTS FUNCTIONALITY
// ==========================================

let allPaidOrders = [];
let selectedPaidOrderId = null;

function printReceipt(orderId) {
    // Print receipt functionality
    window.open(`<?= base_url("restaurant/{$tenant->tenant_slug}/print-receipt") ?>/${orderId}`, '_blank');
}

function openPrintPaidReceiptsModal() {
    // Load paid orders and show modal
    loadPaidOrders();
    const modal = new bootstrap.Modal(document.getElementById('printPaidReceiptsModal'));
    modal.show();
}

function loadPaidOrders() {
    console.log('Loading paid orders...');
    
    $.ajax({
        url: `<?= base_url("restaurant/{$tenant->tenant_slug}/paid-orders") ?>`,
        
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Paid orders loaded:', response);
            if (response.success) {
                allPaidOrders = response.orders;
                renderPaidOrders(response.orders);
            } else {
                showNotification('Failed to load paid orders', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading paid orders:', error);
            showNotification('Error loading paid orders', 'error');
        }
    });
}

function renderPaidOrders(orders) {
    const container = $('#paidOrdersList');
    container.empty();
    
    if (!orders || orders.length === 0) {
        container.html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <p>No paid orders found</p>
            </div>
        `);
        return;
    }
    
    const html = orders.map(order => `
        <div class="order-item p-3 border-bottom" 
             data-order-id="${order.id}" 
             onclick="selectPaidOrder(${order.id})" 
             style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">#${order.order_number}</h6>
                    <p class="mb-1 text-muted small">Table ${order.table_number || 'N/A'}</p>
                    <p class="mb-0 text-muted small">${order.items ? order.items.length : 0} items</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success mb-1">Paid</span>
                    <p class="mb-0 text-primary fw-bold">₱${parseFloat(order.total_amount || 0).toFixed(2)}</p>
                </div>
            </div>
        </div>
    `).join('');
    
    container.html(html);
}

function selectPaidOrder(orderId) {
    // Remove previous selection
    $('#paidOrdersList .order-item').removeClass('bg-light border-start border-info border-3');
    
    // Add selection to clicked item
    $(`#paidOrdersList .order-item[data-order-id="${orderId}"]`).addClass('bg-light border-start border-info border-3');
    
    selectedPaidOrderId = orderId;
    
    // Load and display order details
    loadPaidOrderDetails(orderId);
}

function loadPaidOrderDetails(orderId) {
    $.ajax({
        url: `<?= base_url("restaurant/{$tenant->tenant_slug}/order-details") ?>/${orderId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayPaidOrderDetails(response.order);
            } else {
                showNotification('Failed to load order details', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showNotification('Failed to load order details', 'error');
        }
    });
}

function displayPaidOrderDetails(order) {
    const detailsHtml = `
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">Order Details - #${order.order_number}</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <p><strong>Table:</strong> ${order.table_number ? 'Table ' + order.table_number : 'N/A'}</p>
                        <p><strong>Customer:</strong> ${order.customer_name || 'Walk-in'}</p>
                        <p><strong>Order Time:</strong> ${formatDateTime(order.ordered_at)}</p>
                        <p><strong>Items:</strong> ${order.items ? order.items.length : 0}</p>
                    </div>
                </div>
                
                <hr>
                
                <h6>Items:</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${order.items && order.items.length > 0 ? order.items.map(item => `
                                <tr>
                                    <td>${item.menu_item_name || item.item_name}</td>
                                    <td>${item.quantity}</td>
                                    <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td class="text-end">₱${parseFloat(item.total_price || (item.unit_price * item.quantity)).toFixed(2)}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="4" class="text-center">No items</td></tr>'}
                        </tbody>
                    </table>
                </div>
                
                <hr>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="text-muted small">Subtotal</p>
                        <p>₱${parseFloat(order.subtotal || 0).toFixed(2)}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small">Total</p>
                        <h5 class="text-primary">₱${parseFloat(order.total_amount).toFixed(2)}</h5>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="printReceipt(${order.id})">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#paidOrderDetailsSection').html(detailsHtml);
}

function searchPaidOrders() {
    const searchTerm = $('#paidOrdersSearch').val().toLowerCase().trim();
    
    if (searchTerm === '') {
        renderPaidOrders(allPaidOrders);
        return;
    }
    
    // Search across multiple fields with match tracking
    const filtered = allPaidOrders.filter(order => {
        const orderNumber = (order.order_number || '').toLowerCase();
        const customerName = (order.customer_name || '').toLowerCase();
        const tableNumber = (order.table_number || '').toString().toLowerCase();
        const total = (order.total_amount || '').toString();
        const status = (order.status || '').toLowerCase();
        
        // Track which field(s) matched
        order.searchMatch = [];
        
        if (orderNumber.includes(searchTerm)) {
            order.searchMatch.push('Order #');
        }
        if (customerName.includes(searchTerm)) {
            order.searchMatch.push('Customer');
        }
        if (tableNumber.includes(searchTerm)) {
            order.searchMatch.push('Table');
        }
        if (total.includes(searchTerm)) {
            order.searchMatch.push('Amount');
        }
        if (status.includes(searchTerm)) {
            order.searchMatch.push('Status');
        }
        
        return order.searchMatch.length > 0;
    });
    
    renderPaidOrdersWithMatch(filtered);
}

function renderPaidOrdersWithMatch(orders) {
    const container = $('#paidOrdersList');
    container.empty();
    
    if (!orders || orders.length === 0) {
        container.html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fa-3x mb-3"></i>
                <p>No orders found</p>
                <small>Try searching for order number, customer name, or table number</small>
            </div>
        `);
        return;
    }
    
    const html = orders.map(order => `
        <div class="order-item p-3 border-bottom" 
             data-order-id="${order.id}" 
             onclick="selectPaidOrder(${order.id})" 
             style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h6 class="mb-0">#${order.order_number}</h6>
                        ${order.searchMatch ? `<small class="badge bg-warning text-dark">${order.searchMatch.join(', ')}</small>` : ''}
                    </div>
                    <p class="mb-1 text-muted small">
                        <i class="fas fa-user"></i> ${order.customer_name || 'Walk-in'} 
                        ${order.table_number ? `| <i class="fas fa-chair"></i> Table ${order.table_number}` : ''}
                    </p>
                    <p class="mb-0 text-muted small"><i class="fas fa-list"></i> ${order.items ? order.items.length : 0} items</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success mb-1">Paid</span>
                    <p class="mb-0 text-primary fw-bold">₱${parseFloat(order.total_amount || 0).toFixed(2)}</p>
                </div>
            </div>
        </div>
    `).join('');
    
    container.html(html);
}
