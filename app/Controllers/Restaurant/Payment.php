<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;

class Payment extends BaseRestaurantController
{
    public function index($orderId = null)
    {
        try {
            // Dummy order data
            $orderId = $orderId ?? 1;
            
            $order = (object)[
                'id' => $orderId,
                'order_number' => 'ORD20241201004',
                'table_id' => 3,
                'table_number' => 3,
                'customer_name' => 'New Customer',
                'subtotal' => 200.00,
                'service_charge' => 20.00,
                'vat' => 16.00,
                'total_amount' => 200.00,
                'status' => 'preparing',
                'payment_status' => 'pending',
                'created_at' => '2024-12-01 03:48:00',
                'ordered_at' => '2024-12-01 03:48:00',
                'notes' => 'No onions, extra sauce'
            ];

            // Dummy order items
            $orderItems = [
                (object)[
                    'id' => 1,
                    'order_id' => $orderId,
                    'menu_item_id' => 1,
                    'menu_item_name' => 'Yum Burger',
                    'unit_price' => 100.00,
                    'quantity' => 2,
                    'total_price' => 200.00,
                    'special_instructions' => 'No onions'
                ]
            ];

            $data = [
                'title' => 'Payment - Jollibee Restaurant',
                'page_title' => 'Payment',
                'orderId' => $orderId,
                'order' => $order,
                'order_items' => $orderItems,
                'tenant' => $this->tenantConfig ?? (object)['restaurant_name' => 'Jollibee Restaurant'],
                'current_user' => session()->get('user') ?? (object)['name' => 'Staff User'],
            ];
            
            return view('restaurant/payment', $data);

        } catch (\Exception $e) {
            log_message('error', 'Payment view error: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function printReceipt($orderId = null)
    {
        try {
            $orderId = $orderId ?? 1;
            
            // Dummy order data
            $order = (object)[
                'id' => $orderId,
                'order_number' => 'ORD20241201004',
                'table_id' => 3,
                'table_number' => 3,
                'customer_name' => 'New Customer',
                'subtotal' => 200.00,
                'service_charge' => 20.00,
                'vat' => 16.00,
                'total_amount' => 200.00,
                'status' => 'completed',
                'payment_status' => 'completed',
                'payment_method' => 'cash',
                'amount_received' => 250.00,
                'change_amount' => 50.00,
                'created_at' => '2024-12-01 03:48:00',
                'ordered_at' => '2024-12-01 03:48:00',
                'completed_at' => date('Y-m-d H:i:s')
            ];

            // Dummy order items
            $orderItems = [
                (object)[
                    'id' => 1,
                    'order_id' => $orderId,
                    'menu_item_id' => 1,
                    'menu_item_name' => 'Yum Burger',
                    'unit_price' => 100.00,
                    'quantity' => 2,
                    'total_price' => 200.00
                ]
            ];
            
            $data = [
                'order' => $order,
                'order_items' => $orderItems,
                'tenant' => $this->tenantConfig ?? (object)['restaurant_name' => 'Jollibee Restaurant'],
            ];
            
            return view('restaurant/print-receipt', $data);

        } catch (\Exception $e) {
            log_message('error', 'Print receipt error: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function processPayment()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
        }

        try {
            $orderId = $this->request->getPost('order_id');
            $amountReceived = $this->request->getPost('amount_received');
            $paymentMethod = $this->request->getPost('payment_method');

            // Dummy order validation
            $order = (object)[
                'id' => $orderId,
                'total_amount' => 200.00,
                'table_id' => 3,
                'status' => 'preparing'
            ];

            if (!$order) {
                return $this->response->setJSON(['error' => 'Order not found'])->setStatusCode(404);
            }

            if ($amountReceived < $order->total_amount) {
                return $this->response->setJSON(['error' => 'Amount received is less than order total'])->setStatusCode(400);
            }

            // Simulate payment processing
            $changeAmount = $amountReceived - $order->total_amount;

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment completed successfully',
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'amount_received' => $amountReceived,
                'total_amount' => $order->total_amount,
                'change_amount' => $changeAmount
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }
}
