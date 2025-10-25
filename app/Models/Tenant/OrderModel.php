<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;

/**
 * Order Model - 통합 주문 관리
 * 모든 페이지에서 동일한 주문 데이터를 제공
 */
class OrderModel extends BaseModel
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    
    
    protected $allowedFields = [
        'order_number', 'order_type', 'order_source', 'customer_name',
        'customer_phone', 'customer_email', 'waiter_id', 'cashier_id',
        'subtotal', 'service_charge', 'discount_amount', 'discount_type',
        'vat_amount', 'total_amount', 'status', 'priority_level',
        'payment_status', 'payment_method', 'special_instructions',
        'kitchen_notes', 'internal_notes', 'estimated_ready_at',
        'ready_at', 'served_at', 'completed_at', 'amount_received',
        'change_amount'
    ];
    
    protected $validationRules = [
        'order_number' => 'required|max_length[20]',
        'order_type' => 'required|in_list[dine_in,takeout,delivery,drive_through]',
        'order_source' => 'required|in_list[pos,qr_self_order,kiosk,phone,online]',
        'total_amount' => 'required|decimal',
        'status' => 'in_list[created,pending,confirmed,preparing,ready,served,paid,completed,cancelled]',
        'payment_method' => 'in_list[cash,card,gcash,maya,mixed]'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'ordered_at';
    protected $updatedField = 'updated_at';
    
    /**
     * 주문 프로세스 상태 정의
     */
    const ORDER_STATUSES = [
        'created' => '생성됨',
        'pending' => '대기중',
        'confirmed' => '확인됨',
        'preparing' => '요리중',
        'ready' => '출하준비',
        'served' => '서빙완료',
        'paid' => '정산완료',
        'completed' => '완료',
        'cancelled' => '취소됨'
    ];
    
    /**
     * 통합 주문 데이터 조회 (모든 페이지에서 사용)
     */
    public function getOrdersWithDetails($filters = [])
    {
        $builder = $this->select('
            o.*,
            GROUP_CONCAT(DISTINCT rt.table_number ORDER BY rt.table_number) as table_numbers,
            GROUP_CONCAT(DISTINCT CONCAT(s.first_name, " ", s.last_name) ORDER BY s.first_name) as waiter_names,
            COUNT(DISTINCT oi.id) as item_count,
            COUNT(DISTINCT ot.table_id) as table_count
        ')
        ->from('orders o')
        ->join('order_items oi', 'o.id = oi.order_id', 'left')
        ->join('order_tables ot', 'o.id = ot.order_id', 'left')
        ->join('restaurant_tables rt', 'ot.table_id = rt.id', 'left')
        ->join('staff s', 'o.waiter_id = s.id', 'left')
        ->groupBy('o.id');
        
        // 필터 적용
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $builder->whereIn('o.status', $filters['status']);
            } else {
                $builder->where('o.status', $filters['status']);
            }
        }
        
        if (!empty($filters['order_type'])) {
            $builder->where('o.order_type', $filters['order_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('o.ordered_at >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('o.ordered_at <=', $filters['date_to']);
        }
        
        if (!empty($filters['table_id'])) {
            $builder->where('ot.table_id', $filters['table_id']);
        }
        
        if (!empty($filters['waiter_id'])) {
            $builder->where('o.waiter_id', $filters['waiter_id']);
        }
        
        return $builder->orderBy('o.ordered_at', 'DESC')->get()->getResult();
    }
    
    /**
     * 주문 상세 정보 조회 (아이템 포함)
     */
    public function getOrderWithItems($orderId)
    {
        // 주문 기본 정보
        $order = $this->select('
            o.*,
            GROUP_CONCAT(DISTINCT rt.table_number ORDER BY rt.table_number) as table_numbers,
            CONCAT(s.first_name, " ", s.last_name) as waiter_name,
            CONCAT(c.first_name, " ", c.last_name) as cashier_name
        ')
        ->from('orders o')
        ->join('order_tables ot', 'o.id = ot.order_id', 'left')
        ->join('restaurant_tables rt', 'ot.table_id = rt.id', 'left')
        ->join('staff s', 'o.waiter_id = s.id', 'left')
        ->join('staff c', 'o.cashier_id = c.id', 'left')
        ->where('o.id', $orderId)
        ->groupBy('o.id')
        ->get()
        ->getRow();
        
        if (!$order) {
            return null;
        }
        
        // 주문 아이템 정보
        $orderItems = $this->db->table('order_items oi')
            ->select('oi.*, mi.name as menu_item_name, mi.description')
            ->join('menu_items mi', 'oi.menu_item_id = mi.id', 'left')
            ->where('oi.order_id', $orderId)
            ->get()
            ->getResult();
        
        $order->items = $orderItems;
        
        return $order;
    }
    
    /**
     * 주문 상태 업데이트 (프로세스 관리)
     */
    public function updateOrderStatus($orderId, $status, $userId = null)
    {
        $validStatuses = array_keys(self::ORDER_STATUSES);
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid order status');
        }
        
        $updateData = ['status' => $status];
        
        // 상태별 타임스탬프 설정
        switch ($status) {
            case 'confirmed':
                $updateData['confirmed_at'] = date('Y-m-d H:i:s');
                break;
            case 'preparing':
                $updateData['preparing_at'] = date('Y-m-d H:i:s');
                break;
            case 'ready':
                $updateData['ready_at'] = date('Y-m-d H:i:s');
                break;
            case 'served':
                $updateData['served_at'] = date('Y-m-d H:i:s');
                break;
            case 'paid':
                $updateData['paid_at'] = date('Y-m-d H:i:s');
                break;
            case 'completed':
                $updateData['completed_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        // 주문 상태 업데이트
        $result = $this->update($orderId, $updateData);
        
        // 주문 아이템 상태도 함께 업데이트 (요리 관련 상태인 경우)
        if (in_array($status, ['preparing', 'ready', 'served'])) {
            $this->db->table('order_items')
                ->where('order_id', $orderId)
                ->update(['kitchen_status' => $status]);
        }
        
        return $result;
    }
    
    /**
     * 주문에 테이블 추가
     */
    public function addTableToOrder($orderId, $tableId)
    {
        return $this->db->table('order_tables')->insert([
            'order_id' => $orderId,
            'table_id' => $tableId
        ]);
    }
    
    /**
     * 주문에서 테이블 제거
     */
    public function removeTableFromOrder($orderId, $tableId)
    {
        return $this->db->table('order_tables')
            ->where('order_id', $orderId)
            ->where('table_id', $tableId)
            ->delete();
    }
    
    /**
     * 주문에 아이템 추가
     */
    public function addItemToOrder($orderId, $menuItemId, $quantity, $unitPrice, $specialInstructions = null)
    {
        $itemData = [
            'order_id' => $orderId,
            'menu_item_id' => $menuItemId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'special_instructions' => $specialInstructions,
            'kitchen_status' => 'pending'
        ];
        
        // 메뉴 아이템 이름 가져오기
        $menuItem = $this->db->table('menu_items')
            ->select('name')
            ->where('id', $menuItemId)
            ->get()
            ->getRow();
        
        if ($menuItem) {
            $itemData['item_name'] = $menuItem->name;
        }
        
        return $this->db->table('order_items')->insert($itemData);
    }
    
    /**
     * 주문 아이템 수량 변경
     */
    public function updateItemQuantity($itemId, $quantity)
    {
        $item = $this->db->table('order_items')
            ->select('unit_price, order_id')
            ->where('id', $itemId)
            ->get()
            ->getRow();
        
        if (!$item) {
            return false;
        }
        
        $updateData = [
            'quantity' => $quantity,
            'total_price' => $quantity * $item->unit_price
        ];
        
        $result = $this->db->table('order_items')
            ->where('id', $itemId)
            ->update($updateData);
        
        // 주문 총액 재계산
        $this->recalculateOrderTotal($item->order_id);
        
        return $result;
    }
    
    /**
     * 주문 아이템 삭제
     */
    public function removeItemFromOrder($itemId)
    {
        $item = $this->db->table('order_items')
            ->select('order_id')
            ->where('id', $itemId)
            ->get()
            ->getRow();
        
        if (!$item) {
            return false;
        }
        
        $result = $this->db->table('order_items')
            ->where('id', $itemId)
            ->delete();
        
        // 주문 총액 재계산
        $this->recalculateOrderTotal($item->order_id);
        
        return $result;
    }
    
    /**
     * 주문 총액 재계산
     */
    public function recalculateOrderTotal($orderId)
    {
        $subtotal = $this->db->table('order_items')
            ->selectSum('total_price')
            ->where('order_id', $orderId)
            ->get()
            ->getRow()
            ->total_price ?? 0;
        
        $order = $this->find($orderId);
        if (!$order) {
            return false;
        }
        
        $serviceCharge = $order->service_charge ?? 0;
        $discountAmount = $order->discount_amount ?? 0;
        $vatAmount = $order->vat_amount ?? 0;
        
        $totalAmount = $subtotal + $serviceCharge - $discountAmount + $vatAmount;
        
        return $this->update($orderId, [
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount
        ]);
    }
    
    /**
     * 주문 번호 생성
     */
    public function generateOrderNumber($prefix = 'ORD')
    {
        $date = date('Ymd');
        $lastOrder = $this->select('order_number')
            ->where('DATE(ordered_at)', date('Y-m-d'))
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}