<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Master\TenantModel;

class Dashboard extends BaseController
{
    protected $tenantModel;
    protected $helpers = ['url', 'form', 'session'];

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
    }

    public function index()
    {
        try {
            // Check if user is logged in as admin
            $session = \Config\Services::session();
            $userRole = $session->get('role');
            $tenantId = $session->get('tenant_id');
            
            // Check if user is logged in
            if (!$session->get('user_id')) {
                return redirect()->to('/auth/login')->with('error', 'Please login first');
            }
            
            // Check if user is system admin (no tenant_id)
            if ($userRole !== 'manager' || $tenantId !== null) {
                return redirect()->to('/auth/login')->with('error', 'Admin access required');
            }

            // Get tenant statistics with error handling
            $totalTenants = 0;
            $activeTenants = 0;
            $suspendedTenants = 0;
            
            try {
                $totalTenants = $this->tenantModel->countAllResults();
                $activeTenants = $this->tenantModel->where('status', 'active')->countAllResults();
                $suspendedTenants = $this->tenantModel->where('status', 'suspended')->countAllResults();
            } catch (\Exception $e) {
                log_message('error', 'Failed to get tenant statistics: ' . $e->getMessage());
                // Use default values if database query fails
            }
            
            // Calculate total revenue (simplified - you might want to calculate from actual sales)
            $totalRevenue = $this->calculateTotalRevenue();

            // Get employee statistics
            $totalEmployees = 0;
            $activeEmployees = 0;
            $employeesByTenant = [];

            try {
                $db = \Config\Database::connect('default');
                $employees = $db->table('users')
                               ->where('is_active', 1)
                               ->get()
                               ->getResult();

                $totalEmployees = count($employees);
                $activeEmployees = count(array_filter($employees, function($emp) {
                    return $emp->employment_status === 'active';
                }));

                // Group by tenant
                foreach ($employees as $employee) {
                    $tenantId = $employee->tenant_id ?: 'System';
                    if (!isset($employeesByTenant[$tenantId])) {
                        $employeesByTenant[$tenantId] = 0;
                    }
                    $employeesByTenant[$tenantId]++;
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to get employee statistics: ' . $e->getMessage());
            }

            $data = [
                'title' => 'Admin Dashboard - MultiPOS',
                'page_title' => 'Admin Dashboard',
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'suspended_tenants' => $suspendedTenants,
                'total_revenue' => $totalRevenue,
                'employee_stats' => [
                    'total_employees' => $totalEmployees,
                    'active_employees' => $activeEmployees,
                    'employees_by_tenant' => $employeesByTenant
                ],
                'current_user' => [
                    'username' => $session->get('username') ?? 'Admin',
                    'role' => $session->get('role') ?? 'admin'
                ]
            ];

            return view('admin/dashboard', $data);
        } catch (\Exception $e) {
            log_message('error', 'Admin Dashboard error: ' . $e->getMessage());
            
            // Return basic dashboard even if there's an error
            $data = [
                'title' => 'Admin Dashboard - MultiPOS',
                'page_title' => 'Admin Dashboard',
                'total_tenants' => 0,
                'active_tenants' => 0,
                'suspended_tenants' => 0,
                'total_revenue' => 0,
                'current_user' => [
                    'username' => 'Admin',
                    'role' => 'admin'
                ]
            ];
            
            return view('admin/dashboard', $data);
        }
    }

    private function calculateTotalRevenue()
    {
        // This is a simplified calculation
        // In a real application, you would sum up all tenant revenues
        try {
            // Get all active tenants
            $tenants = $this->tenantModel->where('status', 'active')->findAll();
            $totalRevenue = 0;
            
            foreach ($tenants as $tenant) {
                // For demo purposes, we'll use a simple calculation
                // In reality, you'd query each tenant's sales data
                $totalRevenue += 50000; // Demo amount per tenant
            }
            
            return $totalRevenue;
        } catch (\Exception $e) {
            log_message('error', 'Failed to calculate total revenue: ' . $e->getMessage());
            return 0;
        }
    }

    // Simple test method
    public function test()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Admin Dashboard is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
