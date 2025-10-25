<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;

class Pos extends BaseRestaurantController
{
    /**
     * POS main interface
     */
    public function index()
    {
        $this->requireRole(['manager', 'cashier', 'waiter']);

        $data = [
            'title' => 'Point of Sale',
            'tenant' => (object) [
                'tenant_slug' => $this->tenantId,
                'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Restaurant'
            ],
            'current_user' => $this->currentUser
        ];

        return view('restaurant/pos', $data);
    }

    /**
     * New Order page
     */
    public function newOrder()
    {
        $this->requireRole(['manager', 'cashier', 'waiter']);

        $data = [
            'title' => 'New Order',
            'tenant' => (object) [
                'tenant_slug' => $this->tenantId,
                'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Restaurant'
            ],
            'current_user' => $this->currentUser,
            'menu_categories' => $this->getMenuCategories(),
            'menu_items' => $this->getMenuItems(),
            'tables' => $this->getTables()
        ];

        return view('restaurant/new_order', $data);
    }

    /**
     * Create new order
     */
    public function createOrder()
    {
        $this->requireRole(['manager', 'cashier', 'waiter']);

        $rules = [
            'table_id' => 'required|integer',
            'items' => 'required',
            'total_amount' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
        }

        try {
            $this->tenantDb->transStart();

            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Create order
            $orderData = [
                'order_number' => $orderNumber,
                'table_id' => $this->request->getPost('table_id'),
                'customer_name' => $this->request->getPost('customer_name'),
                'order_type' => $this->request->getPost('order_type') ?? 'dine_in',
                'order_source' => 'pos',
                'waiter_id' => $this->request->getPost('waiter_id') ?? $this->currentUser->id,
                'cashier_id' => $this->currentUser->id,
                'special_instructions' => $this->request->getPost('special_instructions'),
                'status' => 'confirmed',
                'ordered_at' => date('Y-m-d H:i:s')
            ];

            $orderId = $this->tenantDb->table('orders')->insert($orderData, true);

            // Add order items
            $items = json_decode($this->request->getPost('items'), true);
            foreach ($items as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'menu_item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'special_instructions' => $item['special_instructions'] ?? null
                ];

                $this->tenantDb->table('order_items')->insert($orderItemData);
            }

            // Update table status
            $this->tenantDb->table('tables')
                          ->where('id', $orderData['table_id'])
                          ->update([
                              'current_order_id' => $orderId
                          ]);

            $this->tenantDb->transComplete();

            if ($this->tenantDb->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Print to kitchen
            $this->printToKitchen($orderId);

            return $this->jsonResponse([
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'message' => 'Order created successfully'
            ]);

        } catch (\Exception $e) {
            $this->tenantDb->transRollback();
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get menu categories
     */
    private function getMenuCategories(): array
    {
        return $this->tenantDb->table('menu_categories')
                             ->where('is_active', 1)
                             ->orderBy('display_order')
                             ->get()
                             ->getResult();
    }

    /**
     * Get menu items
     */
    private function getMenuItems(): array
    {
        return $this->tenantDb->table('menu_items mi')
                             ->select('mi.*, mc.name as category_name')
                             ->join('menu_categories mc', 'mi.category_id = mc.id')
                             ->where('mi.is_active', 1)
                             ->where('mi.is_available', 1)
                             ->orderBy('mc.display_order, mi.display_order')
                             ->get()
                             ->getResult();
    }

    /**
     * Get tables
     */
    private function getTables(): array
    {
        return $this->tenantDb->table('tables')
                             ->where('is_active', 1)
                             ->orderBy('table_number')
                             ->get()
                             ->getResult();
    }

    /**
     * Get active orders
     */
    private function getActiveOrders(): array
    {
        return $this->tenantDb->table('orders o')
                             ->select('o.*, t.table_number')
                             ->join('tables t', 'o.table_id = t.id', 'left')
                             ->whereIn('o.status', ['pending', 'confirmed', 'preparing'])
                             ->orderBy('o.ordered_at', 'DESC')
                             ->get()
                             ->getResult();
    }

    /**
     * Generate order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = $this->tenantConfig->order_number_prefix ?? 'TP';
        $date = date('Ymd');
        
        $lastOrder = $this->tenantDb->table('orders')
                                   ->where('order_number LIKE', "{$prefix}{$date}%")
                                   ->orderBy('order_number', 'DESC')
                                   ->limit(1)
                                   ->get()
                                   ->getRow();

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    /**
     * Test method
     */
    public function test()
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => 'POS controller is working',
            'tenant' => $this->tenantId ?? 'unknown'
        ]);
    }

    /**
     * Get order details
     */
    public function orderDetails($orderId)
    {
        // Simple test response first
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Test response',
            'order_id' => $orderId,
            'tenant' => $this->tenantId ?? 'unknown'
        ]);

        $this->requireRole(['manager', 'cashier', 'waiter']);

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
     * Print order to kitchen
     */
    private function printToKitchen(int $orderId): void
    {
        // Get kitchen printers
        $printers = $this->tenantDb->table('printers')
                                  ->where('type', 'kitchen')
                                  ->where('is_active', 1)
                                  ->get()
                                  ->getResult();

        // Get order details
        $order = $this->tenantDb->table('orders o')
                               ->select('o.*, t.table_number')
                               ->join('tables t', 'o.table_id = t.id', 'left')
                               ->where('o.id', $orderId)
                               ->get()
                               ->getRow();

        $orderItems = $this->tenantDb->table('order_items oi')
                                    ->select('oi.*, mi.kitchen_station')
                                    ->join('menu_items mi', 'oi.menu_item_id = mi.id')
                                    ->where('oi.order_id', $orderId)
                                    ->get()
                                    ->getResult();

        foreach ($printers as $printer) {
            // Filter items by kitchen station if configured
            $stationsToProcess = json_decode($printer->kitchen_stations ?? '[]', true);
            
            if (empty($stationsToProcess)) {
                // Print all items if no station filter
                $itemsToPrint = $orderItems;
            } else {
                // Filter items by station
                $itemsToPrint = array_filter($orderItems, function($item) use ($stationsToProcess) {
                    return in_array($item->kitchen_station, $stationsToProcess);
                });
            }

            if (!empty($itemsToPrint)) {
                $this->sendToKitchenPrinter($printer, $order, $itemsToPrint);
            }
        }
    }

    /**
     * Send order to kitchen printer
     */
    private function sendToKitchenPrinter(object $printer, object $order, array $items): void
    {
        // Kitchen printer implementation would go here
        // This could use ESC/POS commands or a printing service
        
        // Log the print job
        log_message('info', "Kitchen order printed for Order #{$order->order_number} to printer {$printer->name}");
    }
}