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
    // Segregating for cleaner structure
    // Kitchen
    $routes->get('kitchen', 'Restaurant\Kitchen::index');
    $routes->get('kitchen/ajaxOrders', 'Restaurant\Kitchen::ajaxOrders');
    $routes->post('kitchen/updateOrderStatus', 'Restaurant\Kitchen::updateOrderStatus');

    
    //other plain dashboard routes
    $routes->get('tables', 'Restaurant\Dashboard::tables');
    $routes->get('orders', 'Restaurant\Dashboard::orders');
    $routes->get('menu', 'Restaurant\Dashboard::menu');
    // Inventory
    $routes->get('inventory', 'Restaurant\Dashboard::inventory');
    $routes->post('add-inventory-item', 'Restaurant\Dashboard::addInventoryItem');
    $routes->post(
    'update-inventory-stock/(:num)',
    'Restaurant\Dashboard::updateInventoryStock/$1'
    );

    $routes->post('delete-inventory-item/(:num)', 'Restaurant\Dashboard::deleteInventoryItem/$2');
    
    // Staff
    $routes->get('staff', 'Restaurant\Dashboard::staff');
    $routes->post('add-staff', 'Restaurant\Dashboard::addStaff');
    // get-staff is used for everything!
    $routes->get('get-staff/(:num)', 'Restaurant\Dashboard::getStaff/$2');
    $routes->post('update-staff', 'Restaurant\Dashboard::updateStaff');
    $routes->post('delete-staff/(:num)', 'Restaurant\Dashboard::deleteStaff/$2');
    
    // Reports and Analytics
    $routes->get('reports', 'Reports::index');
    $routes->get('reports/bir', 'Reports::birReports');
    $routes->post('reports-filter', 'Reports::filter');
    $routes->get('reports/export', 'Reports::export/$1');
    // Settings and Profile
    $routes->get('profile', 'Restaurant\Dashboard::profile');
    $routes->get('settings', 'Restaurant\Dashboard::settings');
    $routes->post('save-settings', 'Restaurant\Dashboard::saveSettings');

    // Help route debugging
    $routes->get('help', 'Restaurant\Dashboard::help');

    // Menu Category - FIXED: Removed the extra 'restaurant/(:segment)' prefix
    $routes->post('add-menu-category', 'Restaurant\Dashboard::addMenuCategory');
    $routes->post('add-menu-item', 'Restaurant\Dashboard::addMenuItem');
    $routes->post('new-order', 'Restaurant\OrderController::createOrder/$1');
    $routes->post('create-order', 'Restaurant\Dashboard::createOrder');
    $routes->get('get-menu-item/(:num)', 'Restaurant\Dashboard::getMenuItem/$2');
    $routes->post('update-menu-item', 'Restaurant\Dashboard::updateMenuItem');
    $routes->post('delete-menu-item/(:num)', 'Restaurant\Dashboard::deleteMenuItem/$2');
    // API endpoints
    $routes->post('update-order-status', 'Restaurant\Dashboard::updateOrderStatus');
    $routes->post('update-table-status', 'Restaurant\Dashboard::updateTableStatus');
    $routes->post('update-profile', 'Restaurant\Dashboard::updateProfile');
    $routes->post('save-table', 'Restaurant\Dashboard::saveTable');
    $routes->post('delete-table', 'Restaurant\Dashboard::deleteTable');
    $routes->post('restore-table', 'Restaurant\Dashboard::restoreTable');
    
    // POS related routes
    $routes->get('pos', 'Restaurant\Dashboard::pos');
    $routes->get('paid-orders', 'Restaurant\Pos::paidOrders');  // Print Receipt button
    // Current Orders API
    // POS AJAX/API
    $routes->get('current-orders', 'Restaurant\Dashboard::currentOrders/$1');
    $routes->get('order-details/(:num)', 'Restaurant\Dashboard::orderDetails/$2');
    // Changed from :any to :num
    // $routes->get('order-details/(:num)', 'OrderApi::orderDetails/$1');
    $routes->get('order-api-test', 'OrderApi::test');
    // $routes->post('update-order-status', 'OrderApi::updateOrderStatus');
    
    // Payment routes
    
    // Added this:
    $routes->get('payment/(:num)', 'Restaurant\Payment::index/$2');
    $routes->post('process-payment', 'Restaurant\Payment::processPayment');
    $routes->get('print-receipt/(:num)', 'Restaurant\Payment::printReceipt/$2');
     $routes->get('test-payment', function() {
        return "✅ Routes working!";
    });
    
    // New Order page
    $routes->get('new-order', 'Restaurant\Dashboard::newOrder');
    
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
    // Added this route for tenants actions function: edit/suspend/delete
    $routes->get('tenants/edit/(:num)', 'Admin\Tenants::edit/$1');
    $routes->get('tenants/suspend/(:num)', 'Admin\Tenants::suspend/$1');
    $routes->get('tenants/delete/(:num)', 'Admin\Tenants::delete/$1');
    //Problem saw: missing View files for the above 3 routes: edit, suspend, delete
    

    $routes->get('users', 'Admin\Users::index');
    $routes->get('settings', 'Admin\Settings::index');
    //Added this because there is no get route to show create form, only submit form
    $routes->get('create-tenant', 'Admin\Tenants::create');
    $routes->get('tenants/create', 'Admin\Tenants::create');
        // Added this route for tenants actions function: create/update/delete
    $routes->post('create-tenant', 'Admin\Tenants::create');
    $routes->post('update-tenant/(:num)', 'Admin\Tenants::update/$1');
    $routes->post('delete-tenant/(:num)', 'Admin\Tenants::delete/$1');
    $routes->post('create-user', 'Admin\Users::create');
    $routes->post('update-user/(:num)', 'Admin\Users::update/$1');
    $routes->post('delete-user/(:num)', 'Admin\Users::delete/$1');
    // Added this route for employees
    $routes->get('employees', 'Admin\Employees::index');
    // Trying to look for issues on the routing:
    // Conclusion after looking and debugging: the route doesn't have a problem but rather the controller 
    // method, which are the missing files and methods, probably there are 3 missing files
    
});