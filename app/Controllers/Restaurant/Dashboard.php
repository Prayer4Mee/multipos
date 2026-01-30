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


    /**
     * Dashboard System - full
     */
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
            // Orders today excluding created and cancelled
            $orderStats['orders_today'] = $this->tenantDb->table('orders')
                                                ->where('DATE(ordered_at)', $today)
                                                ->whereNotIn('status', ['created', 'cancelled'])
                                                ->countAllResults();
            // Pending Orders
            $orderStats['pending_orders'] = $this->tenantDb->table('orders')
                                                         ->where('status', 'pending')
                                                         ->where('DATE(ordered_at)', $today)
                                                         ->countAllResults();
            // Preparing Orders
            $orderStats['preparing_orders'] = $this->tenantDb->table('orders')
                                                           ->where('status', 'preparing')
                                                           ->where('DATE(ordered_at)', $today)
                                                           ->countAllResults();
            // Completed Orders - served + paid
            $orderStats['completed_orders'] = $this->tenantDb->table('orders')
                                                           ->where('status', 'completed')
                                                           ->where('payment_status', 'paid') // if needed along with completed status
                                                           ->where('DATE(ordered_at)', $today)
                                                           ->countAllResults();

            // Get active tables available, but not including unavailable
            $active_tables = $this->tenantDb->table('restaurant_tables')
                    ->where('tenant_id', $this->tenantId)
                    ->whereIn('status', ['available','occupied','reserved','cleaning'])
                    ->where('is_active', 1)
                    ->countAllResults();
                    
            // Get today's revenue
            $revenueResult = $this->tenantDb->table('orders')
                                          ->select('SUM(total_amount) as total_revenue')
                                          ->where('payment_status', 'paid')
                                          ->where('DATE(ordered_at)', $today)
                                          ->get()
                                          ->getRow();
            
            $orderStats['today_revenue'] = $revenueResult->total_revenue ?? 0;

            // Get recent orders with table information
            $recentOrders = $this->tenantDb->table('orders as o')
                                         ->select('o.*, rt.table_number, COUNT(oi.id) as item_count')
                                         ->join('restaurant_tables rt', 'o.table_id = rt.id', 'left')
                                         ->join('order_items oi', 'o.id = oi.order_id', 'left')
                                         ->groupBy('o.id')
                                         ->orderBy('o.created_at', 'DESC')
                                         ->limit(10)
                                         ->get()
                                         ->getResult();

            // Get Employees stats
            $staffMembers = $this->tenantDb->table('staff')
                         ->where('tenant_id', $this->tenantId)
                         ->orderBy('role, first_name')
                         ->get()
                         ->getResult();

            $totalEmployees = count($staffMembers);
            $activeEmployees = count(array_filter($staffMembers, function($staff) {
                return $staff->employment_status === 'active';
            }));

            // Group by role
            $employeesByRole = [];
            foreach ($staffMembers as $staff) {
                $role = $staff->role;
                if (!isset($employeesByRole[$role])) {
                    $employeesByRole[$role] = 0;
                }
                $employeesByRole[$role]++;
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to get order statistics: ' . $e->getMessage());
            $totalEmployees = 0;
            $activeEmployees = 0;
            $employeesByRole = [];
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
            'active_tables' => $active_tables,
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
                                ->orderBy('table_number', 'ASC')
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
            'restaurant_tables' => $tables
        ];
        return view('restaurant/pos', $data);
    }

    // Table Management Module!
    public function tables()
    {
        $tables = $this->tenantDb->table('restaurant_tables')
                                ->where('tenant_id', $this->tenantId) // <- Ensures tenant isolation
                                ->where('is_active', 1)  // <- ADD THIS: Only show active tables
                                ->orderBy('table_number', 'ASC')  // <- FIXED: Sort by table_number in ascending order
                                ->get()
                                ->getResult();
        
        // Sort numerically in PHP (handles text table_number)
        usort($tables, function($a, $b) {
            return (int)$a->table_number - (int)$b->table_number;
        });

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
                case 'unavailable':
                    // We just let totalCapacity count it, but not availableCapacity
                    break;
            }
        }

        // Find the next available table number (1-100)
        // Include BOTH active AND deleted tables to avoid suggesting deleted numbers
        $allTables = $this->tenantDb->table('restaurant_tables')
                            ->where('tenant_id', $this->tenantId)
                            ->get()
                            ->getResult();
        $allUsedNumbers = array_column($allTables, 'table_number');
        $nextTableNumber = 1;
        for ($i = 1; $i <= 100; $i++) {
            if (!in_array($i, $allUsedNumbers)) {
                $nextTableNumber = $i;
                break;
            }
        }

        // Get Deleted Tables(Soft Deleted Tables) (Restore Feature only)
        $deletedTables = $this->tenantDb->table('restaurant_tables')
                                    ->where('tenant_id', $this->tenantId)
                                    ->where('is_active', 0)
                                    ->orderBy('table_number', 'ASC')
                                    ->get()
                                    ->getResult();
        
        // Sort deleted tables numerically in PHP
        usort($deletedTables, function($a, $b) {
            return (int)$a->table_number - (int)$b->table_number;
        });

        // Get current date and time separately
        $currentDate = date('Y-m-d');      // Format: YYYY-MM-DD (for date input)
        $currentTime = date('H:i');        // Format: HH:mm (for time input)


        $data = [
            'title' => 'Table Management - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Table Management',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'restaurant_tables' => $tables,
            'deleted_tables' => $deletedTables,  // Note: For restore deleted feature
            'next_table_number' => $nextTableNumber,  // Note: Pass next available number to view
            'current_date' => $currentDate, // Seperate Date
            'current_time' => $currentTime, // Seperate Time
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
    public function saveTable()
    {
        $tableId = $this->request->getPost('table_id'); // Hidden field for edits
        $tableNumber = (int)$this->request->getPost('table_number');
        $status = $this->request->getPost('status');
        
        // Prevent Duplicate table numbers when creating
        if (!$tableId) {
            $existingTable = $this->tenantDb->table('restaurant_tables')
                                        ->where('tenant_id', $this->tenantId)
                                        ->where('table_number', $tableNumber)
                                        ->where('is_active', 1)
                                        ->get()
                                        ->getRow();
            
            if ($existingTable) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Table number ' . $tableNumber . ' already exists, Please use a different number or restore the deleted table'                   
                ]);
            }
        }
        // 2. Combine date and time into a datetime string if reserved
        $reservationDateTime = null;
        if ($status === 'reserved') {
            $resDate = $this->request->getPost('reservation_date');
            $resTime = $this->request->getPost('reservation_time');
            
            if (!empty($resDate) && !empty($resTime)) {
                // Combine into YYYY-MM-DD HH:mm:ss format for TIMESTAMP column
                $reservationDateTime = $resDate . ' ' . $resTime . ':00';
            }
        }
        
        // 3. Prepare Data
        $data = [
            'tenant_id' => $this->tenantId,
            'table_number' => (int)$this->request->getPost('table_number'),
            'capacity' => $this->request->getPost('capacity'),
            'location' => $this->request->getPost('location'),
            'status' => $status,
            'is_active' => 1,  // Always set to active when creating/updating
            // Add reservation data (only if reserved)
            'customer_name'    => ($status === 'reserved') ? $this->request->getPost('customer_name') : null,
            'reservation_time' => $reservationDateTime,
        ];
        
        try {
            if ($tableId) {
                // EDIT EXISTING - Security: ensure table belongs to tenant
                $this->tenantDb->table('restaurant_tables')
                            ->where('id', $tableId)
                            ->where('tenant_id', $this->tenantId)
                            ->update($data);
                 $message = '✅ Table #' . $tableNumber . ' updated successfully!';
            } 
            else {
                // CREATE NEW
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->tenantDb->table('restaurant_tables')->insert($data);
                $message = '✅ Table #' . $tableNumber . ' created successfully!';
            }
            
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        } 
        catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    public function deleteTable() {
        $tableId = $this->request->getPost('table_id');
        try {
            // Security: ensure table belongs to tenant before deleting
            // Soft delete: set is_active to 0
            $this->tenantDb->table('restaurant_tables')
                        ->where('id', $tableId)
                        ->where('tenant_id', $this->tenantId)
                        ->update(['is_active' => 0]);

           return $this->response->setJSON([
            'success' => true,
            'message' => 'Table deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

    }
    // restore feature only
    public function restoreTable() {
        $tableId = $this->request->getPost('table_id');

        try {
            // Restore table: set is_active back to 1
            $this->tenantDb->table('restaurant_tables')
                        ->where('id', $tableId)
                        ->where('tenant_id', $this->tenantId)
                        ->update(['is_active' => 1]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Table restored successfully'
            ]);
        } 
        catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    


    public function orders()
    {
        // 통합 주문 시스템 사용
        $orderModel = new \App\Models\Tenant\OrderModel();
        $orderModel->setDB($this->tenantDb);
        
        // Added this
        $orders = [];
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
                $order->guest_count = $order->guest_count ?? null; // add this line for guest count for which doesn't exist in the db
                $order->items = $this->tenantDb->table('order_items as oi')
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
        // 임시로 CSRF 검증 비활성화
        // if (!$this->request->isAJAX()) {
        //     return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        // }

        try {
            // 데이터베이스 연결 확인
            if (!$this->tenantDb) {
                return $this->response->setJSON(['error' => 'Database connection failed'])->setStatusCode(500);
            }
            
            $this->tenantDb->transStart();

            // Get form data
            $tableId = $this->request->getPost('table_id') ?: $this->request->getGet('table_id');
            $customerName = $this->request->getPost('customer_name') ?: $this->request->getGet('customer_name');
            $items = $this->request->getPost('items') ?: $this->request->getGet('items');
            $subtotal = $this->request->getPost('subtotal') ?: $this->request->getGet('subtotal');
            $serviceCharge = $this->request->getPost('service_charge') ?: $this->request->getGet('service_charge');
            $vat = $this->request->getPost('vat_amount') ?: $this->request->getGet('vat_amount');
            $total = $this->request->getPost('total_amount') ?: $this->request->getGet('total_amount');
            $paymentMethod = $this->request->getPost('payment_method') ?: 'cash';
            
            // Get form data v2 - getVar method
            // $requestMethod = $this->request->getMethod();
            // $tableId = $this->request->getVar('customer_name');
            // $items = $this->request->getVar('items');
            // $subtotal = $this->request->getVar('subtotal');
            // $serviceCharge = $this->request->getVar('service_charge');
            // $vat = $this->request->getVar('vat_amount');
            // $total = $this->request->getVar('total_amount');
            //$paymentMethod = $this->request->getVar('payment_method');

            // 디버깅을 위한 로그
            log_message('info', 'Creating order with data: ' . json_encode([
                'table_id' => $tableId,
                'customer_name' => $customerName,
                'items' => $items,
                'subtotal' => $subtotal,
                'total' => $total,
                'payment_method' => $paymentMethod
            ]));

            // Generate order number
            $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        
            // Create order
            $orderData = [
                'order_number' => $orderNumber,
                'order_type' => 'dine_in',
                'order_source' => 'pos',
                'table_id' => !empty($tableId) ? $tableId : null,
                'customer_name' => $customerName,
                'subtotal' => (float)$subtotal,
                'service_charge' => (float)$serviceCharge,
                'vat_amount' => (float)$vat, // formerly vat but in db vat_amount
                'total_amount' => (float)$total,
                'status' => 'created',
                'payment_method' => $paymentMethod,  // ✅ ADD THIS LINE
                'payment_status' => 'pending',
                'ordered_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')

            ];
            // Safeguard: Check if table exists
            $currentTable = $this->tenantDb->table('restaurant_tables')
                                        ->where('id', $tableId)
                                        ->get()
                                        ->getRow();
            // Added this for safeguarding multiple inserts of tables
            if (!$currentTable) {
                return $this->response->setJSON([
                    'error' => 'Table not Found'
                ])->setStatusCode(404);
            }
            if ($currentTable->status !== 'available') {
                return $this->response->setJSON([
                    'error' => 'Table is already '. $currentTable->status,
                    'message' => 'Please select another table or update its status to available.'
                ])->setStatusCode(400);
            }

            // Change this to the other method
            // $orderId = $this->tenantDb->table('orders')->insert($orderData);
            // New method is this:
            $this->tenantDb->table('orders')->insert($orderData);
            $orderId = $this->tenantDb->insertID();
            // ADD THIS: Link the order to the table in the bridge table
            if (!empty($tableId)) {
                $this->tenantDb->table('order_tables')->insert([
                    'order_id' => $orderId,
                    'table_id' => $tableId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Add order items
            // Problem Here: The JS sends an array but the code is trying to use json_decode
            // json_decode expects a string. so we need to fix that. From:
            // $itemsArray = json_decode($items, true);
            // To:
            $itemsArray = is_array($items) ? $items : json_decode($items, true);
            if ($itemsArray) {
                foreach ($itemsArray as $item) {
                    $itemData = [
                        'order_id' => $orderId,
                        'menu_item_id' => $item['id'],
                        'item_name' => $item['name'],
                        'unit_price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'total_price' => round($item['price'] * $item['quantity'], 2),
                        'kitchen_status' => 'pending'
                    ];
                    $this->tenantDb->table('order_items')->insert($itemData);
                }
            }

            // Get table number for kitchen orders
            $table = $this->tenantDb->table('restaurant_tables')
                                  ->where('id', $tableId)
                                  ->get()
                                  ->getRow();

            // Add to kitchen orders
            $kitchenOrderData = [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'table_number' => $table ? $table->table_number : null,
                'customer_name' => $customerName,
                'status' => 'pending',
                'priority' => 'normal',
                'estimated_time' => 15
            ];
            $this->tenantDb->table('kitchen_orders')->insert($kitchenOrderData);

            // Update table status
            $this->tenantDb->table('restaurant_tables')
                          ->where('id', $tableId)
                          ->update(['status' => 'occupied']);

            $this->tenantDb->transComplete();

            if ($this->tenantDb->transStatus() === false) {
                return $this->response->setJSON(['error' => 'Transaction failed'])
                ->setStatusCode(500);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                // Redirecting
                'redirect_url' => base_url("restaurant/" . ($this->tenantConfig->tenant_slug ?? 'default'). "/payment" . $orderId)
            ]);

        } // Inside your Controller's create-order method catch block:
        catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Transaction failed',
                'debug' => $e->getMessage(), // See the actual error (e.g., "Column 'X' not found")
                'db_error' => $this->tenantDb->error() // See the specific SQL error
            ]);
        }
    }
    /**
     * Add new order
     */
    public function newOrder()
    {

        // Get menu categories
        $menuCategories = $this->tenantDb->table('menu_categories')
                                        ->where('tenant_id', $this->tenantId)
                                        ->where('is_active', 1)
                                        ->orderBy('display_order')
                                        ->get()
                                        ->getResult();
        
        // Get menu items
        $menuItems = $this->tenantDb->table('menu_items as mi')
                                ->select('mi.*, mc.name as category_name')
                                ->join('menu_categories as mc', 'mi.category_id = mc.id')
                                ->where('mi.tenant_id', $this->tenantId)
                                ->where('mi.is_available', 1)
                                ->orderBy('mc.display_order, mi.display_order')
                                ->get()
                                ->getResult();
        
        // Get tables
        $tables = $this->tenantDb->table('restaurant_tables')
                                ->where('tenant_id', $this->tenantId)
                                ->where('is_active', 1)
                                ->orderBy('table_number', 'ASC')
                                ->get()
                                ->getResult();

        // Get tables from database
        $tables = $this->tenantDb->table('restaurant_tables')
                                ->where('tenant_id', $this->tenantId)
                                ->where('is_active', 1)
                                ->orderBy('table_number', 'ASC')
                                ->get()
                                ->getResult();
        
        // Sort numerically
        usort($tables, function($a, $b) {
            return (int)$a->table_number - (int)$b->table_number;
        });
        
        $data = [
            'title' => 'New Order',
            'page_title' => 'New Order',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'tables' => $tables,
            'menu_categories' => $menuCategories, 
            'menu_items' => $menuItems,            
            'tables' => $tables,                   
        ];
        
        return view('restaurant/new_order', $data);
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
                'message' => 'Order status updated successfully',
                // whenever I tried to use csrf_token() here, it will renew it
                csrf_token() => csrf_hash() // ← Return new token
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
            return $this->response->setJSON([
                'error' => 'Invalid request method'
                ])->setStatusCode(400);
        }
         $data = $this->request->getPost();

        // Fields to save to settings table
        $fieldsToSave = [
            'restaurant_name', 
            'currency', 
            'tax_rate', 
            'service_charge_rate', 
            'theme_color'
        ];
    
        try {
            foreach ($fieldsToSave as $key) {
                if (isset($data[$key])) {
                    $value = $data[$key];

                    // Check if setting already exists
                    $existing = $this->tenantDb->table('settings')
                                            ->where('setting_key', $key)
                                            ->get()
                                            ->getRow();
                    
                    if ($existing) {
                        // Update existing setting
                        $this->tenantDb->table('settings')
                                    ->where('setting_key', $key)
                                    ->update([
                                        'setting_value' => $value,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                    } else {
                        // Create new setting
                        $this->tenantDb->table('settings')
                                    ->insert([
                                        'setting_key' => $key,
                                        'setting_value' => $value,
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                    }
                }
            }

            return $this->response->setJSON([
            'success' => true,
            'message' => 'Profile updated successfully!'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
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
            // 1. Prepare the basic update for date and time
            $updateData = ['status' => $status];
            
            // Add reservation details if status is reserved
            if ($status === 'reserved') {
                $reservationTime = $this->request->getPost('reservation_time') ?: null;
                $customerName = $this->request->getPost('customer_name') ?: '';
                
                // 2. Save time if provided, otherwise null
                $updateData['reservation_time'] = !empty($reservationTime) ? $reservationTime : null;
                // 3. Save Name OR 'Guest' if empty
                $updateData['customer_name'] = !empty($customerName) ? $customerName : 'Guest';
            }
            else{
                // 4. AUTO-CLEAR: If status is NOT reserved, wipe the old data
                $updateData['reservation_time'] = null;
                $updateData['customer_name'] = null;
            }

            log_message('debug', 'Update data: ' . json_encode($updateData));

            $result = $this->tenantDb->table('restaurant_tables')
                          ->where('id', $tableId)
                          ->update($updateData);
            // $affectedRows = $this->tenantDb->affectedRows();
            // log_message('debug', "Affected rows AFTER update: {$affectedRows}");
            // log_message('debug', "Table ID: {$tableId}, Tenant ID: {$this->tenantId}");              

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

// Note: You're about to go down inventory system
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
    /**
    * Add new inventory item via AJAX
    */
    public function addInventoryItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'error' => 'Invalid request method'
                ])->setStatusCode(400);
        }
        // Let's update that!
        $rules = [
            'item_name' => 'required|min_length[3]|max_length[100]',
            'category' => 'required|min_length[3]|max_length[50]',
            'current_stock' => 'required|decimal',
            'reorder_level' => 'required|decimal',
            'unit_cost' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => $this->validator->getErrors()
                ])->setStatusCode(400);
        }

        try {
            // Check for duplicate item name/item_code
            $duplicateCheck = $this->tenantDb->table('inventory_items')
                                           ->where('tenant_id', $this->tenantId)
                                           ->groupStart()
                                           ->where('item_name', $this->request->getPost('item_name'))
                                           ->orWhere('item_code', $this->request->getPost('item_code'))
                                           ->groupEnd()
                                           ->get()
                                           ->getRow();

            if ($duplicateCheck) {
                return $this->response->setJSON([
                    'error' => 'item_name or item_code already exists'
                    ])->setStatusCode(400);
            }

            $inventoryItemData = [
                'tenant_id' => $this->tenantId,
                'item_name' => $this->request->getPost('item_name'),
                'item_code' => $this->request->getPost('item_code') ?: null,
                'category' => $this->request->getPost('category'),
                'unit_of_measure' => $this->request->getPost('unit_of_measure') ?: null,
                'current_stock' => $this->request->getPost('current_stock'),
                'reorder_level' => $this->request->getPost('reorder_level'),
                'unit_cost' => $this->request->getPost('unit_price'),
                'supplier_id' => $this->request->getPost('supplier_id') ?: null,
                'storage_location' => $this->request->getPost('storage_location') ?: null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert Inventory Item
            $this->tenantDb->table('inventory_items')
                        ->insert($inventoryItemData);
            $itemId = $this->tenantDb->insertID;

            // // Update inventory
            // $result = $this->tenantDb->table('inventory_items')
            //                        ->where('id', $inventoryId)
            //                        ->where('tenant_id', $this->tenantId)
            //                        ->update($updateData);
            
            // if (!$result) {
            return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Inventory item added successfully',
                    'item_id' => $itemId
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Inventory add error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'An error occurred while adding inventory item'
                ])->setStatusCode(500);
        }
    }
    /**
    * Update inventory items
    */ 
    public function updateInventoryStock($inventoryId = null)
    {
        // if (!$inventoryId) {
        //     return $this->response->setJSON([
        //         'error' => 'Inventory ID is required'
        //     ])->setStatusCode(400);
        // }
         if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'error' => 'Invalid request method'
                ])->setStatusCode(400);
        }
        $id = (int) $this->request->getPost('inventory_id');
        
        if (!$id) {
            return $this->response->setJSON([
                'error' => 'Inventory ID is required'
            ])->setStatusCode(400);
        }

        log_message('info', "Looking for inventory ID: $id in tenant: $this->tenantId");
        
        $rules = [
            'new_stock' => 'required|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => $this->validator->getErrors()
            ])->setStatusCode(400);       
        }

        try {
            // Check if item exists - use $id not $inventoryId
            $existingItem = $this->tenantDb->table('inventory_items')
                                        ->where('id', $id)
                                        ->where('tenant_id', $this->tenantId)
                                        ->get()
                                        ->getRow();

            log_message('info', "Found item: " . json_encode($existingItem));

            if (!$existingItem) {
                return $this->response->setJSON([
                    'error' => 'Inventory item not found'
                ])->setStatusCode(404);
            }

            $newStock = $this->request->getPost('new_stock');
            $reason = $this->request->getPost('reason') ?: null;
            
            $updateData = [
                'current_stock' => $newStock,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update inventory item - use $id not $inventoryId
            $result = $this->tenantDb->table('inventory_items')
                                ->where('id', $id)
                                ->where('tenant_id', $this->tenantId)
                                ->update($updateData);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Inventory stock updated successfully',
                    'new_stock' => $newStock,
                    'csrf' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => 'Failed to update inventory item'
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Inventory update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'An error occurred while updating inventory stock'
            ])->setStatusCode(500);
        }
    }

    /**
    * Delete inventory item
    */
    public function deleteInventoryItem($inventoryId = null){
        if (!$this->request->isAJAX()){
            return $this->response->setJSON([
                'error' => 'Invalid Request'
                ])->setStatusCode(400);
        }
        // initializes - Get ID from route parameter or POST data
        $id = $inventoryId;

        if (!$id) {
            return $this->response->setJSON
            (['success' => false, 
            'error' => 'Inventory ID is required!'
            ])->setStatusCode(400);
        }

        try {
            // Get inventory item to ensure it exists
            // Get id to ensure it exist
            $item = $this->tenantDb->table('inventory_items')
                                    ->where('id', $id)
                                    ->where('tenant_id', $this->tenantId)
                                    ->get()
                                    ->getRow();
            if (!$item) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Inventory item not found!'
                ])->setStatusCode(404); 
            }
            // Delete inventory item                        
            $this->tenantDb->table('inventory_items')
                        ->where('id', $id)
                        ->where('tenant_id', $this->tenantId)
                        ->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => "Inventory member {$item->item_name} {$item->item_code} deleted successfully"
                ]);
                
        } catch (\Exception $e) {
            log_message('error', 'Inventory deletion error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'error' => $e->getMessage()
                ])->setStatusCode(500);
        }
    }

// You're 'bout to go down through staff section
    /**
    * Staff
    */                   
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
                                         ->where('employment_status', 'active')
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

     /**
     * Add new staff via AJAX
     */
    public function addStaff()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'error' => 'Invalid request method'
                ])->setStatusCode(400);
        }
        // Let's update that!
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'username' => 'required|min_length[3]|max_length[50]',
            'email' => 'required|valid_email|max_length[100]',
            'role' => 'required|in_list[manager, cashier, kitchen staff, waiter, staff]',
            'employee_id' => 'required|min_length[2]|max_length[50]',
            'password' => 'required|min_length[2]|max_length[50]',
            'employment_status' => 'required|in_list[active, inactive]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => $this->validator
                            ->getErrors()
            ])->setStatusCode(400);
        }
        try {
            $password = $this->request->getPost('password');
            
            $staffData = [
                'tenant_id' => $this->tenantId,
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'role' => $this->request->getPost('role'),
                'employee_id' => $this->request->getPost('employee_id'),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'employment_status' => $this->request->getPost('employment_status'),
                'phone' => $this->request->getPost('phone') ?: null,
                'hire_date' => $this->request->getPost('hire_date') ?: null,
                'salary' => $this->request->getPost('salary') ?: null,
                'address' => $this->request->getPost('address') ?: null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')          
            ];

            // Check for duplicates
            $duplicate = $this->tenantDb->table('staff')
                                    ->where('tenant_id', $this->tenantId)
                                    ->groupStart()
                                    ->where('username', $staffData['username'])
                                    ->orwhere('email', $staffData['email'])
                                    ->orwhere('employee_id', $staffData['employee_id'])
                                    ->groupEnd()
                                    ->get()
                                    ->getRow();
            if ($duplicate) {
                return $this->response->setJSON([
                    'error' => 'Username, email, or employee ID already exists'
                ])->setStatusCode(400);
            }
            // Time to insert staff after the validation 
            $this->tenantDb->table('staff')->insert($staffData);
            $staffId = $this->tenantDb->insertId();
            
            // Return response
            return $this->response->setJSON([
                'success' => true,
                'message' => 'staff added successfully',
                'staff_id' => $staffId
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Staff and error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Error in adding staff member: ' . $e->getMessage()
                ])->setStatusCode(500);
        }
    }

    /**
     * Get Staff so they can be processed into updateStaff function
     */  
    public function getStaff($staffId) {
        try{
            log_message('info', "Looking for staff ID: $staffId in tenant: $this->tenantId");
            $staff =$this->tenantDb->table('staff')
                                ->where('id', $staffId)
                                ->where('tenant_id', $this->tenantId)
                                ->get()
                                ->getRow();
            if (!$staff) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Staff member not found'
                ])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'success' => true,
                'staff' => $staff
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Staff fetch error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching staff member'
            ])->setStatusCode(500);
        }
    }

    /**
    * Updates Staff After editStaff function
    */                   
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

    /**
    * Delete a staff
    */
    public function deleteStaff($staffId = null){
        if (!$this->request->isAJAX()){
            return $this->response->setJSON(['error' => 'Invalid Request'])->setStatusCode(400);
        }

        $id = $staffId ?? $this->request->getPost('staff_id'); // it accepts both
        // Get staff to ensure it exist
        $staff = $this->tenantDb->table('staff')
                            ->where('id', $id)
                            ->where('tenant_id', $this->tenantId)
                            ->get()
                            ->getRow();

        if (!$staff){
            return $this->response->setJSON
            (['success' => false, 
            'error' => 'Staff member not found!'
            ])->setStatusCode(400);
        }
        try {
            $this->tenantDb->table('staff')
                        ->where('id', $id)
                        ->where('tenant_id', $this->tenantId)
                        ->delete();
            return $this->response->setJSON([
                'success' => true,
                'message' => "Staff member {$staff->first_name} {$staff->last_name} deleted successfully"
                ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()])->setStatusCode(500);
        }
    }


// Take note: below is POS System - this is the left side
    public function currentOrders()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        try {
            // 통합 주문 시스템 사용
            $orderModel = new \App\Models\Tenant\OrderModel();
            // Set the database connection for the model
            $orderModel->setDB($this->tenantDb);
           
            // 활성 주문만 필터링 (완료되지 않은 주문)
            // Get ACTIVE orders only (not paid, not completed)
            $orders = $this->tenantDb->table('orders as o')
                                ->select('o.*, rt.table_number, COUNT(oi.id) as item_count')
                                ->join('restaurant_tables rt', 'o.table_id = rt.id', 'left')
                                ->join('order_items oi', 'o.id = oi.order_id', 'left')
                                ->where('o.payment_status !=', 'paid')  // Exclude paid orders
                                ->where('o.status !=', 'completed')     // Exclude completed orders
                                ->where('o.status !=', 'cancelled')     // Exclude cancelled orders
                                ->groupBy('o.id')
                                ->orderBy('o.created_at', 'DESC')
                                ->get()
                                ->getResult();
            
            // Add guest count and items for each order
            foreach ($orders as $order) {
                $order->guest_count = $order->guest_count ?? null;
                $order->items = $this->tenantDb->table('order_items as oi')
                                            ->select('oi.*, mi.name as menu_item_name')
                                            ->join('menu_items mi', 'oi.menu_item_id = mi.id', 'left')
                                            ->where('oi.order_id', $order->id)
                                            ->get()
                                            ->getResult();
            }
            // // Filter out orders that are already paid
            // $orders = array_filter($orders, function($order) {
            //     $status = property_exists($order, 'payment_status') ? strtolower($order->payment_status) : '';
            //     return $status !== 'paid';
            // });




            return $this->response->setJSON([
                'success' => true,
                'orders' => $orders
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to get current orders: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load orders'])->setStatusCode(500);
        }
    }
// To check Order Details when you click and show you the details on the right side
    // To check Order Details when you click and show you the details on the right side
    public function orderDetails($orderId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }
        
        try {
            // Get order details
            $order = $this->tenantDb->table('orders')
                            ->where('id', $orderId)
                            ->get()
                            ->getRow();

            log_message('info', "Order found: " . ($order ? "YES" : "NO"));
            if (!$order) {
                return $this->response->setJSON(['error' => 'Order not found'])->setStatusCode(404);
            }

            // ============================================
            // Get table number separately (if table_id exists)
            // ============================================
            if ($order->table_id) {
                $table = $this->tenantDb->table('restaurant_tables')
                                    ->where('id', $order->table_id)
                                    ->get()
                                    ->getRow();
                
                if ($table) {
                    $order->table_number = $table->table_number;
                } else {
                    $order->table_number = null;
                }
            } else {
                $order->table_number = null;
            }
            log_message('info', "Table number: " . ($order->table_number ?? 'NULL'));

            // ============================================
            // Get order items
            // ============================================
            $order->items = $this->tenantDb->table('order_items as oi')
                                    ->select('oi.*, mi.name as menu_item_name')
                                    ->join('menu_items mi', 'oi.menu_item_id = mi.id', 'left')
                                    ->where('oi.order_id', $orderId)
                                    ->get()
                                    ->getResult();
            
            // DEBUG LOGS (BEFORE return!)
            log_message('info', "Items Count: " . count($order->items));
            log_message('info', "Items Data: " . json_encode($order->items));

            // RETURN (after all logs!)
            return $this->response->setJSON([
                'success' => true,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get order details: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
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

    

    public function reports()
    {
         return redirect()->to(
        base_url("restaurant/{$this->tenantId}/reports")
        );
    }


    public function profile()
    {
        // Get restaurant settings from database
        $settings = $this->tenantDb->table('settings')
                                ->get()
                                ->getResult();
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
            'user_info' => $userInfo,

            // Add real statistics
            'total_tables' => $this->tenantDb->table('restaurant_tables')->get()->getNumRows(),
            'menu_items' => $this->tenantDb->table('menu_items')->get()->getNumRows(),
            'active_orders' => $this->tenantDb->table('order_tables')->get()->getNumRows(),
            'today_revenue' => $this->getTodayRevenue() // Calling private function getTodayRevenue()
        ];
        return view('restaurant/profile', $data);
    }
    
    private function getTodayRevenue() {
        
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $result = $this->tenantDb->table('orders')
                                ->selectSum('total_amount')
                                ->where('ordered_at >=', $today . ' 00:00:00') // ordered_at instead of
                                ->where('ordered_at <', $tomorrow . ' 00:00:00') // created_at
                                ->where('payment_status', 'paid') // Count paid orders only
                                ->get()
                                ->getRow();
            return $result->total_amount ?? 0;
    }

// Note: Settings down here
// Good News: dalawang method lang need ko ahahahaha
    public function settings()
    {   
        // Took a copy from method profile
        // Get restaurant settings from database
        $settings = $this->tenantDb->table('settings')
                                ->get()
                                ->getResult();
        foreach ($settings as $setting) {
            $settingsData[$setting->setting_key] = $setting->setting_value;
        }
        $data = [
            'title' => 'Settings - ' . $this->tenantConfig->restaurant_name,
            'page_title' => 'Settings',
            'tenant' => $this->tenantConfig,
            'tenant_slug' => $this->tenantId,
            'current_user' => $this->currentUser,
            'settings' => (object) $settingsData // Converts the array directly to an object on the fly
            // 'settings' => $this->getSettings() // Load all settings as key-value
        ];
        return view('restaurant/settings', $data);
    }
    /**
     * Save your settings whenever you edit 
     */
    public function saveSettings()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'error' => 'Invalid request method'
                ])->setStatusCode(400);
        }
        // Use getPost() instead of getJSON()
        $data = $this->request->getPost();
        $fieldsToSave = [
            'restaurant_name', 'business_type', 'currency', 'timezone',
            'vat_rate', 'service_charge_rate', 'min_order_amount', 'delivery_fee',
            'owner_name', 'owner_email', 'owner_phone', 'business_address',
            'theme_color', 'receipt_header', 'receipt_footer', 'auto_print_receipt',
            'tin_number', 'bir_permit_number', 'vat_registered'
        ];
        try {
            foreach ($fieldsToSave as $key) {
                if (isset($data[$key])) {
                    $value = $data[$key];

                    // Check if setting already exists
                    $existing = $this->tenantDb->table('settings')
                                            ->where('setting_key', $key)
                                            ->get()
                                            ->getRow();
                    if ($existing) {
                        // Update Existing Setting for your tenant
                        $this->tenantDb->table('settings')
                                    ->where('setting_key', $key)
                                    ->update ([
                                        'setting_value' => $value,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                    } 
                    else {
                        // Create New Setting if no current existing setting applicable
                        $this->tenantDb->table('settings')
                                    ->insert([
                                        'setting_key' => $key,
                                        'setting_value' => $value,
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                    }
                }
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Settings saved successfully!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
                ])->setStatusCode(500);
        }
    }

// Take note: Below is the Menu Management

    // Menu rin tong isa oow
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
                                       ->join('menu_categories as mc', 'mi.category_id = mc.id')
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

    /**
     * Add new menu item via AJAX
     */
    public function addMenuItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }
        // Let's update that!
        $rules = [
            'category_id' => 'required|integer',
            'name' => 'required|min_length[3]|max_length[100]',
            'price' => 'required|decimal',
            'description' => 'permit_empty|max_length[500]',
            'color_code' => 'permit_empty|max_length[7]'
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
            // Problem: AJAX response always ends up with menu_id = true
            // $menuId = $this->tenantDb->table('menu_items')->insert($menuData);
            $builder = $this->tenantDb->table('menu_items');
            $builder->insert($menuData);
            $menuId = $this->tenantDb->insertId();


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

    /**
     * Fetch a single menu item via AJAX
     */
    public function getMenuItem($id)
    {
        // Get item from the tenant database
        $item = $this->tenantDb->table('menu_items')
                            ->where('id', $id)
                            ->where('tenant_id', $this->tenantId)
                            ->get()
                            ->getRow();

        if ($item) {
            return $this->response->setJSON([
                'success' => true,
                'item' => $item
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Menu item not found'
            ])->setStatusCode(404);
        }
    }
    
    /**
    * Update a menu item via AJAX
    */
    public function updateMenuItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(400);
        }

        $id = $this->request->getPost('item_id');

        // Get item to ensure it exists
        $item = $this->tenantDb->table('menu_items')
                            ->where('id', $id)
                            ->where('tenant_id', $this->tenantId)
                            ->get()
                            ->getRow();

        if (!$item) {
            return $this->response->setJSON(['success' => false, 'error' => 'Menu item not found'])->setStatusCode(404);
        }

        // Prepare update data
        $updateData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price'),
            'category_id' => $this->request->getPost('category_id')
        ];

        try {
            $this->tenantDb->table('menu_items')->where('id', $id)->update($updateData);
            return $this->response->setJSON(['success' => true, 'message' => 'Menu item updated successfully']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()])->setStatusCode(500);
        }
    }
    /**
    * Delete a menu item
    */
    public function deleteMenuItem(){
        if (!$this->request->isAJAX()){
            return $this->response->setJSON(['error' => 'Invalid Request'])->setStatusCode(400);
        }

        $id = $this->request->getPost('item_id');
        // Get item to ensure it exist
        $item = $this->tenantDb->table('menu_items')
                            ->where('id', $id)
                            ->where('tenant_id', $this->tenantId)
                            ->get()
                            ->getRow();

        if (!$item){
            return $this->response->setJSON(['success' => false, 'error' => 'Menu item not found!'])->setStatusCode(400);
        }
        
        try {
            $this->tenantDb->table('menu_items')->where('id', $id)->delete();
            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()])->setStatusCode(500);
        }
    }


}
