<?php
// =====================================
// app/Views/kitchen/orders/index.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Kitchen Header -->
    <div class="kitchen-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>👨‍🍳 Kitchen Orders Management</h2>
                <p class="text-muted">
                    Current time: <span id="current-time"><?= date('H:i:s') ?></span> | 
                    Chef: <?= $current_user['full_name'] ?>
                </p>
            </div>
            <div class="kitchen-stats">
                <div class="stat-item">
                    <span class="stat-value"><?= $pending_orders_count ?></span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $preparing_orders_count ?></span>
                    <span class="stat-label">Preparing</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="avg-prep-time"><?= $avg_prep_time ?>m</span>
                    <span class="stat-label">Avg. Time</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Queue Tabs -->
    <div class="order-tabs">
        <ul class="nav nav-tabs" id="order-queue-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pending-orders">
                    🕐 Pending Orders <span class="badge bg-warning ms-2"><?= $pending_orders_count ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#preparing-orders">
                    👨‍🍳 Preparing <span class="badge bg-info ms-2"><?= $preparing_orders_count ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#ready-orders">
                    ✅ Ready <span class="badge bg-success ms-2"><?= $ready_orders_count ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#completed-orders">
                    📋 Completed Today <span class="badge bg-secondary ms-2"><?= $completed_orders_count ?></span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Order Queue Content -->
    <div class="tab-content" id="order-queue-content">
        
        <!-- Pending Orders -->
        <div class="tab-pane fade show active" id="pending-orders">
            <div class="orders-grid" id="pending-orders-grid">
                <?php foreach ($pending_orders as $order): ?>
                <div class="order-card pending" data-order-id="<?= $order['id'] ?>">
                    <div class="order-header">
                        <div class="order-number">Order #<?= $order['order_number'] ?></div>
                        <div class="order-time">
                            <i class="fas fa-clock"></i>
                            <span class="time-elapsed" data-time="<?= $order['created_at'] ?>">
                                <?= $order['elapsed_time'] ?>
                            </span>
                        </div>
                        <?php if ($order['priority'] === 'urgent'): ?>
                        <div class="priority-badge urgent">🚨 URGENT</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-info">
                        <div class="table-info">
                            <?php if ($order['table_number']): ?>
                            <i class="fas fa-chair"></i> Table <?= $order['table_number'] ?>
                            <?php else: ?>
                            <i class="fas fa-shopping-bag"></i> <?= ucfirst($order['order_type']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="customer-info">
                            <?= $order['customer_name'] ?: 'Walk-in Customer' ?>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div class="item-quantity"><?= $item['quantity'] ?>x</div>
                            <div class="item-details">
                                <div class="item-name"><?= $item['name'] ?></div>
                                <?php if (!empty($item['special_instructions'])): ?>
                                <div class="special-instructions">
                                    <i class="fas fa-sticky-note"></i> <?= $item['special_instructions'] ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($item['modifications'])): ?>
                                <div class="modifications">
                                    <?php foreach ($item['modifications'] as $mod): ?>
                                    <span class="mod-tag"><?= $mod ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-status">
                                <button class="item-status-btn ready" onclick="markItemReady('<?= $item['id'] ?>')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-actions">
                        <button class="action-btn start-cooking" onclick="startCooking('<?= $order['id'] ?>')">
                            <i class="fas fa-play"></i> Start Cooking
                        </button>
                        <button class="action-btn view-details" onclick="viewOrderDetails('<?= $order['id'] ?>')">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Preparing Orders -->
        <div class="tab-pane fade" id="preparing-orders">
            <div class="orders-grid" id="preparing-orders-grid">
                <?php foreach ($preparing_orders as $order): ?>
                <div class="order-card preparing" data-order-id="<?= $order['id'] ?>">
                    <div class="order-header">
                        <div class="order-number">Order #<?= $order['order_number'] ?></div>
                        <div class="cooking-timer">
                            <i class="fas fa-stopwatch"></i>
                            <span id="timer-<?= $order['id'] ?>" data-start="<?= $order['cooking_started_at'] ?>">
                                <?= $order['cooking_time'] ?>
                            </span>
                        </div>
                        <div class="assigned-chef">
                            👨‍🍳 <?= $order['assigned_chef'] ?: 'You' ?>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <div class="table-info">
                            <?php if ($order['table_number']): ?>
                            <i class="fas fa-chair"></i> Table <?= $order['table_number'] ?>
                            <?php else: ?>
                            <i class="fas fa-shopping-bag"></i> <?= ucfirst($order['order_type']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="estimated-time">
                            ⏱️ Est. completion: <?= $order['estimated_completion'] ?>
                        </div>
                    </div>
                    
                    <div class="cooking-progress">
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $order['completion_percentage'] ?>%"></div>
                        </div>
                        <small><?= $order['completion_percentage'] ?>% Complete</small>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item <?= $item['status'] ?>">
                            <div class="item-quantity"><?= $item['quantity'] ?>x</div>
                            <div class="item-details">
                                <div class="item-name"><?= $item['name'] ?></div>
                                <?php if (!empty($item['special_instructions'])): ?>
                                <div class="special-instructions">
                                    <i class="fas fa-sticky-note"></i> <?= $item['special_instructions'] ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-status">
                                <?php if ($item['status'] === 'ready'): ?>
                                <span class="status-badge ready">✅ Ready</span>
                                <?php else: ?>
                                <button class="item-status-btn ready" onclick="markItemReady('<?= $item['id'] ?>')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-actions">
                        <button class="action-btn complete-order" onclick="completeOrder('<?= $order['id'] ?>')" <?= $order['all_items_ready'] ? '' : 'disabled' ?>>
                            <i class="fas fa-flag-checkered"></i> Order Ready
                        </button>
                        <button class="action-btn add-time" onclick="addMoreTime('<?= $order['id'] ?>')">
                            <i class="fas fa-clock"></i> +5 min
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Ready Orders -->
        <div class="tab-pane fade" id="ready-orders">
            <div class="orders-grid" id="ready-orders-grid">
                <?php foreach ($ready_orders as $order): ?>
                <div class="order-card ready" data-order-id="<?= $order['id'] ?>">
                    <div class="order-header">
                        <div class="order-number">Order #<?= $order['order_number'] ?></div>
                        <div class="ready-time">
                            <i class="fas fa-check-circle"></i>
                            Ready for <?= $order['ready_duration'] ?>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <div class="table-info">
                            <?php if ($order['table_number']): ?>
                            <i class="fas fa-chair"></i> Table <?= $order['table_number'] ?>
                            <?php else: ?>
                            <i class="fas fa-shopping-bag"></i> <?= ucfirst($order['order_type']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="pickup-status">
                            <?php if ($order['order_type'] === 'delivery'): ?>
                            🛵 Waiting for driver pickup
                            <?php else: ?>
                            🔔 Waiting for customer pickup
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-summary">
                        <div class="item-count">
                            <?= $order['total_items'] ?> items ready
                        </div>
                        <div class="order-total">
                            Total: ₱<?= number_format($order['total_amount'], 2) ?>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="action-btn notify-server" onclick="notifyServer('<?= $order['id'] ?>')">
                            <i class="fas fa-bell"></i> Notify Server
                        </button>
                        <button class="action-btn mark-served" onclick="markAsServed('<?= $order['id'] ?>')">
                            <i class="fas fa-hand-paper"></i> Mark Served
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Completed Orders -->
        <div class="tab-pane fade" id="completed-orders">
            <div class="completed-orders-list">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Time</th>
                                <th>Items</th>
                                <th>Prep Time</th>
                                <th>Chef</th>
                                <th>Customer</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completed_orders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_number'] ?></td>
                                <td>
                                    <small>
                                        <?= date('H:i', strtotime($order['created_at'])) ?> - 
                                        <?= date('H:i', strtotime($order['completed_at'])) ?>
                                    </small>
                                </td>
                                <td><?= $order['total_items'] ?> items</td>
                                <td>
                                    <span class="prep-time <?= $order['prep_time_class'] ?>">
                                        <?= $order['prep_time_minutes'] ?>m
                                    </span>
                                </td>
                                <td><?= $order['chef_name'] ?></td>
                                <td><?= $order['customer_name'] ?: 'Walk-in' ?></td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="order-details-content">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printKitchenTicket()">
                    <i class="fas fa-print"></i> Print Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.kitchen-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
}

.kitchen-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    color: #ffc107;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.order-tabs .nav-tabs {
    border-bottom: 3px solid #e9ecef;
}

.order-tabs .nav-link {
    font-weight: 600;
    color: #495057;
    border: none;
    padding: 15px 20px;
}

.order-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0;
}

.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    padding: 20px 0;
}

.order-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.order-card.pending {
    border-left: 5px solid #ffc107;
}

.order-card.preparing {
    border-left: 5px solid #007bff;
}

.order-card.ready {
    border-left: 5px solid #28a745;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.order-header {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    position: relative;
}

.order-number {
    font-size: 1.2rem;
    font-weight: bold;
    color: #333;
}

.order-time, .cooking-timer {
    font-size: 0.9rem;
    color: #666;
    margin-top: 5px;
}

.cooking-timer {
    color: #007bff;
    font-weight: bold;
}

.priority-badge.urgent {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.5; }
}

.order-info {
    padding: 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.table-info, .customer-info {
    margin-bottom: 5px;
    font-weight: 600;
}

.cooking-progress {
    padding: 10px 15px;
    background: #e3f2fd;
}

.progress {
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    background: #e9ecef;
}

.progress-bar {
    background: linear-gradient(90deg, #007bff, #28a745);
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.order-items {
    padding: 15px;
}

.order-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
}

.order-item.ready {
    background: #d4edda;
    border-left: 4px solid #28a745;
}

.item-quantity {
    background: var(--primary-color);
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    flex-shrink: 0;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.special-instructions {
    background: #fff3cd;
    color: #856404;
    padding: 5px 8px;
    border-radius: 5px;
    font-size: 0.85rem;
    margin-top: 5px;
}

.modifications {
    margin-top: 5px;
}

.mod-tag {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.75rem;
    margin-right: 5px;
}

.item-status-btn {
    background: #28a745;
    color: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.item-status-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.status-badge.ready {
    background: #28a745;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
}

.order-actions {
    padding: 15px;
    background: #f8f9fa;
    display: flex;
    gap: 10px;
}

.action-btn {
    flex: 1;
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-btn.start-cooking {
    background: #007bff;
    color: white;
}

.action-btn.complete-order {
    background: #28a745;
    color: white;
}

.action-btn.notify-server {
    background: #ffc107;
    color: #333;
}

.action-btn.mark-served {
    background: #6c757d;
    color: white;
}

.action-btn.view-details {
    background: #17a2b8;
    color: white;
}

.action-btn.add-time {
    background: #fd7e14;
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.action-btn:disabled {
    background: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.completed-orders-list {
    padding: 20px;
}

.prep-time.fast {
    color: #28a745;
    font-weight: bold;
}

.prep-time.normal {
    color: #007bff;
}

.prep-time.slow {
    color: #dc3545;
    font-weight: bold;
}

/* Real-time updates */
.time-elapsed {
    font-weight: bold;
}

.urgent-order {
    animation: urgentPulse 2s infinite;
}

@keyframes urgentPulse {
    0% { box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
    50% { box-shadow: 0 5px 20px rgba(220, 53, 69, 0.3); }
    100% { box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .orders-grid {
        grid-template-columns: 1fr;
        padding: 10px;
    }
    
    .kitchen-stats {
        gap: 15px;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .order-actions {
        flex-direction: column;
    }
}
</style>

<script>
let kitchenSocket;
let currentOrderId;

$(document).ready(function() {
    // Initialize real-time updates
    initializeRealTimeUpdates();
    
    // Start timers
    startAllTimers();
    
    // Auto-refresh every 30 seconds
    setInterval(refreshOrders, 30000);
});

function initializeRealTimeUpdates() {
    // Initialize WebSocket connection for real-time updates
    if (typeof io !== 'undefined') {
        kitchenSocket = io('/kitchen');
        
        kitchenSocket.on('new_order', function(order) {
            addNewOrderToQueue(order);
            showNotification('New order received!', 'info');
        });
        
        kitchenSocket.on('order_updated', function(order) {
            updateOrderCard(order);
        });
        
        kitchenSocket.on('order_cancelled', function(orderId) {
            removeOrderCard(orderId);
            showNotification('Order cancelled', 'warning');
        });
    }
}

function startAllTimers() {
    // Update elapsed time for pending orders
    $('.time-elapsed').each(function() {
        const startTime = new Date($(this).data('time'));
        updateElapsedTime($(this), startTime);
    });
    
    // Update cooking timers
    $('[id^="timer-"]').each(function() {
        const startTime = new Date($(this).data('start'));
        updateCookingTimer($(this), startTime);
    });
    
    // Update current time
    updateCurrentTime();
}

function updateElapsedTime(element, startTime) {
    setInterval(function() {
        const now = new Date();
        const elapsed = Math.floor((now - startTime) / 1000 / 60); // minutes
        
        element.text(`${elapsed}m ago`);
        
        // Add urgency styling for orders over 15 minutes
        if (elapsed > 15) {
            element.closest('.order-card').addClass('urgent-order');
        }
    }, 1000);
}

function updateCookingTimer(element, startTime) {
    setInterval(function() {
        const now = new Date();
        const elapsed = Math.floor((now - startTime) / 1000 / 60); // minutes
        
        element.text(`${elapsed}m`);
        
        // Update progress bar if exists
        const progressBar = element.closest('.order-card').find('.progress-bar');
        if (progressBar.length) {
            const estimated = 30; // Estimated cooking time in minutes
            const progress = Math.min((elapsed / estimated) * 100, 100);
            progressBar.css('width', `${progress}%`);
        }
    }, 1000);
}

function updateCurrentTime() {
    setInterval(function() {
        $('#current-time').text(new Date().toLocaleTimeString());
    }, 1000);
}

function startCooking(orderId) {
    $.ajax({
        url: `<?= base_url('api/kitchen/orders/') ?>${orderId}/start`,
        method: 'POST',
        success: function(response) {
            if (response.success) {
                // Move order to preparing tab
                moveOrderCard(orderId, 'pending-orders-grid', 'preparing-orders-grid');
                updateTabCounts();
                showNotification('Order cooking started!', 'success');
            }
        },
        error: function() {
            showNotification('Error starting order', 'error');
        }
    });
}

function markItemReady(itemId) {
    $.ajax({
        url: `<?= base_url('api/kitchen/items/') ?>${itemId}/ready`,
        method: 'POST',
        success: function(response) {
            if (response.success) {
                // Update item status in UI
                $(`.order-item[data-item-id="${itemId}"]`).addClass('ready');
                $(`.order-item[data-item-id="${itemId}"] .item-status-btn`).replaceWith(
                    '<span class="status-badge ready">✅ Ready</span>'
                );
                
                // Check if all items in order are ready
                const orderId = response.order_id;
                checkOrderCompletion(orderId);
                
                showNotification('Item marked as ready!', 'success');
            }
        },
        error: function() {
            showNotification('Error updating item status', 'error');
        }
    });
}

function checkOrderCompletion(orderId) {
    const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
    const totalItems = orderCard.find('.order-item').length;
    const readyItems = orderCard.find('.order-item.ready').length;
    
    if (totalItems === readyItems) {
        orderCard.find('.complete-order').prop('disabled', false);
        showNotification('All items ready! Order can be completed.', 'info');
    }
}

function completeOrder(orderId) {
    $.ajax({
        url: `<?= base_url('api/kitchen/orders/') ?>${orderId}/complete`,
        method: 'POST',
        success: function(response) {
            if (response.success) {
                // Move to ready orders
                moveOrderCard(orderId, 'preparing-orders-grid', 'ready-orders-grid');
                updateTabCounts();
                showNotification('Order completed and ready for pickup!', 'success');
                
                // Notify servers
                notifyServers(orderId);
            }
        },
        error: function() {
            showNotification('Error completing order', 'error');
        }
    });
}

function addMoreTime(orderId) {
    $.ajax({
        url: `<?= base_url('api/kitchen/orders/') ?>${orderId}/add-time`,
        method: 'POST',
        data: { additional_minutes: 5 },
        success: function(response) {
            if (response.success) {
                showNotification('Added 5 minutes to estimated time', 'info');
            }
        },
        error: function() {
            showNotification('Error updating time estimate', 'error');
        }
    });
}

function notifyServer(orderId) {
    $.ajax({
        url: `<?= base_url('api/kitchen/orders/') ?>${orderId}/notify-server`,
        method: 'POST',
        success: function(response) {
            if (response.success) {
                showNotification('Server notified!', 'success');
            }
        },
        error: function() {
            showNotification('Error notifying server', 'error');
        }
    });
}

function markAsServed(orderId) {
    $.ajax({
        url: `<?= base_url('api/kitchen/orders/') ?>${orderId}/served`,
        method: 'POST',
        success: function(response) {
            if (response.success) {
                // Remove from ready orders
                $(`.order-card[data-order-id="${orderId}"]`).fadeOut(500, function() {
                    $(this).remove();
                });
                updateTabCounts();
                showNotification('Order marked as served!', 'success');
            }
        },
        error: function() {
            showNotification('Error marking order as served', 'error');
        }
    });
}

function viewOrderDetails(orderId) {
    currentOrderId = orderId;
    
    $.ajax({
        url: `<?= base_url('api/kitchen/orders/') ?>${orderId}/details`,
        method: 'GET',
        success: function(response) {
            $('#order-details-content').html(response.html);
            $('#orderDetailsModal').modal('show');
        },
        error: function() {
            showNotification('Error loading order details', 'error');
        }
    });
}

function printKitchenTicket() {
    if (currentOrderId) {
        $.ajax({
            url: `<?= base_url('api/kitchen/orders/') ?>${currentOrderId}/print`,
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    showNotification('Kitchen ticket sent to printer!', 'success');
                } else {
                    showNotification('Error printing ticket: ' + response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error printing kitchen ticket', 'error');
            }
        });
    }
}

function moveOrderCard(orderId, fromGrid, toGrid) {
    const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
    orderCard.fadeOut(300, function() {
        orderCard.detach().appendTo(`#${toGrid}`).fadeIn(300);
    });
}

function updateTabCounts() {
    // Update badge counts on tabs
    $('.nav-link').each(function() {
        const tabId = $(this).attr('href');
        const count = $(tabId + '-grid .order-card').length || $(tabId + ' tbody tr').length;
        $(this).find('.badge').text(count);
    });
}

function addNewOrderToQueue(order) {
    // Add new order to pending queue
    // This would render the order card HTML and append to pending-orders-grid
    const orderHtml = renderOrderCard(order);
    $('#pending-orders-grid').prepend(orderHtml);
    updateTabCounts();
}

function updateOrderCard(order) {
    // Update existing order card with new data
    const orderCard = $(`.order-card[data-order-id="${order.id}"]`);
    // Update specific elements within the card
}

function removeOrderCard(orderId) {
    $(`.order-card[data-order-id="${orderId}"]`).fadeOut(500, function() {
        $(this).remove();
        updateTabCounts();
    });
}

function refreshOrders() {
    // Refresh order data without page reload
    $.get('<?= base_url('api/kitchen/orders/refresh') ?>', function(data) {
        // Update counts and any new orders
        updateTabCounts();
    });
}

function notifyServers(orderId) {
    // Send notification to servers that order is ready
    if (kitchenSocket) {
        kitchenSocket.emit('order_ready', { orderId: orderId });
    }
}

function showNotification(message, type = 'info') {
    const alertClass = type === 'error' ? 'alert-danger' : 
                      type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show kitchen-notification" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('.container-fluid').prepend(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}

// Keyboard shortcuts for kitchen efficiency
$(document).keydown(function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.which) {
            case 49: // Ctrl+1 - Switch to pending orders
                $('.nav-link[href="#pending-orders"]').tab('show');
                e.preventDefault();
                break;
            case 50: // Ctrl+2 - Switch to preparing orders
                $('.nav-link[href="#preparing-orders"]').tab('show');
                e.preventDefault();
                break;
            case 51: // Ctrl+3 - Switch to ready orders
                $('.nav-link[href="#ready-orders"]').tab('show');
                e.preventDefault();
                break;
        }
    }
});
</script>

<style>
.kitchen-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
}
</style>
<?= $this->endSection() ?>