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
            'password' => 'root',
            'database' => "restaurant_{$this->tenantId}_db",
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => ENVIRONMENT !== 'production',
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_unicode_ci',
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
        try {
            // If orderId is missing, try to extract from URI
            if (!$orderId) {
                $uri = $this->request->getUri();
                $segments = $uri->getSegments();
                if (count($segments) >= 4 && $segments[2] === 'order-details') {
                    $orderId = $segments[3];
                }
            }

            // Fetch the order from tenant database
            $order = $this->tenantDb->table('orders as o')
                ->select('o.*, rt.table_number')
                ->join('restaurant_tables as rt', 'o.table_id = rt.id', 'left')
                ->where('o.id', $orderId)
                ->where('o.tenant_id', $this->tenantId)
                ->get()
                ->getRow();

            if (!$order) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Order not found'
                ])->setStatusCode(404);
            }

            // Fetch order items with menu item names
            $order->items = $this->tenantDb->table('order_items as oi')
                ->select('oi.*, mi.name as menu_item_name')
                ->join('menu_items as mi', 'oi.menu_item_id = mi.id', 'left')
                ->where('oi.order_id', $orderId)
                ->get()
                ->getResult();

            return $this->response->setJSON([
                'success' => true,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to load order details: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function updateOrderStatus()
    {
        // 간단한 테스트 응답
        // return $this->response->setJSON([
        //     'success' => true,
        //     'message' => 'updateOrderStatus method called',
        //     'method' => $this->request->getMethod(),
        //     'postData' => $this->request->getPost()
        // ]);

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
            
            // Updates the database, order status
            $this->db->table('orders')
                        ->where('id', $orderId)
                        ->where("table_id IN (
                            SELECT id FROM restaurant_tables 
                            WHERE tenant_id = '{$this->tenantId}')" 
                        )
                        ->update([
                            'status' => $newStatus,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

            // 2. If the order is finished or cancelled, free up the table
            if ($newStatus === 'completed' || $newStatus === 'cancelled') {
                // First, get the table_id for this order/identify which table this order is using?
                $order = $this->db->table('orders')
                                ->where('id', $orderId)
                                ->get()
                                ->getRow();
                if ($order && $order->table_id) {
                    // Now, update the restaurant_tables to set is_occupied = 0
                    $this->db->table('restaurant_tables')
                                ->where('id', $order->table_id)
                                ->where('tenant_id', $this->tenantId)
                                ->update(['status' => 'available']);

                }
            }

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