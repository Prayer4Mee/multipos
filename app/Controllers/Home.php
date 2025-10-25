<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * TouchPoint POS - Main Landing Page
     * Shows list of active restaurants for multi-tenant access
     */
    public function index(): string
    {
        // Load tenant model
        $tenantModel = new \App\Models\Master\TenantModel();
        
        // Get all active tenants
        $tenants = $tenantModel->where('status', 'active')
                               ->orderBy('restaurant_name', 'ASC')
                               ->findAll();
        
        // Prepare view data
        $data = [
            'title' => 'TouchPoint POS - Multi-Tenant Restaurant System',
            'tenants' => $tenants,
            'total_tenants' => count($tenants),
            'system_version' => '1.0.0',
            'system_name' => 'TouchPoint POS',
            'company_name' => 'NTEKSYSTEMS Inc.',
            'current_user' => [
                'user_id' => session()->get('user_id'),
                'username' => session()->get('username'),
                'role' => session()->get('role')
            ]
        ];
        
        return view('Home/index', $data);
    }
    
    /**
     * About page
     */
    public function about(): string
    {
        $data = [
            'title' => 'About TouchPoint POS',
            'system_name' => 'TouchPoint POS',
            'version' => '1.0.0',
            'company_name' => 'NTEKSYSTEMS Inc.',
            'features' => [
                'Multi-tenant architecture',
                'BIR compliant POS system',
                'Kitchen Display System (KDS)',
                'GCash & Maya integration',
                'Real-time inventory management',
                'Table management & QR ordering',
                'Comprehensive reporting'
            ]
        ];
        
        return view('Home/about', $data);
    }
    
    /**
     * Contact page
     */
    public function contact(): string
    {
        $data = [
            'title' => 'Contact Us',
            'company_name' => 'NTEKSYSTEMS Inc.',
            'email' => 'support@nteksystems.com',
            'phone' => '+63 (2) 8888-8888',
            'address' => 'Metro Manila, Philippines'
        ];
        
        return view('Home/contact', $data);
    }
    
    /**
     * System health check (for monitoring)
     */
    public function healthCheck()
    {
        $db = \Config\Database::connect();
        
        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $db->connID ? 'connected' : 'disconnected',
            'version' => '1.0.0'
        ];
        
        return $this->response->setJSON($health);
    }
    
    /**
     * Demo restaurant selector
     * Shows a modal or page for selecting demo restaurants
     */
    public function demo(): string
    {
        $tenantModel = new \App\Models\Master\TenantModel();
        
        // Get demo tenants (you can add a 'is_demo' flag to tenants table)
        $demoTenants = $tenantModel->where('status', 'active')
                                   ->limit(3)
                                   ->findAll();
        
        $data = [
            'title' => 'Try Demo',
            'demo_tenants' => $demoTenants
        ];
        
        return view('Home/demo', $data);
    }
}