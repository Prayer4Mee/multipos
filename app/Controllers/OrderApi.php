<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class OrderApi extends Controller
{
    protected $db;
    protected $tenantId;
    
    public function test()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'OrderApi test method working',
            'tenant' => $this->tenantId
        ]);
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Extract tenant from URL
        $uri = $request->getUri();
        $segments = $uri->getSegments();
        
        if (count($segments) >= 2 && $segments[0] === 'restaurant') {
            $this->tenantId = $segments[1];
        }
        
        // Connect to tenant database using the same method as BaseRestaurantController
        if ($this->tenantId) {
            $this->setupTenantDatabase();
        }
    }
    
    private function setupTenantDatabase()
    {
        // Use the same database setup as BaseRestaurantController
        $config = [
            'DSN'      => '',
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'multipos_' . $this->tenantId,
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => false,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => 3306,
        ];
        
        $this->db = \Config\Database::connect($config);
    }
    
    public function orderDetails($orderId = null)
    {
        // URL에서 직접 orderId 추출
        if (!$orderId || $orderId === 'jollibee') {
            $uri = $this->request->getUri();
            $segments = $uri->getSegments();
            
            // URL: /restaurant/jollibee/order-details/4
            // segments: ['restaurant', 'jollibee', 'order-details', '4']
            if (count($segments) >= 4 && $segments[2] === 'order-details') {
                $orderId = $segments[3];
            }
        }
        
        // orderId가 여전히 tenant ID인 경우 처리
        if ($orderId === $this->tenantId) {
            $uri = $this->request->getUri();
            $segments = $uri->getSegments();
            if (count($segments) >= 4) {
                $orderId = $segments[3];
            }
        }
        
        try {
            // 임시 하드코딩된 데이터 (실제 DB 연결 문제 해결 전까지)
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
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Order not found',
                    'debug' => [
                        'orderId' => $orderId,
                        'tenantId' => $this->tenantId
                    ]
                ])->setStatusCode(404);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'order' => $orders[$orderId]
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function updateOrderStatus()
    {
        // 간단한 테스트 응답
        return $this->response->setJSON([
            'success' => true,
            'message' => 'updateOrderStatus method called',
            'method' => $this->request->getMethod(),
            'postData' => $this->request->getPost()
        ]);
        
        try {
            $orderId = $this->request->getPost('order_id');
            $newStatus = $this->request->getPost('status');

            // 디버깅 정보
            $debugInfo = [
                'orderId' => $orderId,
                'newStatus' => $newStatus,
                'method' => $this->request->getMethod(),
                'contentType' => $this->request->getHeaderLine('Content-Type')
            ];

            if (!$orderId || !$newStatus) {
                return $this->response->setJSON([
                    'error' => 'Missing required parameters',
                    'debug' => $debugInfo
                ])->setStatusCode(400);
            }

            // Validate status
            $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                return $this->response->setJSON(['error' => 'Invalid status'])->setStatusCode(400);
            }

            // 임시로 성공 응답 반환 (실제 DB 업데이트는 나중에 구현)
            $timestamp = date('Y-m-d H:i:s');
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order_id' => $orderId,
                'new_status' => $newStatus,
                'updated_at' => $timestamp
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }
}