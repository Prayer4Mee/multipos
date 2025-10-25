<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;

class Kitchen extends BaseRestaurantController
{
    protected $helpers = ['url', 'form', 'session'];
    
    /**
     * Kitchen Display System
     */
    public function index()
    {
        try {
            $data = [
                'title' => 'Kitchen Display System - Jollibee',
                'page_title' => 'Kitchen Display',
                'tenant' => (object) [
                    'slug' => $this->tenantId,
                    'tenant_slug' => $this->tenantId,
                    'restaurant_name' => 'Jollibee'
                ],
                'tenant_slug' => $this->tenantId,
                'current_user' => $this->currentUser,
                'pending_orders' => $this->getPendingOrdersForDisplay(),
                'stations' => [],
                'base_url' => base_url()
            ];

            return view('kitchen/display/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Kitchen index error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get orders for AJAX requests (통합 시스템 사용)
     */
    public function ajaxOrders()
    {
        try {
            // 통합 주문 시스템 사용
            $orderModel = new \App\Models\Tenant\OrderModel();
            $orderModel->setDB($this->tenantDb);
            
            // 요리 관련 주문만 필터링
            $filters = [
                'status' => ['created', 'pending', 'confirmed', 'preparing', 'ready']
            ];
            
            $orders = $orderModel->getOrdersWithDetails($filters);
            
            // Kitchen 표시용으로 포맷팅
            $formattedOrders = [];
            foreach ($orders as $order) {
                $orderDetails = $orderModel->getOrderWithItems($order->id);
                if ($orderDetails) {
                    $formattedOrders[] = [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'table_numbers' => $order->table_numbers,
                        'waiter_names' => $order->waiter_names,
                        'ordered_at' => $order->ordered_at,
                        'special_instructions' => $order->special_instructions,
                        'priority_level' => $order->priority_level,
                        'status' => $order->status,
                        'order_type' => $order->order_type,
                        'items' => array_map(function($item) {
                            return [
                                'name' => $item->quantity . 'x ' . $item->item_name,
                                'status' => $item->kitchen_status
                            ];
                        }, $orderDetails->items)
                    ];
                }
            }
            
            return $this->jsonResponse($formattedOrders);
        } catch (\Exception $e) {
            log_message('error', 'Kitchen ajaxOrders error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update order status (통합 시스템 사용)
     */
    public function updateOrderStatus()
    {
        try {
            $orderId = $this->request->getPost('order_id') ?? $this->request->getGet('order_id');
            $status = $this->request->getPost('status') ?? $this->request->getGet('status');

            if (!$orderId || !$status) {
                return $this->jsonResponse(['error' => 'Missing order_id or status'], 400);
            }

            // 통합 주문 시스템 사용
            $orderModel = new \App\Models\Tenant\OrderModel();
            $orderModel->setDB($this->tenantDb);
            
            $result = $orderModel->updateOrderStatus($orderId, $status, $this->currentUser->id);

            if ($result) {
                return $this->jsonResponse(['success' => true, 'message' => 'Order status updated successfully']);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update order status'], 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Kitchen updateOrderStatus error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get pending orders for display (formatted for frontend)
     */
    private function getPendingOrdersForDisplay(): array
    {
        try {
            $orders = $this->tenantDb->query("
                SELECT 
                    o.id,
                    o.order_number,
                    o.table_id,
                    rt.table_number,
                    o.ordered_at,
                    o.special_instructions,
                    o.priority_level,
                    o.kitchen_status,
                    o.order_type,
                    o.status as order_status,
                    CONCAT(u.first_name, ' ', u.last_name) as waiter_name,
                    GROUP_CONCAT(
                        CONCAT(
                            oi.quantity, 'x ', oi.item_name,
                            CASE 
                                WHEN oi.special_instructions IS NOT NULL AND oi.special_instructions != '' 
                                THEN CONCAT(' (', oi.special_instructions, ')')
                                ELSE ''
                            END,
                            '|', oi.kitchen_status
                        ) SEPARATOR '||'
                    ) as items_data
                FROM orders o
                LEFT JOIN restaurant_tables rt ON o.table_id = rt.id
                LEFT JOIN staff u ON o.waiter_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.status NOT IN ('cancelled', 'completed')
                AND o.kitchen_status IN ('pending', 'preparing', 'ready')
                GROUP BY o.id
                ORDER BY 
                    o.priority_level DESC,
                    o.ordered_at ASC
            ")->getResult();

            $formattedOrders = [];
            foreach ($orders as $order) {
                $items = [];
                if ($order->items_data) {
                    $itemsData = explode('||', $order->items_data);
                    foreach ($itemsData as $itemData) {
                        $parts = explode('|', $itemData);
                        if (count($parts) >= 2) {
                            $items[] = [
                                'name' => $parts[0],
                                'status' => $parts[1]
                            ];
                        }
                    }
                }

                $formattedOrders[] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table_number,
                    'waiter_name' => $order->waiter_name,
                    'ordered_at' => $order->ordered_at,
                    'special_instructions' => $order->special_instructions,
                    'priority_level' => $order->priority_level,
                    'kitchen_status' => $order->kitchen_status,
                    'order_status' => $order->order_status,
                    'order_type' => $order->order_type,
                    'items' => $items
                ];
            }

            return $formattedOrders;
        } catch (\Exception $e) {
            log_message('error', 'getPendingOrdersForDisplay error: ' . $e->getMessage());
            return [];
        }
    }
}
