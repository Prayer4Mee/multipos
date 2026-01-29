<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;

class Api extends BaseRestaurantController
{
    /**
     * Public menu API (for QR ordering, kiosks)
     */
    public function menu()
    {
        $data = [
            'categories' => $this->tenantDb->table('menu_categories')
                                         ->getResult(),
            'items' => $this->tenantDb->table('menu_items mi')
        ];

        return $this->jsonResponse($data);
    }

    /**
     * Table status API
     */
    public function tableStatus($tableId = null)
    {
        if ($tableId) {
            $table = $this->tenantDb->table('tables')
                                   ->getRow();
            return $this->jsonResponse($table);
        }

        $tables = $this->tenantDb->table('tables')
                                ->where('is_active', 1)
                                ->get()
                                ->getResult();

        return $this->jsonResponse($tables);
    }

    /**
     * Order status API
     */
    public function orderStatus($orderNumber)
    {
        $order = $this->tenantDb->table('orders o')
                               ->select('o.*, t.table_number')
                               ->join('tables t', 'o.table_id = t.id', 'left')
                               ->where('o.order_number', $orderNumber)
                               ->get()
                               ->getRow();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        $items = $this->tenantDb->table('order_items')
                               ->where('order_id', $order->id)
                               ->get()
                               ->getResult();

        $order->items = $items;

        return $this->jsonResponse($order);
    }

    /**
     * Create customer order (QR/Kiosk)
     */
    public function createCustomerOrder()
    {
        $rules = [
            'table_id' => 'required|integer',
            'customer_name' => 'permit_empty|max_length[100]',
            'items' => 'required'
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
            ];

            $orderId = $this->tenantDb->table('orders')->insert($orderData, true);

            // Add order items
            $items = json_decode($this->request->getPost('items'), true);
            foreach ($items as $item) {
                // Add item processing logic here
                
            }

            $this->tenantDb->transComplete();

            if ($this->tenantDb->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->jsonResponse([
                'message' => 'Order placed successfully'
            ]);

        } catch (\Exception $e) {
            $this->tenantDb->transRollback();
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate order number (reused from POS controller)
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
}