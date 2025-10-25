<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Base Model for Multi-Tenant Architecture
 * Provides common functionality for all models
 */
abstract class BaseModel extends Model
{
    protected $DBGroup = 'default';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    /**
     * Custom database connection for tenant-specific operations
     */
    protected $tenantDb = null;
    
    /**
     * Set database connection for tenant
     */
    public function setDB($db)
    {
        $this->db = $db;
        $this->tenantDb = $db;
        return $this;
    }
    
    /**
     * Initialize model with tenant database
     */
    public function __construct()
    {
        parent::__construct();
        
        // Initialize tenant database if needed
        $this->tenantDb = null;
    }
    
    /**
     * Get records with pagination and filtering
     */
    public function getPaginated($perPage = 20, $page = 1, $filters = [])
    {
        $builder = $this->builder();
        
        // Apply filters
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $builder->whereIn($field, $value);
                } else {
                    $builder->like($field, $value);
                }
            }
        }
        
        return $builder->paginate($perPage, 'default', $page);
    }
    
    /**
     * Get active records only
     */
    public function getActive($where = [])
    {
        $builder = $this->builder();
        
        if (in_array('is_active', $this->allowedFields)) {
            $builder->where('is_active', true);
        }
        
        if (!empty($where)) {
            $builder->where($where);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Soft delete functionality
     */
    public function softDelete($id)
    {
        if (in_array('is_active', $this->allowedFields)) {
            return $this->update($id, ['is_active' => false]);
        }
        
        return $this->delete($id);
    }
    
    /**
     * Restore soft deleted record
     */
    public function restore($id)
    {
        if (in_array('is_active', $this->allowedFields)) {
            return $this->update($id, ['is_active' => true]);
        }
        
        return false;
    }
    
    /**
     * Get records count with filters
     */
    public function getCount($filters = [])
    {
        $builder = $this->builder();
        
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $builder->where($field, $value);
            }
        }
        
        return $builder->countAllResults();
    }
}