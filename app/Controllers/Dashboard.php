<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;

class Dashboard extends BaseRestaurantController
{
    /**
     * Main dashboard
     */
    public function index()
    {
        $this->requireRole(['manager', 'owner', 'accountant']);

        $data = [
            'title' => 'Dashboard',
            'today_sales' => $this->getTodaySales(),
            'today_orders' => $this->getTodayOrdersCount(),
            'active_tables' => $this->getActiveTables(),
            'low_stock_items' => $this->getLowStockItems(),
            'staff_on_duty' => $this->getStaffOnDuty(),
            'recent_orders' => $this->getRecentOrders(),
            'sales_chart_data' => $this->getSalesChartData()
        ];

        return $this->loadTenantView('dashboard/index', $data);
    }

    /**
     * Get today's sales
     */
    private function getTodaySales(): object
    {
        $today = date('Y-m-d');
        
        $sales = $this->tenantDb->query("
            SELECT 
                COUNT(id) as total_orders,
                SUM(total_amount) as gross_sales,
                SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_sales,
                AVG(total_amount) as avg_order_value
            FROM orders 
            WHERE DATE(ordered_at) = ? AND status != 'cancelled'
        ", [$today])->getRow();

        return $sales;
    }

    /**
     * Get today's order count
     */
    private function getTodayOrdersCount(): int
    {
        return $this->tenantDb->table('orders')
                             ->where('DATE(ordered_at)', date('Y-m-d'))
                             ->where('status !=', 'cancelled')
                             ->countAllResults();
    }

    /**
     * Get active tables
     */
    private function getActiveTables(): array
    {
        return $this->tenantDb->table('tables t')
                             ->select('t.*, o.order_number, o.total_amount, o.ordered_at')
                             ->join('orders o', 't.current_order_id = o.id', 'left')
                             ->where('t.status', 'occupied')
                             ->get()
                             ->getResult();
    }

    /**
     * Get low stock items
     */
    private function getLowStockItems(): array
    {
        return $this->tenantDb->query("
            SELECT name, current_stock, minimum_stock, unit
            FROM inventory_items 
            WHERE current_stock <= minimum_stock AND is_active = 1
            ORDER BY (current_stock / minimum_stock) ASC
            LIMIT 10
        ")->getResult();
    }

    /**
     * Get staff on duty
     */
    private function getStaffOnDuty(): array
    {
        return $this->tenantDb->table('users')
                             ->where('is_active', 1)
                             ->where('employment_status', 'active')
                             ->get()
                             ->getResult();
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders(): array
    {
        return $this->tenantDb->table('orders o')
                             ->select('o.*, t.table_number, u.first_name, u.last_name')
                             ->join('tables t', 'o.table_id = t.id', 'left')
                             ->join('users u', 'o.waiter_id = u.id', 'left')
                             ->orderBy('o.ordered_at', 'DESC')
                             ->limit(10)
                             ->get()
                             ->getResult();
    }

    /**
     * Get sales chart data for the last 7 days
     */
    private function getSalesChartData(): array
    {
        $data = $this->tenantDb->query("
            SELECT 
                DATE(ordered_at) as date,
                SUM(total_amount) as sales,
                COUNT(id) as orders
            FROM orders 
            WHERE ordered_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
            AND status != 'cancelled'
            GROUP BY DATE(ordered_at)
            ORDER BY date ASC
        ")->getResult();

        return $data;
    }

    /**
     * Orders management page
     */
    public function orders()
    {
        $this->requireRole(['manager', 'cashier', 'waiter', 'owner']);

        // Get orders with pagination
        $orders = $this->tenantDb->table('orders o')
                                ->select('o.*, t.table_number, COUNT(oi.id) as item_count')
                                ->join('tables t', 'o.table_id = t.id', 'left')
                                ->join('order_items oi', 'o.id = oi.order_id', 'left')
                                ->groupBy('o.id')
                                ->orderBy('o.created_at', 'DESC')
                                ->limit(50)
                                ->get()
                                ->getResult();

        // Get order statistics
        $order_stats = $this->getOrderStatistics();

        // Get tables for order creation
        $tables = $this->tenantDb->table('tables')
                                ->where('is_active', 1)
                                ->orderBy('table_number')
                                ->get()
                                ->getResult();

        // Get menu categories and items for order creation
        $menu_categories = $this->tenantDb->table('menu_categories')
                                         ->where('is_active', 1)
                                         ->orderBy('display_order')
                                         ->get()
                                         ->getResult();

        $menu_items = $this->tenantDb->table('menu_items mi')
                                    ->select('mi.*, mc.name as category_name')
                                    ->join('menu_categories mc', 'mi.category_id = mc.id')
                                    ->where('mi.is_active', 1)
                                    ->where('mi.is_available', 1)
                                    ->orderBy('mc.display_order, mi.display_order')
                                    ->get()
                                    ->getResult();

        $data = [
            'title' => 'Orders Management',
            'orders' => $orders,
            'order_stats' => $order_stats,
            'tables' => $tables,
            'menu_categories' => $menu_categories,
            'menu_items' => $menu_items
        ];

        return $this->loadTenantView('orders', $data);
    }

    /**
     * Get order statistics
     */
    private function getOrderStatistics(): array
    {
        $today = date('Y-m-d');
        
        $stats = $this->tenantDb->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing_orders,
                SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            FROM orders 
            WHERE DATE(created_at) = ? AND status != 'cancelled'
        ", [$today])->getRow();

        return [
            'total_orders' => $stats->total_orders ?? 0,
            'pending_orders' => $stats->pending_orders ?? 0,
            'preparing_orders' => $stats->preparing_orders ?? 0,
            'ready_orders' => $stats->ready_orders ?? 0,
            'completed_orders' => $stats->completed_orders ?? 0,
            'cancelled_orders' => $stats->cancelled_orders ?? 0
        ];
    }

    /**
     * Get order details
     */
    public function orderDetails($orderId)
    {
        $this->requireRole(['manager', 'cashier', 'waiter', 'owner']);

        try {
            // Get order details
            $order = $this->tenantDb->table('orders o')
                                   ->select('o.*, t.table_number')
                                   ->join('tables t', 'o.table_id = t.id', 'left')
                                   ->where('o.id', $orderId)
                                   ->get()
                                   ->getRow();

            if (!$order) {
                return $this->jsonResponse(['error' => 'Order not found'], 404);
            }

            // Get order items
            $orderItems = $this->tenantDb->table('order_items oi')
                                        ->select('oi.*, mi.name as menu_item_name, mi.price')
                                        ->join('menu_items mi', 'oi.menu_item_id = mi.id')
                                        ->where('oi.order_id', $orderId)
                                        ->get()
                                        ->getResult();

            // Format the response
            $order->items = $orderItems;

            return $this->jsonResponse([
                'success' => true,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus()
    {
        $this->requireRole(['manager', 'cashier', 'waiter', 'owner']);

        $rules = [
            'order_id' => 'required|integer',
            'status' => 'required|in_list[pending,preparing,ready,completed,cancelled]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
        }

        try {
            $orderId = $this->request->getPost('order_id');
            $newStatus = $this->request->getPost('status');

            // Update order status
            $this->tenantDb->table('orders')
                          ->where('id', $orderId)
                          ->update([
                              'status' => $newStatus,
                              'updated_at' => date('Y-m-d H:i:s')
                          ]);

            // If completing order, set completed_at
            if ($newStatus === 'completed') {
                $this->tenantDb->table('orders')
                              ->where('id', $orderId)
                              ->update(['completed_at' => date('Y-m-d H:i:s')]);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}

?>