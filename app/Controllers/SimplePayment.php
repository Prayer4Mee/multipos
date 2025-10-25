<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class SimplePayment extends Controller
{
    public function index($orderId)
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Simple Payment controller accessed',
            'order_id' => $orderId,
            'method' => 'index'
        ]);
    }
    
    public function printReceipt($orderId)
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Simple Payment print receipt accessed',
            'order_id' => $orderId,
            'method' => 'printReceipt'
        ]);
    }
}
