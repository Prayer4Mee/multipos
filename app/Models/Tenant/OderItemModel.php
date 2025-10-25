<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;

/**
 * Order Item Model
 * Manages individual items within orders
 */
class OrderItemModel extends BaseModel
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'order_id', 'menu_item_id', 'item_name', 'unit_price', 'quantity',
        'total_price', 'modifiers', 'special_instructions', 'kitchen_status',
        'preparation_time', 'started_at', 'completed_at'
    ];
    
    protected $validationRules = [
        'order_id' => 'required|integer',
        'menu_item_id' => 'required|integer',
        'quantity' => 'required|integer|greater_than[0]',
        'unit_price' => 'required|decimal',
        'total_price' => 'required|decimal'
    ];
    
    /**
     * Get items for an order
     */
    public function getOrderItems($orderId)
    {
        return $this->select('order_items.*, menu_items.name as menu_item_name, 
                             menu_items.preparation_time as default_prep_time')
                    ->join('menu_items', 'menu_items.id = order_items.menu_item_id', 'left')
                    ->where('order_id', $orderId)
                    ->findAll();
    }
    
    /**
     * Get items by kitchen status
     */
    public function getItemsByKitchenStatus($status)
    {
        return $this->select('order_items.*, orders.order_number, orders.table_id, 
                             restaurant_tables.table_number')
                    ->join('orders', 'orders.id = order_items.order_id')
                    ->join('restaurant_tables', 'restaurant_tables.id = orders.table_id', 'left')
                    ->where('order_items.kitchen_status', $status)
                    ->where('orders.status !=', 'cancelled')
                    ->orderBy('order_items.created_at')
                    ->findAll();
    }
    
    /**
     * Update kitchen status for item
     */
    public function updateKitchenStatus($itemId, $status)
    {
        $updateData = ['kitchen_status' => $status];
        
        switch ($status) {
            case 'preparing':
                $updateData['started_at'] = date('Y-m-d H:i:s');
                break;
            case 'ready':
                $updateData['completed_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        return $this->update($itemId, $updateData);
    }
    
    /**
     * Get preparation time statistics
     */
    public function getPreparationStats($startDate, $endDate)
    {
        return $this->select('menu_items.name, 
                             AVG(TIMESTAMPDIFF(MINUTE, order_items.started_at, order_items.completed_at)) as avg_prep_time,
                             COUNT(*) as items_prepared')
                    ->join('menu_items', 'menu_items.id = order_items.menu_item_id')
                    ->join('orders', 'orders.id = order_items.order_id')
                    ->where('order_items.started_at IS NOT NULL')
                    ->where('order_items.completed_at IS NOT NULL')
                    ->where('DATE(orders.ordered_at) >=', $startDate)
                    ->where('DATE(orders.ordered_at) <=', $endDate)
                    ->groupBy('order_items.menu_item_id')
                    ->findAll();
    }
}