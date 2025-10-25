<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base Restaurant Controller
 * Handles multi-tenant architecture and common functionality
 */
class BaseRestaurantController extends Controller
{
    /**
     * Instance of the main Request object.
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     */
    protected $helpers = ['url', 'form', 'session', 'cookie'];

    /**
     * Multi-tenant properties
     */
    protected $tenantId;
    protected $tenantConfig;
    protected $tenantDb;
    protected $currentUser;
    protected $userRole;

    /**
     * Constructor
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Load required services
        $this->session = \Config\Services::session();
        
        // Extract tenant from URL
        $this->extractTenantFromUrl();
        
        // Validate tenant
        if (!$this->validateTenant()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }
        
        // Setup tenant-specific database
        $this->setupTenantDatabase();
        
        // Load tenant configuration
        $this->loadTenantConfiguration();
        
        // Check authentication (except for public endpoints) - temporarily disabled
        // if (!$this->isPublicEndpoint()) {
        //     $this->checkAuthentication();
        // }
        
        // Set user role for testing
        $this->userRole = 'manager';
        
        // Set dummy user data for testing
        $this->currentUser = (object) [
            'id' => 1,
            'username' => 'test_user',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'manager',
            'tenant_id' => $this->tenantId,
            'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Test Restaurant',
            'employee_id' => 'EMP001'
        ];
        
        // Set view data available to all controllers
        $this->setGlobalViewData();
    }

    /**
     * Extract tenant ID from URL
     */
    protected function extractTenantFromUrl(): void
    {
        $uri = $this->request->getUri();
        $segments = explode('/', trim($uri->getPath(), '/'));
        
        // URL structure: /restaurant/{tenant_slug}/controller/method
        if (isset($segments[1]) && $segments[0] === 'restaurant') {
            $this->tenantId = $segments[1];
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid restaurant URL');
        }
    }

    /**
     * Validate tenant exists and is active
     */
    protected function validateTenant(): bool
    {
        try {
            $masterDb = \Config\Database::connect('default');
            $query = $masterDb->table('tenants')
                             ->where('tenant_slug', $this->tenantId)
                             ->where('status', 'active')
                             ->get();
            
            if ($query->getNumRows() === 0) {
                log_message('error', "Tenant not found: {$this->tenantId}");
                return false;
            }
            
            $this->tenantConfig = $query->getRow();
            log_message('debug', "Tenant found: " . json_encode($this->tenantConfig));
            return true;
        } catch (\Exception $e) {
            log_message('error', "Database error in validateTenant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Setup tenant-specific database connection
     */
    protected function setupTenantDatabase(): void
    {
        try {
            $customConfig = [
                'hostname' => 'localhost',
                'database' => "restaurant_{$this->tenantId}_db",
                'username' => 'phpmyadmin',
                'password' => 'zxcqwe123$',
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

            $this->tenantDb = \Config\Database::connect($customConfig);
            log_message('debug', "Tenant database connected: restaurant_{$this->tenantId}_db");
        } catch (\Exception $e) {
            log_message('error', "Failed to connect to tenant database: " . $e->getMessage());
            // 테넌트 DB가 없으면 기본 DB 사용
            $this->tenantDb = \Config\Database::connect('default');
        }
    }

    /**
     * Load tenant-specific configuration
     */
    protected function loadTenantConfiguration(): void
    {
        try {
            // Get settings from tenant database
            $settings = $this->tenantDb->table('settings')->get()->getResult();
            
            $config = [
                'tenant_slug' => $this->tenantId,
                'restaurant_name' => $this->tenantConfig->restaurant_name,
                'theme_color' => $this->tenantConfig->theme_color ?? '#667eea',
                'logo_url' => $this->tenantConfig->logo_url,
                'currency' => $this->tenantConfig->currency ?? 'PHP',
                'tax_rate' => $this->tenantConfig->tax_rate ?? 0.12,
                'service_charge_rate' => $this->tenantConfig->service_charge_rate ?? 0.10,
                'timezone' => $this->tenantConfig->timezone ?? 'Asia/Manila',
            ];
            
            // Add database settings
            if ($settings) {
                foreach ($settings as $setting) {
                    $config[$setting->setting_key] = $setting->setting_value;
                }
            }
            
            $this->tenantConfig = (object) $config;
        } catch (\Exception $e) {
            log_message('error', "Failed to load tenant configuration: " . $e->getMessage());
            // 기본 설정 사용
            $this->tenantConfig = (object) [
                'tenant_slug' => $this->tenantId,
                'restaurant_name' => $this->tenantConfig->restaurant_name ?? 'Restaurant',
                'theme_color' => '#667eea',
                'logo_url' => null,
                'currency' => 'PHP',
                'tax_rate' => 0.12,
                'service_charge_rate' => 0.10,
                'timezone' => 'Asia/Manila',
            ];
        }
    }

    /**
     * Check if current endpoint is public (no authentication required)
     */
    protected function isPublicEndpoint(): bool
    {
        $publicEndpoints = [
            'restaurant/*/customer/*',
            'restaurant/*/qr/*',
            'restaurant/*/api/menu',
            'restaurant/*/kiosk'
        ];
        
        $currentPath = $this->request->getUri()->getPath();
        
        foreach ($publicEndpoints as $pattern) {
            if (fnmatch($pattern, $currentPath)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check user authentication
     */
    protected function checkAuthentication()
    {
        $userId = $this->session->get("user_id_{$this->tenantId}");
        
        if (!$userId) {
            // Redirect to login
            return redirect()->to("/restaurant/{$this->tenantId}/auth/login");
        }
        
        // Load current user (temporarily disabled for testing)
        // $this->currentUser = $this->tenantDb->table('users')
        //                                   ->where('id', $userId)
        //                                   ->where('is_active', 1)
        //                                   ->get()
        //                                   ->getRow();
        
        // if (!$this->currentUser) {
        //     $this->session->remove("user_id_{$this->tenantId}");
        //     return redirect()->to("/restaurant/{$this->tenantId}/auth/login");
        // }
        
        // Load user from session or set default for testing
        $sessionUserId = $this->session->get('user_id');
        $sessionRole = $this->session->get('role');
        $sessionTenantId = $this->session->get('tenant_id');
        
        // Check if user is logged in
        if (!$sessionUserId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Authentication required');
        }

        // Check tenant isolation - users can only access their assigned tenant
        if ($sessionTenantId !== $this->tenantId) {
            log_message('warning', "User {$sessionUserId} attempted to access tenant {$this->tenantId} but is assigned to {$sessionTenantId}");
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied: Invalid tenant');
        }

        // Load user data from session
        $this->currentUser = (object) [
            'id' => $sessionUserId,
            'username' => $this->session->get('username'),
            'name' => $this->session->get('full_name'),
            'email' => $this->session->get('email'),
            'role' => $sessionRole,
            'tenant_id' => $sessionTenantId,
            'restaurant_name' => $this->session->get('restaurant_name'),
            'employee_id' => $this->session->get('employee_id')
        ];
        
        log_message('debug', "User {$this->currentUser->username} authenticated for tenant {$this->tenantId}");
        
        $this->userRole = $this->currentUser->role;
    }

    /**
     * Set global view data
     */
    protected function setGlobalViewData(): void
    {
        $this->data = [
            'tenant_id' => $this->tenantId,
            'tenant' => $this->tenantConfig,
            'tenant_config' => $this->tenantConfig,
            'current_user' => $this->currentUser,
            'user_role' => $this->userRole,
            'base_url' => base_url("/restaurant/{$this->tenantId}"),
        ];
    }

    /**
     * Check user role permission
     */
    protected function requireRole(array $allowedRoles): bool
    {
        if (!in_array($this->userRole, $allowedRoles)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }
        return true;
    }

    /**
     * JSON response helper
     */
    protected function jsonResponse($data, int $statusCode = 200): ResponseInterface
    {
        return $this->response->setJSON($data)->setStatusCode($statusCode);
    }

    /**
     * Load tenant view with tenant-specific template
     */
    protected function loadTenantView(string $view, array $data = []): string
    {
        $data = array_merge($this->data, $data);
        
        // Check for tenant-specific view first
        $tenantView = "tenants/{$this->tenantId}/{$view}";
        $defaultView = "restaurant/{$view}";
        
        if (is_file(APPPATH . "Views/{$tenantView}.php")) {
            return view($tenantView, $data);
        } else {
            return view($defaultView, $data);
        }
    }
}