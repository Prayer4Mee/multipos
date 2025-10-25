<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;

/**
 * Payment Model
 * Manages payment transactions
 */
class PaymentModel extends BaseModel
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'order_id', 'payment_method', 'amount_paid', 'cash_received',
        'change_given', 'reference_number', 'authorization_code',
        'gateway_response', 'processed_by_user_id', 'payment_status',
        'receipt_number', 'or_number', 'processed_at'
    ];
    
    protected $validationRules = [
        'order_id' => 'required|integer',
        'payment_method' => 'required|in_list[cash,card,gcash,maya,bank_transfer]',
        'amount_paid' => 'required|decimal|greater_than[0]',
        'processed_by_user_id' => 'required|integer',
        'payment_status' => 'in_list[pending,completed,failed,cancelled,refunded]'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'processed_at';
    
    /**
     * Get payments for an order
     */
    public function getOrderPayments($orderId)
    {
        return $this->select('payments.*, users.first_name as processed_by_name')
                    ->join('users', 'users.id = payments.processed_by_user_id', 'left')
                    ->where('order_id', $orderId)
                    ->orderBy('processed_at', 'DESC')
                    ->findAll();
    }
    
    /**
     * Get daily payment summary
     */
    public function getDailyPaymentSummary($date)
    {
        return $this->select('payment_method, COUNT(*) as transaction_count, 
                             SUM(amount_paid) as total_amount')
                    ->where('DATE(processed_at)', $date)
                    ->where('payment_status', 'completed')
                    ->groupBy('payment_method')
                    ->findAll();
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats($startDate, $endDate)
    {
        return $this->select('DATE(processed_at) as date, payment_method,
                             COUNT(*) as count, SUM(amount_paid) as total')
                    ->where('DATE(processed_at) >=', $startDate)
                    ->where('DATE(processed_at) <=', $endDate)
                    ->where('payment_status', 'completed')
                    ->groupBy('DATE(processed_at), payment_method')
                    ->orderBy('date, payment_method')
                    ->findAll();
    }
    
    /**
     * Process refund
     */
    public function processRefund($paymentId, $refundAmount, $reason = null)
    {
        $payment = $this->find($paymentId);
        
        if (!$payment || $payment['payment_status'] !== 'completed') {
            return false;
        }
        
        // Create refund record
        $refundData = [
            'order_id' => $payment['order_id'],
            'payment_method' => $payment['payment_method'],
            'amount_paid' => -abs($refundAmount), // Negative amount for refund
            'reference_number' => 'REFUND_' . $payment['reference_number'],
            'processed_by_user_id' => session('user_id'),
            'payment_status' => 'refunded',
            'gateway_response' => json_encode(['refund_reason' => $reason])
        ];
        
        return $this->insert($refundData);
    }
}