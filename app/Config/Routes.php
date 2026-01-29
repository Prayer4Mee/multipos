<?php
// app/Config/Routes.php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// TOUCHPOINT POS MULTI-TENANT ROUTING SYSTEM
// NTEKSYSTEMS Inc.
// ============================================================

// Default route - redirect to login
$routes->get('/', 'Auth::index');
$routes->get('multipos', 'Auth::index');
$routes->get('welcome', function() {
    return view('welcome_message');
});


// ============================================================
// MULTI-TENANT RESTAURANT ROUTES
// URL Pattern: /restaurant/{tenant_slug}/...
// ============================================================

$routes->group('restaurant/(:segment)', function($routes) {

    // Dashboard (Manager)
    $routes->get('/', 'Restaurant\Dashboard::index');
    $routes->get('dashboard', 'Restaurant\Dashboard::index');
    
    // Additional Dashboard routes
    $routes->get('pos', 'Restaurant\Dashboard::pos');
    $routes->get('kitchen', 'Restaurant\Kitchen::index');
    $routes->get('tables', 'Restaurant\Dashboard::tables');
    $routes->get('orders-page', 'Restaurant\Dashboard::orders');
    $routes->get('menu', 'Restaurant\Dashboard::menu');
    $routes->get('inventory', 'Restaurant\Dashboard::inventory');
    $routes->get('staff', 'Restaurant\Dashboard::staff');
    $routes->get('reports', 'Restaurant\Dashboard::reports');
    $routes->get('profile', 'Restaurant\Dashboard::profile');
    $routes->get('settings', 'Restaurant\Dashboard::settings');
    
    // API endpoints
    $routes->get('create-order', 'App\Controllers\Restaurant\Dashboard::createOrder');
    $routes->post('create-order', 'App\Controllers\Restaurant\Dashboard::createOrder');
    $routes->post('update-order-status', 'OrderApi::updateOrderStatus');
    $routes->post('update-table-status', 'Restaurant\Dashboard::updateTableStatus');
    $routes->post('update-profile', 'Restaurant\Dashboard::updateProfile');
    
    // Current Orders API
        $routes->get('current-orders', 'Restaurant\Dashboard::currentOrders');
    $routes->get('order-details/(:any)', 'OrderApi::orderDetails/$1');
    $routes->get('order-api-test', 'OrderApi::test');
    $routes->post('update-order-status', 'OrderApi::updateOrderStatus');
    
    // Payment routes
    $routes->get('payment/(:num)', 'Restaurant\Payment::index/$1');
    $routes->get('print-receipt/(:num)', 'Restaurant\Payment::printReceipt/$1');
    $routes->post('process-payment', 'Restaurant\Payment::processPayment');
    
    // New Order page
    $routes->get('new-order', function() {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>New Order - Jollibee</title>
    <meta name="csrf-token" content="test-token-123">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .menu-table { max-height: 70vh; overflow-y: auto; }
        .order-summary { max-height: 80vh; overflow-y: auto; }
        .category-filter { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; padding: 15px; margin-bottom: 20px; }
        .category-btn { margin: 5px; border-radius: 20px; padding: 8px 16px; border: 2px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: white; transition: all 0.3s ease; }
        .category-btn:hover, .category-btn.active { background: rgba(255,255,255,0.3); border-color: rgba(255,255,255,0.6); transform: translateY(-2px); }
        .add-btn { border-radius: 20px; padding: 5px 15px; }
        .order-item { border-left: 4px solid #007bff; margin-bottom: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-plus-circle"></i> New Order</h1>
                    <a href="/restaurant/jollibee/pos" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to POS</a>
                </div>
            </div>
        </div>
        <div class="category-filter">
            <h5 class="text-white mb-3"><i class="fas fa-filter"></i> Menu Categories</h5>
            <div class="d-flex flex-wrap">
                <button class="btn category-btn active" data-category="all"><i class="fas fa-hamburger"></i> All Items</button>
                <button class="btn category-btn" data-category="chicken"><i class="fas fa-drumstick-bite"></i> Chicken</button>
                <button class="btn category-btn" data-category="pasta"><i class="fas fa-pizza-slice"></i> Pasta</button>
                <button class="btn category-btn" data-category="beverages"><i class="fas fa-coffee"></i> Beverages</button>
                <button class="btn category-btn" data-category="desserts"><i class="fas fa-ice-cream"></i> Desserts</button>
            </div>
        </div>
        <div class="row">
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
                                    <tr><th width="5%">#</th><th width="30%">Item Name</th><th width="15%">Category</th><th width="15%">Price</th><th width="20%">Description</th><th width="15%">Action</th></tr>
                                </thead>
                                <tbody id="menu-items-table">
                                    <tr data-category="chicken"><td>1</td><td><strong>Chicken Joy (2 pcs)</strong></td><td><span class="badge bg-warning">Chicken</span></td><td><strong>₱100.00</strong></td><td>Crispy fried chicken with rice</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(1, \'Chicken Joy (2 pcs)\', 100.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                    <tr data-category="chicken"><td>2</td><td><strong>Yum Burger</strong></td><td><span class="badge bg-warning">Chicken</span></td><td><strong>₱50.00</strong></td><td>Chicken burger with special sauce</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(2, \'Yum Burger\', 50.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                    <tr data-category="pasta"><td>3</td><td><strong>Jolly Spaghetti</strong></td><td><span class="badge bg-info">Pasta</span></td><td><strong>₱80.00</strong></td><td>Sweet-style spaghetti with hotdog</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(3, \'Jolly Spaghetti\', 80.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                    <tr data-category="pasta"><td>4</td><td><strong>Carbonara</strong></td><td><span class="badge bg-info">Pasta</span></td><td><strong>₱90.00</strong></td><td>Creamy carbonara pasta</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(4, \'Carbonara\', 90.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                    <tr data-category="beverages"><td>5</td><td><strong>Coke</strong></td><td><span class="badge bg-success">Beverages</span></td><td><strong>₱25.00</strong></td><td>Refreshing cola drink</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(5, \'Coke\', 25.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                    <tr data-category="beverages"><td>6</td><td><strong>Pineapple Juice</strong></td><td><span class="badge bg-success">Beverages</span></td><td><strong>₱30.00</strong></td><td>Fresh pineapple juice</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(6, \'Pineapple Juice\', 30.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                    <tr data-category="desserts"><td>7</td><td><strong>Ice Cream</strong></td><td><span class="badge bg-danger">Desserts</span></td><td><strong>₱40.00</strong></td><td>Vanilla ice cream</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(7, \'Ice Cream\', 40.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                    <tr data-category="chicken"><td>8</td><td><strong>Chicken Sandwich</strong></td><td><span class="badge bg-warning">Chicken</span></td><td><strong>₱60.00</strong></td><td>Grilled chicken sandwich</td><td><button class="btn btn-primary btn-sm add-btn" onclick="addToOrder(8, \'Chicken Sandwich\', 60.00)"><i class="fas fa-plus"></i> Add</button></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-shopping-cart"></i> Order Summary</h5></div>
                    <div class="card-body order-summary">
                        <div class="mb-3"><label class="form-label">Table Number</label><select class="form-select" id="table-select"><option value="1">Table 1</option><option value="2">Table 2</option><option value="3">Table 3</option><option value="4">Table 4</option><option value="5">Table 5</option><option value="6">Table 6</option><option value="7">Table 7</option><option value="8">Table 8</option></select></div>
                        <div class="mb-3"><label class="form-label">Customer Name</label><input type="text" class="form-control" id="customer-name" placeholder="Enter customer name"></div>
                        <div class="order-items mb-3"><h6>Order Items:</h6><div id="order-items-list" style="max-height: 300px; overflow-y: auto;"><div class="text-muted text-center py-3"><i class="fas fa-shopping-cart"></i><br>No items added yet</div></div></div>
                        <div class="order-total mb-3">
                            <div class="d-flex justify-content-between"><strong>Subtotal:</strong><span id="subtotal">₱0.00</span></div>
                            <div class="d-flex justify-content-between"><span>Service Charge (10%):</span><span id="service-charge">₱0.00</span></div>
                            <div class="d-flex justify-content-between"><span>VAT (12%):</span><span id="vat">₱0.00</span></div>
                            <hr><div class="d-flex justify-content-between"><strong>Total:</strong><strong id="total">₱0.00</strong></div>
                        </div>
                        <div class="d-grid gap-2"><button class="btn btn-success" id="create-order-btn" onclick="createOrder()" disabled><i class="fas fa-check"></i> Create Order</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let orderItems = []; let currentCategory = "all";
        $(document).ready(function() {
            updateItemCount();
            $(\'.category-btn\').click(function() {
                $(\'.category-btn\').removeClass(\'active\'); $(this).addClass(\'active\');
                currentCategory = $(this).data(\'category\'); filterMenuItems();
            });
            $(\'#menu-search\').on(\'input\', function() {
                const searchTerm = $(this).val().toLowerCase(); filterMenuItems(searchTerm);
            });
        });
        function filterMenuItems(searchTerm = \'\') {
            $(\'#menu-items-table tr\').each(function() {
                const $row = $(this); const itemCategory = $row.data(\'category\'); const itemName = $row.find(\'td:nth-child(2)\').text().toLowerCase();
                const categoryMatch = currentCategory === \'all\' || itemCategory === currentCategory; const searchMatch = searchTerm === \'\' || itemName.includes(searchTerm);
                if (categoryMatch && searchMatch) { $row.show(); } else { $row.hide(); }
            }); updateItemCount();
        }
        function updateItemCount() { const visibleItems = $(\'#menu-items-table tr:visible\').length; $(\'#item-count\').text(visibleItems + \' items\'); }
        function addToOrder(id, name, price) {
            const existingItem = orderItems.find(item => item.id === id);
            if (existingItem) { existingItem.quantity += 1; } else { orderItems.push({ id: id, name: name, price: price, quantity: 1 }); }
            updateOrderDisplay(); showNotification(\'Item added to order\', \'success\');
        }
        function removeFromOrder(id) { orderItems = orderItems.filter(item => item.id !== id); updateOrderDisplay(); }
        function updateQuantity(id, newQuantity) {
            const item = orderItems.find(item => item.id === id);
            if (item) { if (newQuantity <= 0) { removeFromOrder(id); } else { item.quantity = newQuantity; updateOrderDisplay(); } }
        }
        function updateOrderDisplay() {
            const orderList = $(\'#order-items-list\'); orderList.empty();
            if (orderItems.length === 0) {
                orderList.html(\'<div class="text-muted text-center py-3"><i class="fas fa-shopping-cart"></i><br>No items added yet</div>\');
                $(\'#create-order-btn\').prop(\'disabled\', true);
            } else {
                orderItems.forEach(item => {
                    const itemHtml = `<div class="order-item"><div class="d-flex justify-content-between align-items-start"><div class="flex-grow-1"><div class="fw-bold">${item.name}</div><small class="text-muted">₱${item.price.toFixed(2)} each</small></div><div class="d-flex align-items-center"><button class="btn btn-sm btn-outline-secondary me-1" onclick="updateQuantity(${item.id}, ${item.quantity - 1})"><i class="fas fa-minus"></i></button><span class="mx-2 fw-bold">${item.quantity}</span><button class="btn btn-sm btn-outline-secondary me-2" onclick="updateQuantity(${item.id}, ${item.quantity + 1})"><i class="fas fa-plus"></i></button></div></div><div class="d-flex justify-content-between align-items-center mt-2"><span class="fw-bold text-primary">₱${(item.price * item.quantity).toFixed(2)}</span><button class="btn btn-sm btn-outline-danger" onclick="removeFromOrder(${item.id})"><i class="fas fa-trash"></i></button></div></div>`;
                    orderList.append(itemHtml);
                }); $(\'#create-order-btn\').prop(\'disabled\', false);
            } updateTotals();
        }
        function updateTotals() {
            const subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const serviceCharge = subtotal * 0.10; const vat = (subtotal + serviceCharge) * 0.12; const total = subtotal + serviceCharge + vat;
            $(\'#subtotal\').text(\'₱\' + subtotal.toFixed(2)); $(\'#service-charge\').text(\'₱\' + serviceCharge.toFixed(2)); $(\'#vat\').text(\'₱\' + vat.toFixed(2)); $(\'#total\').text(\'₱\' + total.toFixed(2));
        }
        function createOrder() {
            const tableId = $(\'#table-select\').val(); 
            const customerName = $(\'#customer-name\').val();
            
            if (!customerName.trim()) { 
                showNotification(\'Please enter customer name\', \'error\'); 
                return; 
            }
            if (orderItems.length === 0) { 
                showNotification(\'Please add items to order\', \'error\'); 
                return; 
            }
            
            $(\'#create-order-btn\').prop(\'disabled\', true).html(\'<i class="fas fa-spinner fa-spin"></i> Creating...\');
            
            // 실제 API 호출
            const orderData = {
                table_id: tableId,
                customer_name: customerName,
                items: orderItems,
                subtotal: orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0),
                service_charge: orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 0.10,
                vat: (orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 1.10) * 0.12,
                total: (orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 1.10) * 1.12,
                status: \'created\'
            };
            
            // Get CSRF token
            const csrfToken = $(\'meta[name="csrf-token"]\').attr(\'content\') || \'\';
            
            $.ajax({
                url: \'/restaurant/jollibee/create-order\',
                method: \'GET\',
                data: orderData,
                headers: {
                    \'X-Requested-With\': \'XMLHttpRequest\'
                },
                success: function(response) {
                    console.log(\'Order creation response:\', response);
                    console.log(\'Response type:\', typeof response);
                    console.log(\'Response success:\', response.success);
                    
                    // 응답이 문자열인 경우 JSON 파싱
                    if (typeof response === \'string\') {
                        try {
                            response = JSON.parse(response);
                            console.log(\'Parsed response:\', response);
                        } catch (e) {
                            console.error(\'Failed to parse response:\', e);
                        }
                    }
                    
                    if (response && response.success === true) {
                        console.log(\'Order creation successful!\');
                        showNotification(\'Order created successfully!\', \'success\');
                        orderItems = [];
                        $(\'#customer-name\').val(\'\');
                        updateOrderDisplay();
                        $(\'#create-order-btn\').prop(\'disabled\', false).html(\'<i class="fas fa-check"></i> Create Order\');
                        setTimeout(() => { 
                            window.location.href = \'/restaurant/jollibee/pos\'; 
                        }, 2000);
                    } else {
                        console.log(\'Order creation failed - response:\', response);
                        showNotification(\'Failed to create order. Please try again.\', \'error\');
                        $(\'#create-order-btn\').prop(\'disabled\', false).html(\'<i class="fas fa-check"></i> Create Order\');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(\'Order creation failed:\', xhr.responseText, status, error);
                    let errorMessage = \'Failed to create order. Please try again.\';
                    if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.error || errorMessage;
                        } catch (e) {
                            console.log(\'Could not parse error response:\', xhr.responseText);
                        }
                    }
                    showNotification(errorMessage, \'error\');
                    $(\'#create-order-btn\').prop(\'disabled\', false).html(\'<i class="fas fa-check"></i> Create Order\');
                }
            });
        }
        function showNotification(message, type) {
            // 기존 알림 제거
            $(\'.alert-notification\').remove();
            
            const alertClass = type === \'success\' ? \'alert-success\' : type === \'error\' ? \'alert-danger\' : \'alert-info\';
            const notification = $(`<div class="alert ${alertClass} alert-dismissible fade show position-fixed alert-notification" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">${message}<button type="button" class="btn-close" onclick="$(this).parent().fadeOut(300, function() { $(this).remove(); })"></button></div>`);
            
            $(\'body\').append(notification); 
            
            // 자동 제거
            setTimeout(() => { 
                notification.fadeOut(500, function() { 
                    $(this).remove(); 
                }); 
            }, 3000);
        }
    </script>
</body>
</html>';
        return $html;
    });
    
    // Auth routes
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attemptLogin');
    $routes->get('logout', 'Auth::logout');
    
    // Tenant-specific API routes
    $routes->get('api/orders', 'OrderApi::getOrders');
    $routes->get('api/order-details/(:num)', 'OrderApi::orderDetails/$1');
    $routes->post('api/update-order-status', 'OrderApi::updateOrderStatus');
    $routes->get('api/menu-items', 'OrderApi::getMenuItems');
    $routes->get('api/tables', 'OrderApi::getTables');
    
});

// ============================================================
// API ROUTES (Global - Outside tenant group)
// ============================================================

// ============================================================
// AUTHENTICATION ROUTES (Global)
// ============================================================
$routes->get('auth/login', 'Auth::index');
$routes->post('auth/login', 'Auth::attemptLogin');
$routes->get('auth/logout', 'Auth::logout');

// ============================================================
// ADMIN ROUTES (Super Admin)
// ============================================================

// $routes->group('admin', ['filter' => 'role:admin'], function($routes) {
$routes->group('admin', function($routes) {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('tenants', 'Admin\Tenants::index');
    $routes->get('users', 'Admin\Users::index');
    $routes->get('settings', 'Admin\Settings::index');
    $routes->post('create-tenant', 'Admin\Tenants::create');
    $routes->post('update-tenant/(:num)', 'Admin\Tenants::update/$1');
    $routes->post('delete-tenant/(:num)', 'Admin\Tenants::delete/$1');
    $routes->post('create-user', 'Admin\Users::create');
    $routes->post('update-user/(:num)', 'Admin\Users::update/$1');
    $routes->post('delete-user/(:num)', 'Admin\Users::delete/$1');
});