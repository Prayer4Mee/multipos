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
    $routes->post('create-order', 'Restaurant\Dashboard::createOrder');
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
    $routes->get('new-order', 'Restaurant\Pos::newOrder');
    
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
// AUTHENTICATION ROUTES (Global)
// ============================================================
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// ============================================================
// ADMIN ROUTES (Super Admin)
// ============================================================
$routes->group('admin', ['filter' => 'role:admin'], function($routes) {
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