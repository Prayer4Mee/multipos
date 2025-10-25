<?php
// ============================================================================
// Kitchen Display System View
// app/Views/kitchen/display/index.php
// ============================================================================
?>
<?= $this->extend('layouts/main') ?>

<?php
// Ensure tenant variable is available
if (!isset($tenant)) {
    $tenant = (object)['slug' => 'jollibee'];
}
?>

<?= $this->section('head') ?>
<style>
    body {
        background-color: #1a1a1a;
        color: #ffffff;
    }
    .navbar {
        background-color: #333 !important;
    }
    .order-card {
        background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
        border-left: 4px solid #4facfe;
        color: white;
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
    .order-card.selected {
        border-left-color: #00ff88;
        box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
    }
    .order-card.urgent {
        border-left-color: #dc3545;
        animation: pulse 2s infinite;
    }
    .order-card.priority {
        border-left-color: #ffc107;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    .timer {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        font-size: 1.1em;
    }
    .timer.warning {
        color: #ffc107;
    }
    .timer.danger {
        color: #dc3545;
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 5px;
    }
    .info-item i {
        width: 16px;
    }
    .order-item {
        border-left: 3px solid #4facfe;
    }
    .item-name {
        font-weight: 500;
    }
    .kitchen-display-container {
        padding: 20px;
    }
    .card-header {
        background: rgba(255, 255, 255, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    .card-footer {
        background: rgba(255, 255, 255, 0.05);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="kitchen-display-container">
    <!-- Header with Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-primary" id="pending-count">0</h3>
                            <p class="mb-0">Pending Orders</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning" id="preparing-count">0</h3>
                            <p class="mb-0">Preparing</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success" id="ready-count">0</h3>
                            <p class="mb-0">Ready</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info timer" id="current-time"></h3>
                            <p class="mb-0">Current Time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Orders Grid -->
    <div class="row" id="orders-grid">
        <!-- Orders will be loaded here via AJAX -->
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Order Details - #<span id="modal-order-number"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="order-detail-content">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="btn-start-preparing" onclick="updateOrderStatus('preparing', currentOrderId)">
                    <i class="fas fa-play"></i> Start Preparing
                </button>
                <button type="button" class="btn btn-success" id="btn-mark-ready" onclick="updateOrderStatus('ready', currentOrderId)">
                    <i class="fas fa-check"></i> Mark Ready
                </button>
                <button type="button" class="btn btn-primary" id="btn-mark-served" onclick="updateOrderStatus('served', currentOrderId)">
                    <i class="fas fa-check-double"></i> Mark Served
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentOrderId = null;
    let refreshInterval;
    let currentOrders = []; // Store current orders data
    
    // Initialize kitchen display
    $(document).ready(function() {
        loadOrders();
        startAutoRefresh();
        updateClock();
        
        // Update clock every second
        setInterval(updateClock, 1000);
    });
    
    function loadOrders() {
        const tenantSlug = '<?= $tenant->slug ?? "jollibee" ?>';
        $.ajax({
            url: `<?= $base_url ?? base_url() ?>restaurant/${tenantSlug}/kitchen/ajax-orders`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(orders) {
                console.log('Kitchen orders loaded:', orders);
                
                // Check for new orders
                const previousOrderCount = currentOrders.length;
                const newOrderCount = orders.length;
                
                // Store previous order IDs for comparison
                const previousOrderIds = currentOrders.map(order => order.id);
                const newOrderIds = orders.map(order => order.id);
                
                // Find new orders
                const newOrders = orders.filter(order => !previousOrderIds.includes(order.id));
                
                // Update current orders
                currentOrders = orders;
                
                // Render orders and update statistics
                renderOrders(orders);
                updateStatistics(orders);
                
                // Show notification for new orders
                if (newOrders.length > 0) {
                    newOrders.forEach(order => {
                        showNotification(`New order #${order.order_number} from Table ${order.table_number}`, 'info');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load orders:', error);
                showNotification('Failed to load orders. Please refresh the page.', 'error');
            }
        });
    }
    
    function renderOrders(orders) {
        const grid = $('#orders-grid');
        grid.empty();
        
        if (orders.length === 0) {
            grid.append(`
                <div class="col-12 text-center">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>No Pending Orders</h4>
                            <p class="text-muted">All orders are complete! Great job! 🎉</p>
                            <small class="text-info">New orders will appear here automatically</small>
                        </div>
                    </div>
                </div>
            `);
            return;
        }
        
        orders.forEach(order => {
            const orderCard = createOrderCard(order);
            grid.append(orderCard);
        });
    }
    
    function createOrderCard(order) {
        const urgentClass = isOrderUrgent(order.ordered_at) ? 'urgent' : '';
        const priorityClass = order.priority_level === 'high' ? 'priority' : '';
        const elapsedTime = calculateElapsedTime(order.ordered_at);
        const timerClass = getTimerClass(elapsedTime);
        
        return `
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="card order-card ${urgentClass} ${priorityClass}" data-order-id="${order.id}" onclick="selectOrder(${order.id})">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>#${order.order_number}</strong>
                            <span class="badge bg-info ms-2">${order.order_type || 'Dine-in'}</span>
                            ${order.priority_level === 'high' ? '<span class="badge bg-warning ms-1">Priority</span>' : ''}
                        </div>
                        <div class="timer ${timerClass}">${elapsedTime}</div>
                    </div>
                    <div class="card-body">
                        <div class="order-info mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-item">
                                        <i class="fas fa-table text-primary"></i>
                                        <strong>Table:</strong> ${order.table_number || 'N/A'}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-item">
                                        <i class="fas fa-user text-info"></i>
                                        <strong>Waiter:</strong> ${order.waiter_name || 'N/A'}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-${getStatusColor(order.status)} fs-6">${order.status.toUpperCase()}</span>
                            </div>
                        </div>
                        
                        <div class="order-items">
                            <h6 class="text-warning mb-2"><i class="fas fa-utensils"></i> Order Items:</h6>
                            ${order.items.map(item => `
                                <div class="order-item mb-2 p-2 bg-dark rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="item-name">${item.name}</span>
                                        <span class="badge bg-${getItemStatusColor(item.status)}">${item.status}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        ${order.special_instructions ? `
                            <div class="special-instructions mt-3 p-2 bg-warning bg-opacity-10 rounded">
                                <strong class="text-warning"><i class="fas fa-exclamation-triangle"></i> Special Instructions:</strong>
                                <p class="mb-0 mt-1">${order.special_instructions}</p>
                            </div>
                        ` : ''}
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100">
                            <button class="btn btn-outline-light btn-sm" onclick="event.stopPropagation(); viewOrderDetails(${order.id})">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            ${order.kitchen_status === 'pending' ? `
                                <button class="btn btn-warning btn-sm" onclick="event.stopPropagation(); updateOrderStatus('preparing', ${order.id})">
                                    <i class="fas fa-play"></i> Start
                                </button>
                            ` : ''}
                            ${order.kitchen_status === 'preparing' ? `
                                <button class="btn btn-success btn-sm" onclick="event.stopPropagation(); updateOrderStatus('ready', ${order.id})">
                                    <i class="fas fa-check"></i> Ready
                                </button>
                            ` : ''}
                            ${order.kitchen_status === 'ready' ? `
                                <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); updateOrderStatus('served', ${order.id})">
                                    <i class="fas fa-check-double"></i> Served
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function updateOrderStatus(status, orderId) {
        if (!confirm(`Are you sure you want to mark this order as ${status}?`)) {
            return;
        }

        const tenantSlug = '<?= $tenant->slug ?? "jollibee" ?>';
        $.ajax({
            url: `<?= $base_url ?? base_url() ?>restaurant/${tenantSlug}/kitchen/update-order-status`,
            method: 'POST',
            data: {
                order_id: orderId,
                status: status,
                csrf_test_name: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotification(`Order #${orderId} marked as ${status}`, 'success');
                    
                    // Close modal if open
                    $('#orderDetailModal').modal('hide');
                    
                    // Refresh the display
                    loadOrders();
                } else {
                    showNotification('Failed to update status: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Failed to update order status', 'error');
            }
        });
    }

    function selectOrder(orderId) {
        // Remove previous selection
        $('.order-card').removeClass('selected');
        // Add selection to current card
        $(`.order-card[data-order-id="${orderId}"]`).addClass('selected');
        currentOrderId = orderId;
    }

    function viewOrderDetails(orderId) {
        currentOrderId = orderId;
        
        // Find the order data
        const order = findOrderById(orderId);
        if (!order) {
            showNotification('Order not found', 'error');
            return;
        }
        
        // Update modal title
        $('#modal-order-number').text(order.order_number);
        
        // Build order details content
        const orderDetails = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Order Information</h6>
                    <div class="info-item mb-2">
                        <i class="fas fa-table text-primary"></i>
                        <strong>Table:</strong> ${order.table_number || 'N/A'}
                    </div>
                    <div class="info-item mb-2">
                        <i class="fas fa-user text-info"></i>
                        <strong>Waiter:</strong> ${order.waiter_name || 'N/A'}
                    </div>
                    <div class="info-item mb-2">
                        <i class="fas fa-clock text-warning"></i>
                        <strong>Ordered At:</strong> ${new Date(order.ordered_at).toLocaleString()}
                    </div>
                    <div class="info-item mb-2">
                        <i class="fas fa-flag text-danger"></i>
                        <strong>Priority:</strong> ${order.priority_level || 'normal'}
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success">Order Status</h6>
                    <div class="status-badge mb-3">
                        <span class="badge bg-${getStatusColor(order.status)} fs-6">${order.status.toUpperCase()}</span>
                    </div>
                    <div class="info-item mb-2">
                        <i class="fas fa-hourglass-half text-warning"></i>
                        <strong>Elapsed Time:</strong> ${calculateElapsedTime(order.ordered_at)}
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="order-items-detail">
                <h6 class="text-warning mb-3"><i class="fas fa-utensils"></i> Order Items</h6>
                ${order.items.map(item => `
                    <div class="order-item-detail mb-3 p-3 bg-dark rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="item-name fs-6">${item.name}</span>
                            <span class="badge bg-${getItemStatusColor(item.status)}">${item.status}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
            
            ${order.special_instructions ? `
                <hr class="my-4">
                <div class="special-instructions-detail">
                    <h6 class="text-warning mb-3"><i class="fas fa-exclamation-triangle"></i> Special Instructions</h6>
                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                        <p class="mb-0">${order.special_instructions}</p>
                    </div>
                </div>
            ` : ''}
        `;
        
        // Update modal content
        $('#order-detail-content').html(orderDetails);
        
        // Show/hide action buttons based on current status
        updateModalButtons(order.status);
        
        // Show modal
        $('#orderDetailModal').modal('show');
    }
    
    function findOrderById(orderId) {
        return currentOrders.find(order => order.id == orderId);
    }
    
    function updateModalButtons(status) {
        // Hide all buttons first
        $('#btn-start-preparing, #btn-mark-ready, #btn-mark-served').hide();
        
        // Show appropriate buttons based on status
        switch (status) {
            case 'pending':
                $('#btn-start-preparing').show();
                break;
            case 'preparing':
                $('#btn-mark-ready').show();
                break;
            case 'ready':
                $('#btn-mark-served').show();
                break;
        }
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
    
    function updateStatistics(orders) {
        const stats = {
            pending: orders.filter(o => o.status === 'pending' || o.status === 'created').length,
            preparing: orders.filter(o => o.status === 'preparing').length,
            ready: orders.filter(o => o.status === 'ready').length
        };
        
        console.log('Kitchen Statistics:', stats);
        console.log('Orders for stats:', orders.map(o => ({id: o.id, status: o.status})));
        
        $('#pending-count').text(stats.pending);
        $('#preparing-count').text(stats.preparing);
        $('#ready-count').text(stats.ready);
    }
    
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        $('#current-time').text(timeString);
    }
    
    function calculateElapsedTime(orderedAt) {
        const now = new Date();
        const orderTime = new Date(orderedAt);
        const diffMs = now - orderTime;
        const diffMins = Math.floor(diffMs / 60000);
        
        const hours = Math.floor(diffMins / 60);
        const minutes = diffMins % 60;
        
        if (hours > 0) {
            return `${hours}h ${minutes}m`;
        } else {
            return `${minutes}m`;
        }
    }
    
    function getTimerClass(timeString) {
        const minutes = parseInt(timeString);
        if (minutes > 20) return 'danger';
        if (minutes > 15) return 'warning';
        return '';
    }
    
    function isOrderUrgent(orderedAt) {
        const now = new Date();
        const orderTime = new Date(orderedAt);
        const diffMins = (now - orderTime) / 60000;
        return diffMins > 20; // Orders older than 20 minutes are urgent
    }
    
    function getStatusColor(status) {
        switch (status) {
            case 'pending': return 'warning';
            case 'preparing': return 'info';
            case 'ready': return 'success';
            default: return 'secondary';
        }
    }
    
    function getItemStatusColor(status) {
        return getStatusColor(status);
    }
    
    function startAutoRefresh() {
        refreshInterval = setInterval(loadOrders, 3000); // Refresh every 3 seconds for better real-time sync
    }
    
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }
    
    // Stop refresh when page is not visible (optimization)
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
</script>
<?= $this->endSection() ?>
<?php
// End of file
?>