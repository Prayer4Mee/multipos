<?php

namespace App\Models\Master;

use App\Models\BaseModel;

/**
 * Tenant Model
 * Manages restaurant tenant instances
 */
class TenantModel extends BaseModel
{
    protected $table = 'tenants';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'tenant_slug', 'restaurant_name', 'business_type', 'owner_name', 
        'owner_email', 'owner_phone', 'business_address', 'tin_number',
        'vat_registered', 'bir_permit_number', 'subscription_plan',
        'subscription_status', 'subscription_expires_at', 'monthly_fee',
        'settings', 'theme_color', 'logo_url', 'timezone', 'currency',
        'status', 'database_name'
    ];
    
    protected $validationRules = [
        'tenant_slug' => 'required|min_length[3]|max_length[50]|alpha_dash|is_unique[tenants.tenant_slug]',
        'restaurant_name' => 'required|min_length[3]|max_length[100]',
        'owner_name' => 'required|min_length[3]|max_length[100]',
        'owner_email' => 'required|valid_email|is_unique[tenants.owner_email]',
        'business_type' => 'required|in_list[restaurant,cafe,retail,medical,automotive]',
        'subscription_plan' => 'in_list[startup,sme,enterprise]'
    ];
    
    protected $validationMessages = [
        'tenant_slug' => [
            'is_unique' => 'This restaurant identifier is already taken'
        ],
        'owner_email' => [
            'is_unique' => 'This email is already registered'
        ]
    ];
    
    /**
     * Get tenant by slug
     */
    public function getBySlug($slug)
    {
        return $this->where('tenant_slug', $slug)
                    ->where('status', 'active')
                    ->first();
    }
    
    /**
     * Get active tenants
     */
    public function getActiveTenants()
    {
        return $this->where('status', 'active')
                    ->where('subscription_status', 'active')
                    ->findAll();
    }
    
    /**
     * Create new tenant with database
     */
    public function createTenant($data)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Generate database name
            $data['database_name'] = 'restaurant_' . $data['tenant_slug'] . '_db';
            
            // Insert tenant record
            $tenantId = $this->insert($data);
            
            if (!$tenantId) {
                throw new \Exception('Failed to create tenant record');
            }
            
            // Create tenant database
            $this->createTenantDatabase($data['database_name']);
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }
            
            return $tenantId;
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Tenant creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create tenant-specific database
     */
    private function createTenantDatabase($databaseName)
    {
        $db = \Config\Database::connect();
        
        // Create database
        $db->query("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        // Switch to new database
        $db->query("USE `{$databaseName}`");
        
        // Run tenant database schema
        $this->runTenantSchema($db);
    }
    
    /**
     * Execute tenant database schema
     */
    private function runTenantSchema($db)
    {
        $schemaFile = APPPATH . 'Database/Schema/tenant_schema.sql';
        
        if (!file_exists($schemaFile)) {
            throw new \Exception('Tenant schema file not found');
        }
        
        $schema = file_get_contents($schemaFile);
        $statements = explode(';', $schema);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $db->query($statement);
            }
        }
    }
}
