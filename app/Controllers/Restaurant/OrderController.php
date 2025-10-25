<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;
use App\Models\Tenant\OrderModel;

/**
 * 통합 주문 관리 컨트롤러
 * 모든 페이지에서 동일한 주문 데이터를 제공
 */
class OrderController extends BaseRestaurantController
{
    protected $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
        $this->orderModel->setDB($this->tenantDb);
    }
    
    /**
     * 주문 목록 조회 (모든 페이지에서 사용)
     */
    public function getOrders()
    {
        try {
            $filters = [
                'status' => $this->request->getGet('status'),
                'order_type' => $this->request->getGet('order_type'),
                'date_from' => $this->request->getGet('date_from'),
                'date_to' => $this->request->getGet('date_to'),
                'table_id' => $this->request->getGet('table_id'),
                'waiter_id' => $this->request->getGet('waiter_id')
            ];
            
            // 빈 값 제거
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            $orders = $this->orderModel->getOrdersWithDetails($filters);
            
            return $this->jsonResponse([
                'success' => true,
                'orders' => $orders,
                'total' => count($orders)
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController getOrders error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문 상세 조회
     */
    public function getOrderDetails($orderId)
    {
        try {
            $order = $this->orderModel->getOrderWithItems($orderId);
            
            if (!$order) {
                return $this->jsonResponse(['error' => 'Order not found'], 404);
            }
            
            return $this->jsonResponse([
                'success' => true,
                'order' => $order
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController getOrderDetails error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문 생성
     */
    public function createOrder()
    {
        try {
            $rules = [
                'order_type' => 'required|in_list[dine_in,takeout,delivery,drive_through]',
                'customer_name' => 'permit_empty|max_length[100]',
                'table_ids' => 'permit_empty',
                'waiter_id' => 'permit_empty|integer',
                'items' => 'required'
            ];
            
            if (!$this->validate($rules)) {
                return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
            }
            
            $this->tenantDb->transStart();
            
            // 주문 번호 생성
            $orderNumber = $this->orderModel->generateOrderNumber();
            
            // 주문 기본 정보
            $orderData = [
                'order_number' => $orderNumber,
                'order_type' => $this->request->getPost('order_type'),
                'order_source' => 'pos',
                'customer_name' => $this->request->getPost('customer_name'),
                'customer_phone' => $this->request->getPost('customer_phone'),
                'customer_email' => $this->request->getPost('customer_email'),
                'waiter_id' => $this->request->getPost('waiter_id'),
                'cashier_id' => $this->currentUser->id,
                'special_instructions' => $this->request->getPost('special_instructions'),
                'status' => 'created',
                'priority_level' => 'normal',
                'payment_status' => 'pending',
                'payment_method' => 'cash'
            ];
            
            $orderId = $this->orderModel->insert($orderData);
            
            // 테이블 배정 (다중 테이블 지원)
            $tableIds = $this->request->getPost('table_ids');
            if ($tableIds) {
                if (is_string($tableIds)) {
                    $tableIds = json_decode($tableIds, true);
                }
                foreach ($tableIds as $tableId) {
                    $this->orderModel->addTableToOrder($orderId, $tableId);
                }
            }
            
            // 주문 아이템 추가
            $items = $this->request->getPost('items');
            if (is_string($items)) {
                $items = json_decode($items, true);
            }
            
            $subtotal = 0;
            foreach ($items as $item) {
                $itemTotal = $item['quantity'] * $item['price'];
                $subtotal += $itemTotal;
                
                $this->orderModel->addItemToOrder(
                    $orderId,
                    $item['id'],
                    $item['quantity'],
                    $item['price'],
                    $item['special_instructions'] ?? null
                );
            }
            
            // 주문 총액 계산
            $totalAmount = $subtotal; // 서비스 차지, 할인, VAT는 나중에 추가
            $this->orderModel->update($orderId, [
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount
            ]);
            
            $this->tenantDb->transComplete();
            
            if ($this->tenantDb->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }
            
            return $this->jsonResponse([
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'message' => 'Order created successfully'
            ]);
            
        } catch (\Exception $e) {
            $this->tenantDb->transRollback();
            log_message('error', 'OrderController createOrder error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문 상태 업데이트
     */
    public function updateOrderStatus()
    {
        try {
            $orderId = $this->request->getPost('order_id');
            $status = $this->request->getPost('status');
            
            if (!$orderId || !$status) {
                return $this->jsonResponse(['error' => 'Missing order_id or status'], 400);
            }
            
            $result = $this->orderModel->updateOrderStatus($orderId, $status, $this->currentUser->id);
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Order status updated successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update order status'], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController updateOrderStatus error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문 아이템 추가
     */
    public function addOrderItem()
    {
        try {
            $rules = [
                'order_id' => 'required|integer',
                'menu_item_id' => 'required|integer',
                'quantity' => 'required|integer|greater_than[0]',
                'unit_price' => 'required|decimal'
            ];
            
            if (!$this->validate($rules)) {
                return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
            }
            
            $orderId = $this->request->getPost('order_id');
            $menuItemId = $this->request->getPost('menu_item_id');
            $quantity = $this->request->getPost('quantity');
            $unitPrice = $this->request->getPost('unit_price');
            $specialInstructions = $this->request->getPost('special_instructions');
            
            $result = $this->orderModel->addItemToOrder(
                $orderId,
                $menuItemId,
                $quantity,
                $unitPrice,
                $specialInstructions
            );
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Item added to order successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to add item to order'], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController addOrderItem error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문 아이템 수량 변경
     */
    public function updateOrderItemQuantity()
    {
        try {
            $rules = [
                'item_id' => 'required|integer',
                'quantity' => 'required|integer|greater_than[0]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
            }
            
            $itemId = $this->request->getPost('item_id');
            $quantity = $this->request->getPost('quantity');
            
            $result = $this->orderModel->updateItemQuantity($itemId, $quantity);
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Item quantity updated successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update item quantity'], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController updateOrderItemQuantity error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문 아이템 삭제
     */
    public function removeOrderItem()
    {
        try {
            $rules = [
                'item_id' => 'required|integer'
            ];
            
            if (!$this->validate($rules)) {
                return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
            }
            
            $itemId = $this->request->getPost('item_id');
            
            $result = $this->orderModel->removeItemFromOrder($itemId);
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Item removed from order successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to remove item from order'], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController removeOrderItem error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문에 테이블 추가
     */
    public function addTableToOrder()
    {
        try {
            $rules = [
                'order_id' => 'required|integer',
                'table_id' => 'required|integer'
            ];
            
            if (!$this->validate($rules)) {
                return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
            }
            
            $orderId = $this->request->getPost('order_id');
            $tableId = $this->request->getPost('table_id');
            
            $result = $this->orderModel->addTableToOrder($orderId, $tableId);
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Table added to order successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to add table to order'], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController addTableToOrder error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 주문에서 테이블 제거
     */
    public function removeTableFromOrder()
    {
        try {
            $rules = [
                'order_id' => 'required|integer',
                'table_id' => 'required|integer'
            ];
            
            if (!$this->validate($rules)) {
                return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
            }
            
            $orderId = $this->request->getPost('order_id');
            $tableId = $this->request->getPost('table_id');
            
            $result = $this->orderModel->removeTableFromOrder($orderId, $tableId);
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Table removed from order successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to remove table from order'], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'OrderController removeTableFromOrder error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
