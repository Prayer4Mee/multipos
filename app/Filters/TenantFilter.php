<?php
// app/Filters/TenantFilter.php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Master\TenantModel;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $uri = $request->getUri();
        $segments = $uri->getSegments();
        
        // Extract tenant slug from URL
        // URL pattern: /restaurant/{tenant_slug}/...
        if (isset($segments[0]) && $segments[0] === 'restaurant' && isset($segments[1])) {
            $tenantSlug = $segments[1];
            
            // Load tenant from database
            $tenantModel = new TenantModel();
            $tenant = $tenantModel->where('tenant_slug', $tenantSlug)
                                  ->where('status', 'active')
                                  ->first();
            
            if (!$tenant) {
                return redirect()->to('/')->with('error', 'Restaurant not found or inactive');
            }
            
            // Store tenant info in session/request
            $request->tenant = $tenant;
            $request->tenant_id = $tenant['id'];
            
            // Set tenant database connection
            $this->setTenantDatabase($tenant['id']);
            
            // Load tenant configuration
            $this->loadTenantConfig($tenant);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Cleanup if needed
    }
    
    private function setTenantDatabase($tenantId)
    {
        $config = config('Database');
        $config->default['database'] = "restaurant_{$tenantId}_db";
        
        // Reconnect with new database
        \Config\Database::connect($config->default, false);
    }
    
    private function loadTenantConfig($tenant)
    {
        // Load tenant-specific configuration
        $configFile = APPPATH . "Config/tenants/{$tenant['tenant_slug']}.php";
        
        if (file_exists($configFile)) {
            require_once $configFile;
        }
    }
}
