<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;

/**
 * Menu Item Model
 * Manages restaurant menu items
 */
class MenuItemModel extends BaseModel
{
    protected $table = 'menu_items';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'category_id', 'sku', 'name', 'description', 'price', 'cost_price',
        'vat_type', 'image_url', 'preparation_time', 'calories', 'track_inventory',
        'current_stock', 'reorder_level', 'is_available', 'is_featured',
        'availability_schedule', 'has_modifiers', 'modifier_groups', 'display_order'
    ];
    
    protected $validationRules = [
        'category_id' => 'required|integer',
        'name' => 'required|min_length[3]|max_length[100]',
        'price' => 'required|decimal',
        'vat_type' => 'in_list[vatable,non_vatable,zero_rated]'
    ];
    
    /**
     * Get available menu items with category info
     */
    public function getAvailableItems()
    {
        return $this->select('menu_items.*, menu_categories.name as category_name, menu_categories.color_code')
                    ->join('menu_categories', 'menu_categories.id = menu_items.category_id')
                    ->where('menu_items.is_available', true)
                    ->where('menu_categories.is_active', true)
                    ->orderBy('menu_categories.display_order, menu_items.display_order')
                    ->findAll();
    }
    
    /**
     * Get items by category
     */
    public function getItemsByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)
                    ->where('is_available', true)
                    ->orderBy('display_order')
                    ->findAll();
    }
    
    /**
     * Get featured items
     */
    public function getFeaturedItems($limit = 6)
    {
        return $this->where('is_featured', true)
                    ->where('is_available', true)
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Get low stock items
     */
    public function getLowStockItems()
    {
        return $this->where('track_inventory', true)
                    ->where('current_stock <= reorder_level')
                    ->where('is_available', true)
                    ->findAll();
    }
    
    /**
     * Update stock quantity
     */
    public function updateStock($itemId, $quantity, $operation = 'subtract')
    {
        $item = $this->find($itemId);
        
        if (!$item || !$item['track_inventory']) {
            return false;
        }
        
        $newStock = $operation === 'add' 
            ? $item['current_stock'] + $quantity 
            : $item['current_stock'] - $quantity;
            
        $newStock = max(0, $newStock); // Prevent negative stock
        
        return $this->update($itemId, ['current_stock' => $newStock]);
    }
    
    /**
     * Search menu items
     */
    public function searchItems($keyword)
    {
        return $this->groupStart()
                        ->like('name', $keyword)
                        ->orLike('description', $keyword)
                    ->groupEnd()
                    ->where('is_available', true)
                    ->findAll();
    }
}