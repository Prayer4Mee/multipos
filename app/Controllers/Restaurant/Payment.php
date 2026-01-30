<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
class Payment extends BaseRestaurantController
{
    protected $orderModel;
    
    public function test($orderId)
    {
        // URL에서 직접 orderId 추출
        if (!$orderId || $orderId === $this->tenantId) {
            $uri = $this->request->getUri();
            $segments = $uri->getSegments();
            
            if (count($segments) >= 4 && $segments[2] === 'payment') {
                $orderId = $segments[3];
            }
        }
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Payment - Order #' . $orderId . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1><i class="fas fa-credit-card"></i> Payment Page</h1>
                <p>Order ID: ' . $orderId . '</p>
                <p>This is a working payment page.</p>
                <a href="/restaurant/jollibee/pos" class="btn btn-primary">Back to POS</a>
            </div>
        </div>
    </div>
</body>
</html>';
        
        return $this->response->setBody($html);
    }
    
    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->orderModel = new \App\Models\Tenant\OrderModel();
    //     $this->orderModel->setDB($this->tenantDb);
    // }

    // Remove the constructor entirely, or use this:
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Now you can use $this->tenantDb
        $this->orderModel = new \App\Models\Tenant\OrderModel();
        $this->orderModel->setDB($this->tenantDb);
    }
    
    /**
     * 결제 페이지
     */
    public function index($orderId)
    {
        try {
            // // URL에서 직접 orderId 추출 (라우트 매개변수 문제 해결)
            // if (!$orderId || $orderId === $this->tenantId) {
            //     $uri = $this->request->getUri();
            //     $segments = $uri->getSegments();
                
            //     // URL: /restaurant/jollibee/payment/5
            //     // segments: ['restaurant', 'jollibee', 'payment', '5']
            //     if (count($segments) >= 4 && $segments[2] === 'payment') {
            //         $orderId = $segments[3];
            //     }
            // }
            $order = $this->tenantDb->table('orders as o')
                                    ->select('o.*, o.vat_amount,rt.table_number')
                                    ->join('restaurant_tables as rt', 'o.table_id = rt.id', 'left')
                                    ->where('o.id', $orderId)
                                    ->get()
                                    ->getRow();
                                    
                if (!$order) {
                throw new \Exception('Order not found');
                }
                
                                     
            // FIX: Ensure these properties exist so the view doesn't crash
            $order->vat_amount = $order->vat_amount ?? 0;
            $order->total_amount = $order->total_amount ?? 0;
            $order->order_number = $order->order_number ?? 'N/A';
            // Get Order Items
            $order->items = $this->tenantDb->table('order_items')
                                    ->where('order_id', $orderId)
                                    ->get()
                                    ->getResult();
            $order->items = $order->items ?? [];
            $data = [
                'title' => 'Payment - ' . $order->order_number,
                'page_title' => 'Payment',
                'tenant' => (object) [
                    'slug' => $this->tenantId,
                    'tenant_slug' => $this->tenantId,
                    'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Jollibee',
                    'theme_color' => $this->tenantConfig->theme_color,  // ← ADD THIS
                ],
                'tenant_slug' => $this->tenantId,
                'current_user' => $this->currentUser,
                'order' => $order,
                'items' => $order->items, //Added this
                'base_url' => base_url()

                
            ];
            
            return view('restaurant/payment', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Payment index error: ' . $e->getMessage());
            return redirect()->to(base_url("restaurant/{$this->tenantId}/pos"))
                ->with('error', 'Failed to load payment page');
        }
    }
    
    /**
     * 영수증 인쇄
     */
    public function printReceipt($orderId)
    {
        try {
            $order = $this->tenantDb->table('orders')
                                   ->where('id', $orderId)
                                   ->get()
                                   ->getRow();
            
            if (!$order) {
                return redirect()->to(base_url("restaurant/{$this->tenantId}/pos"))
                    ->with('error', 'Order not found');
            }            
            // 주문 아이템 가져오기
            // No join needed - item_name is already in order_items
            $order->items = $this->tenantDb->table('order_items')
                            ->where('order_id', $orderId)
                            ->get()
                            ->getResult();
            // Set defaults
            // $order->items = $order->items ?? [];
            // $order->vat_amount = $order->vat_amount ?? 0;
            // $order->service_charge = $order->service_charge ?? 0;
            // $order->cashier_name = $order->cashier_name ?? 'System';

            $data = [
                'order' => $order,
                'tenant' => (object) [
                    'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Restaurant',
                    'address' => $this->tenantConfig->address ?? '',
                    'phone' => $this->tenantConfig->phone ?? '',
                    // 'theme_color' => $this->tenantConfig->theme_color ?? '#667eea'
                ],
                'tenant_slug' => $this->tenantId
            ];
            
            return view('restaurant/receipt', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Payment printReceipt error: ' . $e->getMessage());
            return redirect()->to(base_url("restaurant/{$this->tenantId}/pos"))
                ->with('error', 'Failed to print receipt');
        }
    }
    
    /**
     * Process payment
     */
    public function processPayment()
    {
        $this->requireRole(['manager', 'cashier']);

        $rules = [
            'order_id' => 'required|integer',
            'payment_method' => 'required|in_list[cash,card,gcash,maya,mixed]',
            'amount_received' => 'required|decimal',
            'total_amount' => 'required|decimal',
            // Added this 
            'discount_type' => 'permit_empty|string',
            'discount_amount' => 'permit_empty|decimal'
        ];
        $discountType   = $this->request->getPost('discount_type');
        $discountAmount = $this->request->getPost('discount_amount') ?? 0;

        if (!$this->validate($rules)) {
            return $this->jsonResponse(['error' => $this->validator->getErrors()], 400);
        }

        $orderId = $this->request->getPost('order_id');
        $paymentMethod = $this->request->getPost('payment_method');
        $amountReceived = $this->request->getPost('amount_received');
        $totalAmount = $this->request->getPost('total_amount');
        $changeAmount = $amountReceived - $totalAmount;

        try {
            // 주문 존재 확인
            // ============================================================================
            // FIX: Use tenantDb directly to get OBJECT, not array
            // OLD: $order = $this->orderModel->find($orderId); Returns array
            // NEW: $this->tenantDb->table(...)->getRow(); Returns object
            // ============================================================================
            $order = $this->tenantDb->table('orders')
                                    ->where('id', $orderId)
                                    ->get()
                                    ->getRow();  // ← This returns an object, not array!

            if (!$order) {
                return $this->jsonResponse(['error' => 'Order not found'], 404);
            }
            // Starts transaction for payment processing
            // 결제 처리
            $this->tenantDb->transStart();
            // Prepare update data
            // 주문 상태 업데이트
            $updateData = [
                'payment_method' => $paymentMethod,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'payment_status' => 'paid',
                // 'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'discount_type'   => $discountType,
                'discount_amount' => $discountAmount
                
                
            ];
            // Only mark as "Completed" IF AND ONLY IF it is already "Served"
            if ($order && $order->status === 'served') {
                $updateData['status'] = 'completed';
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            }
            // Otherwise, keep the current status (ready/pending/etc)
            // Status will become 'completed' when waiter marks it as 'served'
            
            $this->orderModel->update($orderId, $updateData);
            
            $this->tenantDb->transComplete();
            
            if ($this->tenantDb->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Payment processed successfully',
                'change_amount' => $changeAmount,
                'order_id' => $orderId
            ]);
            
        } catch (\Exception $e) {
            $this->tenantDb->transRollback();
            log_message('error', 'Payment processPayment error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Process cash payment
     */
    private function processCashPayment(array $paymentData, object $order): array
    {
        $paymentData['status'] = 'completed';
        $paymentData['completed_at'] = date('Y-m-d H:i:s');

        $paymentId = $this->tenantDb->table('payments')->insert($paymentData, true);

        return [
            'success' => true,
            'payment_id' => $paymentId
        ];
    }

    /**
     * Process card payment
     */
    private function processCardPayment(array $paymentData, object $order): array
    {
        // Integration with card payment terminal would go here
        // For now, simulate successful payment
        
        $paymentData['status'] = 'completed';
        $paymentData['completed_at'] = date('Y-m-d H:i:s');
        $paymentData['authorization_code'] = 'AUTH' . time();
        $paymentData['card_last_digits'] = $this->request->getPost('card_last_digits');

        $paymentId = $this->tenantDb->table('payments')->insert($paymentData, true);

        return [
            'success' => true,
            'payment_id' => $paymentId
        ];
    }

    /**
     * Process GCash payment
     */
    private function processGCashPayment(array $paymentData, object $order): array
    {
        // GCash API integration
        $gcashService = new \App\Services\GCashService($this->tenantId);
        
        $gcashData = [
            'amount' => $paymentData['amount'],
            'merchant_ref' => $order->order_number,
            'description' => "Payment for Order #{$order->order_number}",
            'vat_amount' => $order->vat_amount
        ];

        $response = $gcashService->createPayment($gcashData);

        if ($response['success']) {
            $paymentData['status'] = 'completed';
            $paymentData['completed_at'] = date('Y-m-d H:i:s');
            $paymentData['provider_transaction_id'] = $response['transaction_id'];
            $paymentData['reference_number'] = $response['reference_number'];
            $paymentData['provider_fee'] = $response['fee'] ?? 0;

            $paymentId = $this->tenantDb->table('payments')->insert($paymentData, true);

            return [
                'success' => true,
                'payment_id' => $paymentId
            ];
        } else {
            return [
                'success' => false,
            ];
        }
    }

    /**
     * Process Maya payment
     */
    private function processMayaPayment(array $paymentData, object $order): array
    {
        // Maya API integration
        $mayaService = new \App\Services\MayaService($this->tenantId);
        
        $mayaData = [
            'amount' => $paymentData['amount'],
            'merchant_ref' => $order->order_number,
            'description' => "Payment for Order #{$order->order_number}"
        ];

        $response = $mayaService->createPayment($mayaData);

        if ($response['success']) {
            $paymentData['status'] = 'completed';
            $paymentData['completed_at'] = date('Y-m-d H:i:s');
            $paymentData['provider_transaction_id'] = $response['transaction_id'];
            $paymentData['reference_number'] = $response['reference_number'];

            $paymentId = $this->tenantDb->table('payments')->insert($paymentData, true);

            return [
                'success' => true,
                'payment_id' => $paymentId
            ];
        } else {
            return [
                'success' => false,
            ];
        }
    }

    /**
     * Generate BIR receipt number
     */
    private function generateReceiptNumber(): string
    {
        $prefix = $this->tenantConfig->receipt_prefix ?? 'OR';
        $date = date('Ymd');
        
        $lastReceipt = $this->tenantDb->table('payments')
                                     ->where('official_receipt_number LIKE', "{$prefix}{$date}%")
                                     ->orderBy('official_receipt_number', 'DESC')
                                     ->limit(1)
                                     ->get()
                                     ->getRow();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->official_receipt_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return $prefix . $date . $newNumber;
    }

    /**
     * Print receipt
     */
    // private function printReceipt(int $orderId, int $paymentId): void
    // {
    //     // Receipt printing implementation
    //     log_message('info', "Receipt printed for Order ID: {$orderId}, Payment ID: {$paymentId}");
    // }
}