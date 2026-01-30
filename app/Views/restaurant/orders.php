<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="orders-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-clipboard-check"></i> Orders Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" onclick="location.href='<?= base_url('restaurant/' . $tenant_slug . '/new-order') ?>'">
                <i class="fas fa-plus"></i> New Order
            </button>
        </div>
    </div>

    <!-- Order Status Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="statusFilter" id="all" value="all" checked>
                <label class="btn btn-outline-primary" for="all">All Orders</label>

                <input type="radio" class="btn-check" name="statusFilter" id="pending" value="pending">
                <label class="btn btn-outline-warning" for="pending">Pending</label>

                <input type="radio" class="btn-check" name="statusFilter" id="preparing" value="preparing">
                <label class="btn btn-outline-info" for="preparing">Preparing</label>

                <input type="radio" class="btn-check" name="statusFilter" id="ready" value="ready">
                <label class="btn btn-outline-success" for="ready">Ready</label>

                <input type="radio" class="btn-check" name="statusFilter" id="completed" value="completed">
                <label class="btn btn-outline-secondary" for="completed">Completed</label>
                <!-- Added this two statusFilter -->
                <input type="radio" class="btn-check" name="statusFilter" id="served" value="served">
                <label class="btn btn-outline-success" for="served">Served</label>

                <input type="radio" class="btn-check" name="statusFilter" id="cancelled" value="cancelled">
                <label class="btn btn-outline-danger" for="cancelled">Cancelled</label>
            </div>
        </div>
    </div>

    <!-- Orders List -->

    <div class="row" id="orders-list-container">

<?php if (empty($orders)): ?>
    <div class="col-12 text-center p-5 text-muted">
        No orders found.
    </div>
<?php endif; ?>

<?php foreach ($orders as $order): ?>

<?php
    // Determine actual status based on served + paid logic
    // 1 COMPLETED always
    if ($order->status === 'completed') {
        $displayStatus = 'completed';
        $badgeText = 'Completed ✓';
        $badgeClass = 'success';
    // } if ($order->status === 'served' && $order->payment_status === 'paid') {
    //     $displayStatus = 'completed';
    //     $badgeText = 'Completed ✓';
    //     $badgeClass = 'success';
    // 2. Served but not paying yet!
    } elseif ($order->status === 'served' && $order->payment_status === 'pending') {
        $displayStatus = 'served-unpaid';
        $badgeText = 'Served (Unpaid)';
        $badgeClass = 'warning';
    } elseif ($order->status !== 'served' && $order->payment_status === 'paid') {
        $displayStatus = 'paid-unserved';
        $badgeText = 'Paid (Not Served)';
        $badgeClass = 'info';
    // 3. Paid but not yet served
    } elseif (
        $order->payment_status === 'paid' && !in_array($order->status, ['served', 'completed'])) {
        $displayStatus = 'paid-unserved';
        $badgeText = 'Paid (Not Served)';
        $badgeClass = 'info';
    // 4. Special Case = refund
    } elseif ($order->payment_status === 'refunded') {
        $displayStatus = 'refunded';
        $badgeText = 'Refunded';
        $badgeClass = 'danger';
    // 5. Work flow: pending->preparing->ready->served
    } else {
        $statusMap = [
            'pending'   => ['warning', 'Pending'],
            'preparing' => ['info', 'Preparing'],
            'ready'     => ['success', 'Ready'],
            'served'    => ['success', 'Served'],
            'cancelled' => ['danger', 'Cancelled']
        ];
        [$badgeClass, $badgeText] = $statusMap[$order->status] ?? ['dark', ucfirst($order->status)];
        $displayStatus = $order->status;
    }
?>
<!-- Detection Filter -->
<div class="col-md-6 col-lg-4 mb-3">
    <div class="card order-card"
         data-order-id="<?= esc($order->id) ?>"
         data-status="<?= esc($displayStatus) ?>">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Order #<?= esc($order->id) ?></h6>
            <span class="badge bg-<?= $badgeClass ?>">
                <?= $badgeText ?>
            </span>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-8">
                    <p class="mb-1"><strong>Table <?= esc($order->table_id) ?></strong></p>
                    <p class="mb-1 text-muted small"><?= esc($order->guest_count) ?> guests</p>
                    <p class="mb-1 text-muted small">
                        Ordered: <?= date('g:i A', strtotime($order->created_at)) ?>
                    </p>
                </div>
                <div class="col-4 text-end">
                    <h6 class="text-primary mb-0">
                        ₱<?= number_format($order->total_amount, 2) ?>
                    </h6>
                </div>
            </div>

            <hr class="my-2">

            <div class="order-items">
                <?php foreach ($order->items as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">
                            <?= esc($item->menu_item_name) ?> x<?= esc($item->quantity) ?>
                        </span>
                        <span class="small">
                            ₱<?= number_format($item->unit_price ?? 0, 2) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            
<!-- Take note: This is where you can change the status of your orders! -->
            <!-- Wrapper for Cancelled Button -->
            <?php $isCompleted = ($order->status === 'completed');?>
                
            <div class="mt-3">
                <div class="btn-group w-100">
                    <?php if (!$isCompleted && $order->status !== 'cancelled'): ?>

                        <?php if ($order->status === 'pending'): ?>
                            <button class="btn btn-sm btn-outline-info"
                                    onclick="startPreparing(<?= $order->id ?>)">
                                <i class="fas fa-play"></i> Start
                            </button>

                        <?php elseif ($order->status === 'preparing'): ?>
                            <button class="btn btn-sm btn-outline-success"
                                    onclick="markReady(<?= $order->id ?>)">
                                <i class="fas fa-check"></i> Ready
                            </button>

                        <?php elseif ($order->status === 'ready'): ?>
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="markServed(<?= $order->id ?>)">
                                <i class="fas fa-check-circle"></i> Served
                            </button>
                        <?php endif; ?>
                    <?php endif; ?> <!-- end cancelled wrapper -->
            <!-- Note: Always show this!!!!! -->
                        <button class="btn btn-sm btn-outline-primary"
                                onclick="viewOrder(<?= $order->id ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>

                        <!-- Always show print receipt button -->
                        <button class="btn btn-sm btn-outline-info"
                                onclick="reprintReceipt(<?= $order->id ?>)">
                            <i class="fas fa-print"></i> Receipt
                        </button>

                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- New Order Modal -->
<div class="modal fade" id="newOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newOrderForm">
                    <div class="row">
                        <!-- Oder Type -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Order Type</label>
                                <select class="form-select" name="order_type" required>
                                    <option value="">Select Order Type</option>
                                    <option value="dine_in" selected>Dine In</option>
                                    <option value="takeout">Takeout</option>
                                    <option value="delivery">Delivery</option>
                                    <option value="drive_through">Drive Through</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Table Number</label>
                                <select class="form-select" name="table_id" required>
                                    <option value="">Select Table</option>
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
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Number of Guests</label>
                                <input type="number" class="form-control" name="guests" min="1" max="12" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer Name (Optional)</label>
                        <input type="text" class="form-control" name="customer_name" placeholder="Enter customer name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Special Instructions</label>
                        <textarea class="form-control" name="instructions" rows="3" placeholder="Any special requests or instructions"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createOrder()">Create Order</button>
            </div>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewOrderContent">
        <!-- Order details will be injected here -->
        <p class="text-center text-muted">Loading...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>

    // Start with Persistent Master Copy
let allOrderCards = [];
$(document).ready(function() {
    // Save all order cards on page load (array)
    allOrderCards = $('#orders-list-container > .col-md-6').toArray();
    // Status filter handler
    $('input[name="statusFilter"]').change(function() {
        const status = $(this).val();
        filterOrders(status);
    });
});

function filterOrders(status) {
    const container = $('#orders-list-container');
    container.empty(); // Clear current cards
    // const cards = $('.order-card').detach(); // Remove all cards temporarily, if not included in filter

    let filtered = allOrderCards.filter(function(col){
        const cardStatus = $(col).find('.order-card').data('status');
        return status === 'all' || cardStatus === status;
    })

    // Optional: sort filtered cards by order ID (or created_at)
    filtered.sort(function(a, b) {
        const idA = $(a).find('.order-card').data('order-id');
        const idB = $(b).find('.order-card').data('order-id');
        return idA - idB;
    });

    // Cancelled goes last if showing all
    if (status === 'all'){
        filtered.sort(function(a, b){
            const aStatus = $(a).find('.order-card').data('status');
            const bStatus = $(b).find('.order-card').data('status');
            if(aStatus === 'cancelled') return 1;
            if(bStatus === 'cancelled') return -1;
            return $(a).find('.order-card').data('order-id') - $(b).find('.order-card').data('order-id');
        })
    } 


    if(status === 'all'){
    filtered = allOrderCards.sort(function(a,b){
        // cancelled goes last
        if($(a).data('status') === 'cancelled') return 1;
        if($(b).data('status') === 'cancelled') return -1;
        return $(a).data('order-id') - $(b).data('order-id');
        });
    }

    // Append the sorted/filtered cards back to container
    // Isn't this problematic? key words: problem, errors, prone to error
    container.empty().append(filtered);
}
// Helper function to escape HTML and prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
function markReady(orderId) {
    if (confirm('Mark order #' + orderId + ' as ready?')) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_slug}/update-order-status") ?>',
            method: 'POST',
            data: {
                order_id: orderId,
                status: 'ready'
            },
        success: function (response) {
        // Update UI
        // 1. Update Card
        const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
        // 2. Update Badge
        orderCard.find('.badge')
            .removeClass('bg-info')
            .addClass('bg-success')
            .text('Ready');
        // 3. Update Data Attribute
        orderCard.attr('data-status', 'ready');

        // 4. Update buttons
        const buttonGroup = orderCard.find('.btn-group');
        buttonGroup.html(`
            <button class="btn btn-sm btn-outline-secondary" onclick="(${orderId})">
                <i class="fas fa-check-circle"></i> Served
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${orderId})">
                <i class="fas fa-eye"></i> View
            </button>
        `);
        },
        error:function(xhr) {
            alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON .error : 'Server Error'));
            }
        });
    }
}


function startPreparing(orderId) {
    if (confirm('Start preparing order #' + orderId + '?')) {
       $.ajax({
            url: '<?= base_url("restaurant/{$tenant_slug}/update-order-status") ?>',
            method: 'POST',
            data: {
                order_id: orderId,
                status: 'preparing'
            },
        success: function (response) {
        // Update UI
        // 1. Update Card
        const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
        // 2. Update Badge
        orderCard.find('.badge')
        .removeClass('bg-warning')
        .addClass('bg-info')
        .text('Preparing');
        // 3. Update Data Attribute
        orderCard.attr('data-status', 'preparing');
        
        // 4. Update buttons
        const buttonGroup = orderCard.find('.btn-group');
        buttonGroup.html(`
            <button class="btn btn-sm btn-outline-success" onclick="markReady(${orderId})">
                <i class="fas fa-check"></i> Ready
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${orderId})">
                <i class="fas fa-eye"></i> View
            </button>
        `);
        },
        error:function(xhr) {
            alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON .error : 'Server Error'));
            }
        });
    }
}
function markServed(orderId) {
    if (confirm('Mark order #' + orderId + ' as served?')) {
        $.ajax({
            url: '<?= base_url("restaurant/{$tenant_slug}/update-order-status") ?>',
            method: 'POST',
            data: {
                order_id: orderId,
                status: 'served'
            },
            success: function (response) {
                const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
                
                // Reload order details to check payment status
                $.ajax({
                    url: `<?= base_url("restaurant/{$tenant_slug}/order-details") ?>/${orderId}`,
                    method: 'GET',
                    success: function(resp) {
                        const order = resp.order;
                        let badgeText = 'Served';
                        let badgeClass = 'success';
                        let showPaymentButton = true;
                        
                        // Check if both served AND paid
                        if (order.payment_status === 'paid') {
                            badgeText = 'Completed ✓';
                            badgeClass = 'success';
                            showPaymentButton = false;
                        }
                        
                        // Update badge
                        orderCard.find('.badge')
                            .removeClass('bg-warning bg-info bg-secondary')
                            .addClass(`bg-${badgeClass}`)
                            .text(badgeText);
                        
                        // Update data attribute
                        orderCard.attr('data-status', order.payment_status === 'paid' ? 'completed' : 'served');
                        
                        // Update buttons
                        const buttonGroup = orderCard.find('.btn-group');
                        let buttonHtml = `
                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${orderId})">
                                <i class="fas fa-eye"></i> View
                            </button>
                        `;
                        
                        if (showPaymentButton && order.payment_status !== 'paid') {
                            buttonHtml += `
                                <button class="btn btn-sm btn-outline-primary" onclick="proceedToPayment(${orderId})">
                                    <i class="fas fa-credit-card"></i> Payment
                                </button>
                            `;
                        }
                        
                        buttonHtml += `
                            <button class="btn btn-sm btn-outline-info" onclick="reprintReceipt(${orderId})">
                                <i class="fas fa-print"></i> Receipt
                            </button>
                        `;
                        
                        buttonGroup.html(buttonHtml);
                        
                        showNotification('Order marked as served', 'success');
                    },
                    error: function() {
                        showNotification('Error refreshing order status', 'error');
                    }
                });
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Server Error'));
            }
        });
    }
}

function proceedToPayment(orderId) {
    window.location.href = `<?= base_url("restaurant/{$tenant_slug}/payment") ?>/${orderId}`;
}

// Allows you to view details to the right side of the screen when you click something
function viewOrder(orderId) {
    // Show loading state
    $('#viewOrderContent').html('<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading Order Details...</p>');

    $.ajax({
        url: '<?= base_url("restaurant/{$tenant_slug}/order-details") ?>/' + orderId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("AJAX Response:", response);  // ← ADD THIS
            console.log("Order object:", response.order);  // ← ADD THIS
            console.log("Table number:", response.order.table_number);  // ← ADD THIS
            console.log("Items:", response.order.items);  // ← ADD THIS
            console.log("Items length:", response.order.items.length);  // ← ADD THIS
            
            if (response.success && response.order) {
                const order = response.order;

                // Build items table from backend data
                const itemsRows = order.items.map(item => `
                    <tr>
                        <td>${escapeHtml(item.menu_item_name)}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td class="text-end">₱${(parseFloat(item.unit_price) * item.quantity).toFixed(2)}</td>
                    </tr>
                `).join('');

                // Format the order Time
                const orderTime = new Date(order.created_at).toLocaleString();

                // Build the modal HTML
                const html = `
                    <div class="order-details-modal">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Order Details - #ORD${orderId}</strong></p>
                                <p><strong>Table:</strong> ${order.table_number ? 'Table ' + order.table_number : 'N/A'}</p>
                                <p><strong>Customer:</strong> ${order.customer_name ? escapeHtml(order.customer_name) : 'N/A'}</p>
                                <p><strong>Status:</strong> <span class="badge bg-info">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p><strong>Order Time:</strong> ${orderTime}</p>
                                <p><strong>Items:</strong> ${order.items.length}</p>
                                <p><strong>Total:</strong> <span class="text-primary h5">₱${parseFloat(order.total_amount).toFixed(2)}</span></p>
                            </div>
                        </div>
                        <hr>

                        <h6><i class="fas fa-list"></i> Order Items</h6>
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsRows.length > 0 ? itemsRows : '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                `;

                $('#viewOrderContent').html(html);
            } else {
                $('#viewOrderContent').html('<p class="text-danger"><i class="fas fa-exclamation-circle"></i> Failed to load order details</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading order details:', error);
            let errorMessage = 'Failed to load order details';

            if (xhr.status === 404) {
                errorMessage = 'Order not found';
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }

            $('#viewOrderContent').html(`
                <p class="text-danger">
                    <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                </p>
            `);
        }
    });

    // Show the modal
    $('#viewOrderModal').modal('show');
}


function getOrderDisplayStatus(order) {
    // Both served AND paid = Completed
    if (order.status === 'served' && order.payment_status === 'paid') {
        return {
            status: 'completed',
            text: 'Completed',
            badgeClass: 'bg-success'
        };
    }
    
    // Served but not paid yet
    if (order.status === 'served' && order.payment_status === 'pending') {
        return {
            status: 'served-unpaid',
            text: 'Served (Unpaid)',
            badgeClass: 'bg-warning'
        };
    }
    
    // Paid but not served yet
    if (order.status !== 'served' && order.payment_status === 'paid') {
        return {
            status: 'paid-unserved',
            text: 'Paid (Not Served)',
            badgeClass: 'bg-info'
        };
    }
    
    // Refunded
    if (order.payment_status === 'refunded') {
        return {
            status: 'refunded',
            text: 'Refunded',
            badgeClass: 'bg-danger'
        };
    }
    
    // Standard statuses
    const statusMap = {
        'pending': { text: 'Pending', badgeClass: 'bg-warning' },
        'preparing': { text: 'Preparing', badgeClass: 'bg-info' },
        'ready': { text: 'Ready', badgeClass: 'bg-success' },
        'served': { text: 'Served', badgeClass: 'bg-success' }
    };
    
    const mapping = statusMap[order.status] || { text: order.status, badgeClass: 'bg-secondary' };
    return {
        status: order.status,
        text: mapping.text,
        badgeClass: mapping.badgeClass
    };
}


function reprintReceipt(orderId) {
    window.open(`<?= base_url("restaurant/{$tenant_slug}/print-receipt") ?>/${orderId}`, '_blank');
}

function createOrder() {
    // Get form values
    const orderType = document.querySelector('select[name="order_type"]').value;
    const tableId = document.querySelector('select[name="table_id"]').value;
    const guests = document.querySelector('input[name="guests"]').value;
    const customerName = document.querySelector('input[name="customer_name"]').value;
    const instructions = document.querySelector('textarea[name="instructions"]').value;

    console.log('Form data before validation:', {orderType, tableId, guests, customerName, instructions});

    // Validate
    if (!orderType || !tableId || !guests) {
        alert('Please fill in: Order Type, Table, and Number of Guests');
        return;
    }

    // Disable button
    const btn = document.querySelector('#newOrderModal .btn-primary');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

    // Send AJAX
    fetch('<?= base_url("restaurant/{$tenant_slug}/create-order") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            order_type: orderType,
            table_id: tableId,
            guest_count: guests,
            customer_name: customerName,
            special_instructions: instructions,
            items: JSON.stringify([])
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            alert('Order created successfully!');
            document.getElementById('newOrderForm').reset();
            $('#newOrderModal').modal('hide');
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating order: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Create Order';
    });
}

function refreshOrders() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
