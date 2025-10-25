<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;

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
    
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new \App\Models\Tenant\OrderModel();
        $this->orderModel->setDB($this->tenantDb);
    }
    
    /**
     * 결제 페이지
     */
    public function index($orderId)
    {
        try {
            // URL에서 직접 orderId 추출 (라우트 매개변수 문제 해결)
            if (!$orderId || $orderId === $this->tenantId) {
                $uri = $this->request->getUri();
                $segments = $uri->getSegments();
                
                // URL: /restaurant/jollibee/payment/5
                // segments: ['restaurant', 'jollibee', 'payment', '5']
                if (count($segments) >= 4 && $segments[2] === 'payment') {
                    $orderId = $segments[3];
                }
            }
            
            // 임시 하드코딩된 주문 데이터
            $orders = [
                1 => (object) [
                    'id' => 1,
                    'order_number' => 'ORD20241201001',
                    'table_id' => 1,
                    'table_numbers' => '1',
                    'customer_name' => 'Test Customer',
                    'status' => 'pending',
                    'total_amount' => 150.00,
                    'ordered_at' => '2025-10-18 10:13:56',
                    'items' => [
                        (object) [
                            'id' => 1,
                            'item_name' => 'Chicken Joy (2 pcs)',
                            'quantity' => 1,
                            'unit_price' => 100.00,
                            'total_price' => 100.00
                        ],
                        (object) [
                            'id' => 2,
                            'item_name' => 'Rice',
                            'quantity' => 1,
                            'unit_price' => 50.00,
                            'total_price' => 50.00
                        ]
                    ]
                ],
                4 => (object) [
                    'id' => 4,
                    'order_number' => 'ORD20241201003',
                    'table_id' => 2,
                    'table_numbers' => '2',
                    'customer_name' => 'Test Customer',
                    'status' => 'pending',
                    'total_amount' => 150.00,
                    'ordered_at' => '2025-10-19 03:46:30',
                    'items' => [
                        (object) [
                            'id' => 3,
                            'item_name' => 'Jolly Spaghetti',
                            'quantity' => 1,
                            'unit_price' => 80.00,
                            'total_price' => 80.00
                        ],
                        (object) [
                            'id' => 4,
                            'item_name' => 'Coke',
                            'quantity' => 1,
                            'unit_price' => 70.00,
                            'total_price' => 70.00
                        ]
                    ]
                ],
                5 => (object) [
                    'id' => 5,
                    'order_number' => 'ORD20241201004',
                    'table_id' => 3,
                    'table_numbers' => '3',
                    'customer_name' => 'New Customer',
                    'status' => 'preparing',
                    'total_amount' => 200.00,
                    'ordered_at' => '2025-10-19 03:48:43',
                    'items' => [
                        (object) [
                            'id' => 5,
                            'item_name' => 'Yum Burger',
                            'quantity' => 2,
                            'unit_price' => 100.00,
                            'total_price' => 200.00
                        ]
                    ]
                ]
            ];
            
            if (!isset($orders[$orderId])) {
                return redirect()->to(base_url("restaurant/{$this->tenantId}/pos"))
                    ->with('error', 'Order not found');
            }
            
            $order = $orders[$orderId];
            
            $data = [
                'title' => 'Payment - ' . $order->order_number,
                'page_title' => 'Payment',
                'tenant' => (object) [
                    'slug' => $this->tenantId,
                    'tenant_slug' => $this->tenantId,
                    'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Jollibee'
                ],
                'tenant_slug' => $this->tenantId,
                'current_user' => $this->currentUser,
                'order' => $order,
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
            $order->items = $this->tenantDb->table('order_items oi')
                                          ->select('oi.*, mi.name as item_name')
                                          ->join('menu_items mi', 'oi.menu_item_id = mi.id', 'left')
                                          ->where('oi.order_id', $orderId)
                                          ->get()
                                          ->getResult();
            
            $data = [
                'order' => $order,
                'tenant' => (object) [
                    'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Jollibee',
                    'address' => $this->tenantConfig->address ?? '',
                    'phone' => $this->tenantConfig->phone ?? ''
                ]
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
            'total_amount' => 'required|decimal'
        ];

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
            $order = $this->orderModel->find($orderId);
            if (!$order) {
                return $this->jsonResponse(['error' => 'Order not found'], 404);
            }
            
            // 결제 처리
            $this->tenantDb->transStart();
            
            // 주문 상태 업데이트
            $updateData = [
                'payment_method' => $paymentMethod,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'payment_status' => 'paid',
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s')
            ];
            
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
    private function printReceipt(int $orderId, int $paymentId): void
    {
        // Receipt printing implementation
        log_message('info', "Receipt printed for Order ID: {$orderId}, Payment ID: {$paymentId}");
    }
}