<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="orders-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-clipboard-check"></i> Orders Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshOrders()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOrderModal">
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
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="row">
        <!-- Order Card 1 -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card order-card" data-order-id="1234" data-status="preparing">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Order #1234</h6>
                    <span class="badge bg-info">Preparing</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <p class="mb-1"><strong>Table 2</strong></p>
                            <p class="mb-1 text-muted small">2 guests</p>
                            <p class="mb-1 text-muted small">Ordered: 2:30 PM</p>
                        </div>
                        <div class="col-4 text-end">
                            <h6 class="text-primary mb-0">₱134</h6>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="order-items">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Chicken Burger x1</span>
                            <span class="small">₱89</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">French Fries x1</span>
                            <span class="small">₱45</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-success" onclick="markReady(1234)">
                                <i class="fas fa-check"></i> Ready
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(1234)">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Card 2 -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card order-card" data-order-id="1235" data-status="pending">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Order #1235</h6>
                    <span class="badge bg-warning">Pending</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <p class="mb-1"><strong>Table 7</strong></p>
                            <p class="mb-1 text-muted small">4 guests</p>
                            <p class="mb-1 text-muted small">Ordered: 2:25 PM</p>
                        </div>
                        <div class="col-4 text-end">
                            <h6 class="text-primary mb-0">₱110</h6>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="order-items">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Fried Chicken x1</span>
                            <span class="small">₱75</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Coffee x1</span>
                            <span class="small">₱35</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-info" onclick="startPreparing(1235)">
                                <i class="fas fa-play"></i> Start
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(1235)">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Card 3 -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card order-card" data-order-id="1236" data-status="ready">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Order #1236</h6>
                    <span class="badge bg-success">Ready</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <p class="mb-1"><strong>Table 1</strong></p>
                            <p class="mb-1 text-muted small">2 guests</p>
                            <p class="mb-1 text-muted small">Ordered: 2:20 PM</p>
                        </div>
                        <div class="col-4 text-end">
                            <h6 class="text-primary mb-0">₱175</h6>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="order-items">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Beef Burger x1</span>
                            <span class="small">₱95</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">French Fries x1</span>
                            <span class="small">₱45</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Coffee x1</span>
                            <span class="small">₱35</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-secondary" onclick="markCompleted(1236)">
                                <i class="fas fa-check-circle"></i> Complete
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(1236)">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Card 4 -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card order-card" data-order-id="1237" data-status="completed">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Order #1237</h6>
                    <span class="badge bg-secondary">Completed</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <p class="mb-1"><strong>Table 3</strong></p>
                            <p class="mb-1 text-muted small">3 guests</p>
                            <p class="mb-1 text-muted small">Completed: 2:15 PM</p>
                        </div>
                        <div class="col-4 text-end">
                            <h6 class="text-primary mb-0">₱200</h6>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="order-items">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Chicken Wings x2</span>
                            <span class="small">₱130</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">French Fries x2</span>
                            <span class="small">₱90</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(1237)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="reprintReceipt(1237)">
                                <i class="fas fa-print"></i> Receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Status filter handler
    $('input[name="statusFilter"]').change(function() {
        const status = $(this).val();
        filterOrders(status);
    });
});

function filterOrders(status) {
    $('.order-card').each(function() {
        const orderStatus = $(this).data('status');
        
        if (status === 'all' || orderStatus === status) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function markReady(orderId) {
    if (confirm('Mark order #' + orderId + ' as ready?')) {
        // Simulate API call
        console.log('Marking order as ready:', orderId);
        alert('Order #' + orderId + ' marked as ready!');
        
        // Update UI
        const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
        orderCard.find('.badge').removeClass('bg-info').addClass('bg-success').text('Ready');
        orderCard.attr('data-status', 'ready');
        
        // Update buttons
        const buttonGroup = orderCard.find('.btn-group');
        buttonGroup.html(`
            <button class="btn btn-sm btn-outline-secondary" onclick="markCompleted(${orderId})">
                <i class="fas fa-check-circle"></i> Complete
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${orderId})">
                <i class="fas fa-eye"></i> View
            </button>
        `);
    }
}

function startPreparing(orderId) {
    if (confirm('Start preparing order #' + orderId + '?')) {
        // Simulate API call
        console.log('Starting preparation for order:', orderId);
        alert('Order #' + orderId + ' preparation started!');
        
        // Update UI
        const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
        orderCard.find('.badge').removeClass('bg-warning').addClass('bg-info').text('Preparing');
        orderCard.attr('data-status', 'preparing');
        
        // Update buttons
        const buttonGroup = orderCard.find('.btn-group');
        buttonGroup.html(`
            <button class="btn btn-sm btn-outline-success" onclick="markReady(${orderId})">
                <i class="fas fa-check"></i> Ready
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${orderId})">
                <i class="fas fa-eye"></i> View
            </button>
        `);
    }
}

function markCompleted(orderId) {
    if (confirm('Mark order #' + orderId + ' as completed?')) {
        // Simulate API call
        console.log('Marking order as completed:', orderId);
        alert('Order #' + orderId + ' completed!');
        
        // Update UI
        const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
        orderCard.find('.badge').removeClass('bg-success').addClass('bg-secondary').text('Completed');
        orderCard.attr('data-status', 'completed');
        
        // Update buttons
        const buttonGroup = orderCard.find('.btn-group');
        buttonGroup.html(`
            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${orderId})">
                <i class="fas fa-eye"></i> View Details
            </button>
            <button class="btn btn-sm btn-outline-info" onclick="reprintReceipt(${orderId})">
                <i class="fas fa-print"></i> Receipt
            </button>
        `);
    }
}

function viewOrder(orderId) {
    // Simulate viewing order details
    alert('Viewing order details for #' + orderId);
}

function reprintReceipt(orderId) {
    // Simulate reprinting receipt
    alert('Reprinting receipt for order #' + orderId);
}

function createOrder() {
    const form = document.getElementById('newOrderForm');
    const formData = new FormData(form);
    
    // Simulate order creation
    console.log('Creating new order:', Object.fromEntries(formData));
    alert('New order created successfully!');
    $('#newOrderModal').modal('hide');
    form.reset();
}

function refreshOrders() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
