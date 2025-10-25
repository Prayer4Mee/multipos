<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;
use App\Models\Master\TenantModel;

class Dashboard extends BaseRestaurantController
{
    protected $tenantModel;
    protected $tenant;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
    }

    public function index()
    {
        // Get employee statistics
        $totalEmployees = 0;
        $activeEmployees = 0;
        $employeesByRole = [];

        try {
            // Get employees from main database for this tenant
            $db = \Config\Database::connect('default');
            $employees = $db->table('users')
                           ->where('tenant_id', $this->tenantId)
                           ->where('is_active', 1)
                           ->get()
                           ->getResult();

            $totalEmployees = count($employees);
            $activeEmployees = count(array_filter($employees, function($emp) {
                return $emp->employment_status === 'active';
            }));

            // Group by role
            foreach ($employees as $employee) {
                $role = $employee->role;
                if (!isset($employeesByRole[$role])) {
                    $employeesByRole[$role] = 0;
                }
                $employeesByRole[$role]++;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to get employee statistics: ' . $e->getMessage());
        }

        // Get order statistics
        $orderStats = [
            'pending_orders' => 0,
            'preparing_orders' => 0,
            'completed_orders' => 0,
            'today_revenue' => 0
        ];

        $recentOrders = [];

        try {
            // Get order statistics
            $today = date('Y-m-d');
            $orderStats['pending_orders'] = $this->tenantDb->table('orders')
                                                         ->where('status', 'pending')
                                                         ->countAllResults();
            
            $orderStats['preparing_orders'] = $this->tenantDb->table('orders')
                                                           ->where('status', 'preparing')
                                                           ->countAllResults();
            
            $orderStats['completed_orders'] = $this->tenantDb->table('orders')
                                                           ->where('status', 'completed')
                                                           ->where('DATE(created_at)', $today)
                                                           ->countAllResults();

            // Get today's revenue
            $revenueResult = $this->tenantDb->table('orders')
                                          ->select('SUM(total_amount) as total_revenue')
                                          ->where('status', 'completed')
                                          ->where('DATE(created_at)', $today)
                                          ->get()
                                          ->getRow();
            
            $orderStats['today_revenue'] = $revenueResult->total_revenue ?? 0;

            // Get recent orders with table information
            $recentOrders = $this->tenantDb->table('orders o')
                                         ->select('o.*, rt.table_number, COUNT(oi.id) as item_count')
                                         ->join('restaurant_tables rt', 'o.table_id = rt.id', 'left')
                                         ->join('order_items oi', 'o.id = oi.order_id', 'left')
                                         ->groupBy('o.id')
                                         ->orderBy('o.created_at', 'DESC')
                                         ->limit(10)
                                         ->get()
                                         ->getResult();

        } catch (\Exception $e) {
            log_message('error', 'Failed to get order statistics: ' . $e->getMessage());
        }

        $data = [
            'title' => 'Restaurant Dashboard - MultiPOS',
            'page_title' => 'Dashboard',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser ?? (object)['role' => 'staff', 'name' => 'Demo User'],
            'employee_stats' => [
                'total_employees' => $totalEmployees,
                'active_employees' => $activeEmployees,
                'employees_by_role' => $employeesByRole
            ],
            'order_stats' => $orderStats,
            'recent_orders' => $recentOrders
        ];

        return view('restaurant/dashboard', $data);
    }

    public function pos()
    {
        // Get menu categories from database
        $menuCategories = $this->tenantDb->table('menu_categories')
                                        ->where('is_active', 1)
                                        ->orderBy('display_order')
                                        ->get()
                                        ->getResult();
        
        // Get menu items from database
        $menuItems = $this->tenantDb->table('menu_items mi')
                                   ->select('mi.*, mc.name as category_name')
                                   ->join('menu_categories mc', 'mi.category_id = mc.id')
                                   ->where('mi.is_available', 1)
                                   ->orderBy('mc.display_order, mi.display_order')
                                   ->get()
                                   ->getResult();
        
        // Get available tables
        $tables = $this->tenantDb->table('restaurant_tables')
                                ->where('status', 'available')
                                ->orderBy('table_number')
                                ->get()
                                ->getResult();

        $data = [
            'title' => 'POS Terminal - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'POS Terminal',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'menu_categories' => $menuCategories,
            'menu_items' => $menuItems,
            'tables' => $tables
        ];
        return view('restaurant/pos', $data);
    }


    public function tables()
    {
        $tables = $this->tenantDb->table('restaurant_tables')
                                ->orderBy('table_number')
                                ->get()
                                ->getResult();

        // Calculate table statistics
        $totalTables = count($tables);
        $availableTables = 0;
        $occupiedTables = 0;
        $reservedTables = 0;
        $cleaningTables = 0;
        $totalCapacity = 0;
        $availableCapacity = 0;

        foreach ($tables as $table) {
            $totalCapacity += $table->capacity;
            
            switch ($table->status) {
                case 'available':
                    $availableTables++;
                    $availableCapacity += $table->capacity;
                    break;
                case 'occupied':
                    $occupiedTables++;
                    break;
                case 'reserved':
                    $reservedTables++;
                    break;
                case 'cleaning':
                    $cleaningTables++;
                    break;
            }
        }

        $data = [
            'title' => 'Table Management - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Table Management',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'tables' => $tables,
            'stats' => [
                'total_tables' => $totalTables,
                'available_tables' => $availableTables,
                'occupied_tables' => $occupiedTables,
                'reserved_tables' => $reservedTables,
                'cleaning_tables' => $cleaningTables,
                'total_capacity' => $totalCapacity,
                'available_capacity' => $availableCapacity,
                'occupancy_rate' => $totalTables > 0 ? round(($occupiedTables / $totalTables) * 100, 1) : 0
            ]
        ];
        return view('restaurant/tables', $data);
    }

    public function orders()
    {
        // 통합 주문 시스템 사용
        $orderModel = new \App\Models\Tenant\OrderModel();
        $orderModel->setDB($this->tenantDb);
        
        $orderStats = [
            'total_orders' => 0,
            'pending_orders' => 0,
            'preparing_orders' => 0,
            'ready_orders' => 0,
            'completed_orders' => 0,
            'today_revenue' => 0
        ];

        try {
            // 통합 시스템으로 주문 데이터 가져오기
            $orders = $orderModel->getOrdersWithDetails();

            // Get order items for each order
            foreach ($orders as $order) {
                $order->items = $this->tenantDb->table('order_items oi')
                                              ->select('oi.*, mi.name as menu_item_name')
                                              ->join('menu_items mi', 'oi.menu_item_id = mi.id', 'left')
                                              ->where('oi.order_id', $order->id)
                                              ->get()
                                              ->getResult();
            }

            // Calculate order statistics
            $today = date('Y-m-d');
            $orderStats['total_orders'] = count($orders);
            
            foreach ($orders as $order) {
                switch($order->status) {
                    case 'pending': $orderStats['pending_orders']++; break;
                    case 'preparing': $orderStats['preparing_orders']++; break;
                    case 'ready': $orderStats['ready_orders']++; break;
                    case 'completed': 
                        $orderStats['completed_orders']++;
                        if (date('Y-m-d', strtotime($order->created_at)) === $today) {
                            $orderStats['today_revenue'] += $order->total_amount;
                        }
                        break;
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to get orders: ' . $e->getMessage());
        }

        $data = [
            'title' => 'Orders - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Orders',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'orders' => $orders,
            'order_stats' => $orderStats
        ];
        return view('restaurant/orders', $data);
    }

    /**
     * Create new order via AJAX
     */
    public function createOrder()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $rules = [
            'table_id' => 'required|integer',
            'items' => 'required',
            'total_amount' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['error' => $this->validator->getErrors()])->setStatusCode(400);
        }

        try {
            $this->tenantDb->transStart();

            // Generate order number
            $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create order
            $orderData = [
                'order_number' => $orderNumber,
                'order_type' => 'dine_in',
                'order_source' => 'pos',
                'table_id' => $this->request->getPost('table_id'),
                'customer_name' => $this->request->getPost('customer_name'),
                'subtotal' => $this->request->getPost('total_amount'),
                'total_amount' => $this->request->getPost('total_amount'),
                'status' => 'pending',
                'payment_status' => 'pending',
                'ordered_at' => date('Y-m-d H:i:s')
            ];

            $orderId = $this->tenantDb->table('orders')->insert($orderData);

            // Add order items
            $items = json_decode($this->request->getPost('items'), true);
            foreach ($items as $item) {
                $itemData = [
                    'order_id' => $orderId,
                    'menu_item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'kitchen_status' => 'pending'
                ];
                $this->tenantDb->table('order_items')->insert($itemData);
            }

            // Get table number for kitchen orders
            $table = $this->tenantDb->table('restaurant_tables')
                                  ->where('id', $this->request->getPost('table_id'))
                                  ->get()
                                  ->getRow();

            // Add to kitchen orders
            $kitchenOrderData = [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'table_number' => $table ? $table->table_number : null,
                'customer_name' => $this->request->getPost('customer_name'),
                'status' => 'pending',
                'priority' => 'normal',
                'estimated_time' => 15
            ];
            $this->tenantDb->table('kitchen_orders')->insert($kitchenOrderData);

            // Update table status
            $this->tenantDb->table('restaurant_tables')
                          ->where('id', $this->request->getPost('table_id'))
                          ->update(['status' => 'occupied']);

            $this->tenantDb->transComplete();

            if ($this->tenantDb->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->response->setJSON([
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'message' => 'Order created successfully'
            ]);

        } catch (\Exception $e) {
            $this->tenantDb->transRollback();
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Update order status via AJAX
     */
    public function updateOrderStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $orderId = $this->request->getPost('order_id');
        $status = $this->request->getPost('status');

        try {
            $this->tenantDb->transStart();

            $updateData = ['status' => $status];
            
            if ($status === 'completed') {
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'ready') {
                $updateData['ready_at'] = date('Y-m-d H:i:s');
            }

            // Update orders table
            $this->tenantDb->table('orders')
                          ->where('id', $orderId)
                          ->update($updateData);

            // Update kitchen_orders table
            $this->tenantDb->table('kitchen_orders')
                          ->where('order_id', $orderId)
                          ->update(['status' => $status]);

            // Update order_items kitchen_status
            $this->tenantDb->table('order_items')
                          ->where('order_id', $orderId)
                          ->update(['kitchen_status' => $status]);

            // If order is completed, update table status to available
            if ($status === 'completed') {
                $order = $this->tenantDb->table('orders')
                                      ->where('id', $orderId)
                                      ->get()
                                      ->getRow();
                
                if ($order && $order->table_id) {
                    $this->tenantDb->table('restaurant_tables')
                                  ->where('id', $order->table_id)
                                  ->update(['status' => 'available']);
                }
            }

            $this->tenantDb->transComplete();

            if ($this->tenantDb->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);

        } catch (\Exception $e) {
            $this->tenantDb->transRollback();
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Update restaurant profile via AJAX
     */
    public function updateProfile()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        try {
            $restaurantName = $this->request->getPost('restaurant_name');
            $currency = $this->request->getPost('currency');
            $taxRate = $this->request->getPost('tax_rate');
            $serviceChargeRate = $this->request->getPost('service_charge_rate');
            $themeColor = $this->request->getPost('theme_color');

            // Update settings in database
            $settings = [
                'restaurant_name' => $restaurantName,
                'currency' => $currency,
                'tax_rate' => $taxRate,
                'service_charge_rate' => $serviceChargeRate
            ];

            foreach ($settings as $key => $value) {
                $this->tenantDb->table('settings')
                              ->where('setting_key', $key)
                              ->update(['setting_value' => $value]);
            }

            // Update tenant theme color in main database
            $this->db = \Config\Database::connect('default');
            $this->db->table('tenants')
                    ->where('tenant_slug', $this->tenantId)
                    ->update(['theme_color' => $themeColor]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Update table status via AJAX
     */
    public function updateTableStatus()
    {
        log_message('debug', 'updateTableStatus called');
        log_message('debug', 'Request method: ' . $this->request->getMethod());
        log_message('debug', 'Is AJAX: ' . ($this->request->isAJAX() ? 'true' : 'false'));
        log_message('debug', 'Post data: ' . json_encode($this->request->getPost()));

        if (!$this->request->isAJAX()) {
            log_message('error', 'Non-AJAX request to updateTableStatus');
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $tableId = $this->request->getPost('table_id');
        $status = $this->request->getPost('status');

        log_message('debug', "Updating table {$tableId} to status {$status}");

        if (!$tableId || !$status) {
            log_message('error', 'Missing table_id or status');
            return $this->response->setJSON(['error' => 'Missing required parameters'])->setStatusCode(400);
        }

        try {
            $updateData = ['status' => $status];
            
            // Add reservation details if status is reserved
            if ($status === 'reserved') {
                $reservationTime = $this->request->getPost('reservation_time');
                $customerName = $this->request->getPost('customer_name');
                
                if ($reservationTime) {
                    $updateData['reservation_time'] = $reservationTime;
                }
                if ($customerName) {
                    $updateData['customer_name'] = $customerName;
                }
            }

            log_message('debug', 'Update data: ' . json_encode($updateData));

            $result = $this->tenantDb->table('restaurant_tables')
                          ->where('id', $tableId)
                          ->update($updateData);

            log_message('debug', 'Database update result: ' . ($result ? 'success' : 'failed'));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Table status updated successfully',
                'table_id' => $tableId,
                'new_status' => $status
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Exception in updateTableStatus: ' . $e->getMessage());
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    public function menu()
    {
        // Simple approach - get data directly
        $menuCategories = [];
        $menuItems = [];
        
        try {
            // Get menu categories
            $menuCategories = $this->tenantDb->table('menu_categories')
                                            ->where('tenant_id', $this->tenantId)
                                            ->where('is_active', 1)
                                            ->orderBy('display_order')
                                            ->get()
                                            ->getResult();

            // Get menu items
            $menuItems = $this->tenantDb->table('menu_items mi')
                                       ->select('mi.*, mc.name as category_name')
                                       ->join('menu_categories mc', 'mi.category_id = mc.id')
                                       ->where('mi.tenant_id', $this->tenantId)
                                       ->where('mi.is_available', 1)
                                       ->orderBy('mc.display_order, mi.display_order')
                                       ->get()
                                       ->getResult();
        } catch (\Exception $e) {
            // If there's an error, use empty arrays
            $menuCategories = [];
            $menuItems = [];
        }

        $data = [
            'title' => 'Menu Management - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Menu Management',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'menu_categories' => $menuCategories,
            'menu_items' => $menuItems
        ];
        return view('restaurant/menu', $data);
    }

    public function inventory()
    {
        // Get inventory items from database
        $inventoryItems = [];
        $summaryStats = [
            'total_items' => 0,
            'in_stock' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0
        ];

        try {
            $inventoryItems = $this->tenantDb->table('inventory_items')
                                           ->where('tenant_id', $this->tenantId)
                                           ->where('is_active', 1)
                                           ->orderBy('category, item_name')
                                           ->get()
                                           ->getResult();

            // Calculate summary statistics
            foreach ($inventoryItems as $item) {
                $summaryStats['total_items']++;
                if ($item->current_stock == 0) {
                    $summaryStats['out_of_stock']++;
                } elseif ($item->current_stock <= $item->reorder_level) {
                    $summaryStats['low_stock']++;
                } else {
                    $summaryStats['in_stock']++;
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error loading inventory: " . $e->getMessage());
            $inventoryItems = [];
        }

        $data = [
            'title' => 'Inventory - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Inventory',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'inventory_items' => $inventoryItems,
            'summary_stats' => $summaryStats
        ];
        return view('restaurant/inventory', $data);
    }

    public function staff()
    {
        // Get staff data from tenant database
        $staffMembers = [];
        $staffStats = [
            'total_staff' => 0,
            'active_staff' => 0,
            'by_role' => []
        ];

        try {
            $staffMembers = $this->tenantDb->table('staff')
                                         ->where('tenant_id', $this->tenantId)
                                         ->where('is_active', 1)
                                         ->orderBy('role, first_name')
                                         ->get()
                                         ->getResult();

            // Calculate statistics
            $staffStats['total_staff'] = count($staffMembers);
            $staffStats['active_staff'] = count(array_filter($staffMembers, function($staff) {
                return $staff->employment_status === 'active';
            }));

            // Group by role
            foreach ($staffMembers as $staff) {
                $role = $staff->role;
                if (!isset($staffStats['by_role'][$role])) {
                    $staffStats['by_role'][$role] = 0;
                }
                $staffStats['by_role'][$role]++;
            }
        } catch (\Exception $e) {
            log_message('error', "Error loading staff data: " . $e->getMessage());
            $staffMembers = [];
        }

        $data = [
            'title' => 'Staff Management - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Staff Management',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'staff_members' => $staffMembers,
            'staff_stats' => $staffStats
        ];
        return view('restaurant/staff', $data);
    }

    public function currentOrders()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        try {
            // 통합 주문 시스템 사용
            $orderModel = new \App\Models\Tenant\OrderModel();
            $orderModel->setDB($this->tenantDb);
            
            // 활성 주문만 필터링 (완료되지 않은 주문)
            $filters = [
                'status' => ['created', 'pending', 'confirmed', 'preparing', 'ready', 'served', 'paid']
            ];
            
            $orders = $orderModel->getOrdersWithDetails($filters);

            return $this->response->setJSON([
                'success' => true,
                'orders' => $orders
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get current orders: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load orders'])->setStatusCode(500);
        }
    }

    public function orderDetails($orderId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        try {
            // Get order details with items
            $order = $this->tenantDb->table('orders o')
                                  ->select('o.*, rt.table_number')
                                  ->join('restaurant_tables rt', 'o.table_id = rt.id', 'left')
                                  ->where('o.id', $orderId)
                                  ->get()
                                  ->getRow();

            if (!$order) {
                return $this->response->setJSON(['error' => 'Order not found'])->setStatusCode(404);
            }

            // Get order items
            $order->items = $this->tenantDb->table('order_items oi')
                                         ->select('oi.*, mi.name as menu_item_name')
                                         ->join('menu_items mi', 'oi.menu_item_id = mi.id', 'left')
                                         ->where('oi.order_id', $orderId)
                                         ->get()
                                         ->getResult();

            return $this->response->setJSON([
                'success' => true,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get order details: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load order details'])->setStatusCode(500);
        }
    }

    public function completePayment()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $rules = [
            'order_id' => 'required|integer',
            'payment_method' => 'required|in_list[cash,card,digital]',
            'amount_received' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['error' => $this->validator->getErrors()])->setStatusCode(400);
        }

        try {
            $orderId = $this->request->getPost('order_id');
            $paymentMethod = $this->request->getPost('payment_method');
            $amountReceived = $this->request->getPost('amount_received');

            // Get order details
            $order = $this->tenantDb->table('orders')
                                  ->where('id', $orderId)
                                  ->where('status', 'ready')
                                  ->get()
                                  ->getRow();

            if (!$order) {
                return $this->response->setJSON(['error' => 'Order not found or not ready for payment'])->setStatusCode(404);
            }

            if ($amountReceived < $order->total_amount) {
                return $this->response->setJSON(['error' => 'Amount received is less than order total'])->setStatusCode(400);
            }

            // Update order status to completed
            $this->tenantDb->table('orders')
                          ->where('id', $orderId)
                          ->update([
                              'status' => 'completed',
                              'payment_method' => $paymentMethod,
                              'amount_received' => $amountReceived,
                              'change_amount' => $amountReceived - $order->total_amount,
                              'completed_at' => date('Y-m-d H:i:s')
                          ]);

            // Update table status to available
            $this->tenantDb->table('restaurant_tables')
                          ->where('id', $order->table_id)
                          ->update(['status' => 'available']);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment completed successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Payment completion error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to complete payment'])->setStatusCode(500);
        }
    }

    public function updateStaff()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $rules = [
            'staff_id' => 'required|integer',
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'username' => 'required|min_length[3]|max_length[50]',
            'email' => 'required|valid_email|max_length[100]',
            'role' => 'required|in_list[manager,cashier,kitchen_staff,waiter,staff]',
            'employee_id' => 'required|min_length[3]|max_length[20]',
            'employment_status' => 'required|in_list[active,inactive,terminated]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['error' => $this->validator->getErrors()])->setStatusCode(400);
        }

        try {
            $staffId = $this->request->getPost('staff_id');
            $updateData = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'role' => $this->request->getPost('role'),
                'employee_id' => $this->request->getPost('employee_id'),
                'phone' => $this->request->getPost('phone') ?: null,
                'employment_status' => $this->request->getPost('employment_status'),
                'hire_date' => $this->request->getPost('hire_date') ?: null,
                'salary' => $this->request->getPost('salary') ?: null,
                'address' => $this->request->getPost('address') ?: null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update password if provided
            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $updateData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            // Check if staff exists and belongs to this tenant
            $existingStaff = $this->tenantDb->table('staff')
                                          ->where('id', $staffId)
                                          ->where('tenant_id', $this->tenantId)
                                          ->get()
                                          ->getRow();

            if (!$existingStaff) {
                return $this->response->setJSON(['error' => 'Staff member not found'])->setStatusCode(404);
            }

            // Check for duplicate username/email (excluding current staff)
            $duplicateCheck = $this->tenantDb->table('staff')
                                           ->where('id !=', $staffId)
                                           ->where('tenant_id', $this->tenantId)
                                           ->groupStart()
                                           ->where('username', $updateData['username'])
                                           ->orWhere('email', $updateData['email'])
                                           ->orWhere('employee_id', $updateData['employee_id'])
                                           ->groupEnd()
                                           ->get()
                                           ->getRow();

            if ($duplicateCheck) {
                return $this->response->setJSON(['error' => 'Username, email, or employee ID already exists'])->setStatusCode(400);
            }

            // Update staff member
            $result = $this->tenantDb->table('staff')
                                   ->where('id', $staffId)
                                   ->where('tenant_id', $this->tenantId)
                                   ->update($updateData);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Staff member updated successfully'
                ]);
            } else {
                return $this->response->setJSON(['error' => 'Failed to update staff member'])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Staff update error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'An error occurred while updating staff member'])->setStatusCode(500);
        }
    }

    public function reports()
    {
        $data = [
            'title' => 'Reports - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Reports',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser
        ];
        return view('restaurant/reports', $data);
    }

    public function profile()
    {
        // Get restaurant settings from database
        $settings = $this->tenantDb->table('settings')->get()->getResult();
        $settingsData = [];
        foreach ($settings as $setting) {
            $settingsData[$setting->setting_key] = $setting->setting_value;
        }

        // Get current user info
        $userInfo = $this->currentUser;

        $data = [
            'title' => 'Restaurant Profile - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Restaurant Profile',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'settings' => (object) $settingsData,
            'user_info' => $userInfo
        ];
        return view('restaurant/profile', $data);
    }

    public function settings()
    {
        $data = [
            'title' => 'Settings - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Settings',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser
        ];
        return view('restaurant/settings', $data);
    }

    /**
     * Add new menu item via AJAX
     */
    public function addMenuItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $rules = [
            'category_id' => 'required|integer',
            'name' => 'required|min_length[3]|max_length[100]',
            'price' => 'required|decimal',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['error' => $this->validator->getErrors()])->setStatusCode(400);
        }

        try {
            $menuData = [
                'tenant_id' => $this->tenantId,
                'category_id' => $this->request->getPost('category_id'),
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'price' => $this->request->getPost('price'),
                'cost_price' => $this->request->getPost('cost_price') ?: 0,
                'vat_type' => $this->request->getPost('vat_type') ?: 'vatable',
                'preparation_time' => $this->request->getPost('preparation_time') ?: 15,
                'is_available' => 1,
                'display_order' => $this->request->getPost('display_order') ?: 0
            ];

            $menuId = $this->tenantDb->table('menu_items')->insert($menuData);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu item added successfully',
                'menu_id' => $menuId
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Add new menu category via AJAX
     */
    public function addMenuCategory()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['error' => $this->validator->getErrors()])->setStatusCode(400);
        }

        try {
            $categoryData = [
                'tenant_id' => $this->tenantId,
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'color_code' => $this->request->getPost('color_code') ?: '#007bff',
                'is_active' => 1,
                'display_order' => $this->request->getPost('display_order') ?: 0
            ];

            $categoryId = $this->tenantDb->table('menu_categories')->insert($categoryData);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu category added successfully',
                'category_id' => $categoryId
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

}
