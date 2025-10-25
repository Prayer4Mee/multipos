<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;

/**
 * Daily Sales Model
 * Manages daily sales summaries for BIR compliance
 */
class DailySalesModel extends BaseModel
{
    protected $table = 'daily_sales';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'business_date', 'total_orders', 'gross_sales', 'total_discounts',
        'net_sales', 'service_charge', 'vatable_sales', 'vat_amount',
        'non_vatable_sales', 'zero_rated_sales', 'cash_sales', 'card_sales',
        'gcash_sales', 'maya_sales', 'z_reading_number', 'z_reading_generated_at',
        'z_reading_data', 'is_finalized', 'finalized_by_user_id', 'finalized_at'
    ];
    
    protected $validationRules = [
        'business_date' => 'required|valid_date',
        'gross_sales' => 'decimal',
        'z_reading_number' => 'max_length[20]'
    ];
    
    /**
     * Get or create daily sales record
     */
    public function getOrCreateDailySales($date)
    {
        $existing = $this->where('business_date', $date)->first();
        
        if ($existing) {
            return $existing;
        }
        
        // Create new record
        $data = [
            'business_date' => $date,
            'total_orders' => 0,
            'gross_sales' => 0.00,
            'total_discounts' => 0.00,
            'net_sales' => 0.00,
            'service_charge' => 0.00,
            'vatable_sales' => 0.00,
            'vat_amount' => 0.00,
            'non_vatable_sales' => 0.00,
            'zero_rated_sales' => 0.00,
            'cash_sales' => 0.00,
            'card_sales' => 0.00,
            'gcash_sales' => 0.00,
            'maya_sales' => 0.00
        ];
        
        $this->insert($data);
        return $this->where('business_date', $date)->first();
    }
    
    /**
     * Update daily sales with order data
     */
    public function updateWithOrder($order, $payments)
    {
        $date = date('Y-m-d', strtotime($order['ordered_at']));
        $dailySales = $this->getOrCreateDailySales($date);
        
        // Calculate updates
        $updates = [
            'total_orders' => $dailySales['total_orders'] + 1,
            'gross_sales' => $dailySales['gross_sales'] + $order['total_amount'],
            'total_discounts' => $dailySales['total_discounts'] + $order['discount_amount'],
            'net_sales' => $dailySales['net_sales'] + $order['subtotal'],
            'service_charge' => $dailySales['service_charge'] + $order['service_charge'],
            'vat_amount' => $dailySales['vat_amount'] + $order['vat_amount']
        ];
        
        // Update VAT breakdown (simplified - would need proper VAT calculation)
        $updates['vatable_sales'] = $dailySales['vatable_sales'] + $order['subtotal'];
        
        // Update payment method totals
        foreach ($payments as $payment) {
            $method = $payment['payment_method'];
            $field = $method . '_sales';
            
            if (array_key_exists($field, $updates)) {
                $updates[$field] = $dailySales[$field] + $payment['amount_paid'];
            }
        }
        
        return $this->update($dailySales['id'], $updates);
    }
    
    /**
     * Generate Z-Reading
     */
    public function generateZReading($date, $userId)
    {
        $dailySales = $this->where('business_date', $date)->first();
        
        if (!$dailySales) {
            throw new \Exception('No sales data found for date: ' . $date);
        }
        
        if ($dailySales['is_finalized']) {
            throw new \Exception('Daily sales already finalized for date: ' . $date);
        }
        
        // Generate Z-Reading number
        $zReadingNumber = $this->generateZReadingNumber($date);
        
        // Prepare Z-Reading data
        $zReadingData = [
            'business_date' => $date,
            'z_reading_number' => $zReadingNumber,
            'summary' => $dailySales,
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => $userId
        ];
        
        // Update record
        $updates = [
            'z_reading_number' => $zReadingNumber,
            'z_reading_generated_at' => date('Y-m-d H:i:s'),
            'z_reading_data' => json_encode($zReadingData),
            'is_finalized' => true,
            'finalized_by_user_id' => $userId,
            'finalized_at' => date('Y-m-d H:i:s')
        ];
        
        $this->update($dailySales['id'], $updates);
        
        return $zReadingData;
    }
    
    /**
     * Generate sequential Z-Reading number
     */
    private function generateZReadingNumber($date)
    {
        $year = date('Y', strtotime($date));
        $prefix = 'Z' . $year;
        
        $lastZReading = $this->like('z_reading_number', $prefix)
                             ->orderBy('z_reading_number', 'DESC')
                             ->first();
        
        if ($lastZReading) {
            $lastNumber = intval(substr($lastZReading['z_reading_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }
    
    /**
     * Get sales report data
     */
    public function getSalesReport($startDate, $endDate)
    {
        return $this->where('business_date >=', $startDate)
                    ->where('business_date <=', $endDate)
                    ->orderBy('business_date', 'DESC')
                    ->findAll();
    }
}

?>