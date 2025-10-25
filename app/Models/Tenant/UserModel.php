<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;

/**
 * User Model
 * Manages restaurant staff and users
 */
class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'username', 'email', 'password_hash', 'employee_id', 'first_name',
        'last_name', 'phone', 'address', 'emergency_contact', 'role',
        'permissions', 'department', 'hire_date', 'hourly_rate',
        'employment_status', 'pin_code', 'is_active'
    ];
    
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|alpha_numeric',
        'email' => 'permit_empty|valid_email',
        'first_name' => 'required|min_length[2]|max_length[50]',
        'last_name' => 'required|min_length[2]|max_length[50]',
        'role' => 'required|in_list[manager,cashier,kitchen_staff,waiter,owner,accountant]',
        'employment_status' => 'in_list[active,inactive,terminated]'
    ];
    
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    
    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            unset($data['data']['password']);
        }
        
        return $data;
    }
    
    /**
     * Get user by username
     */
    public function getUserByUsername($username)
    {
        return $this->where('username', $username)
                    ->where('is_active', true)
                    ->where('employment_status', 'active')
                    ->first();
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)
                    ->where('is_active', true)
                    ->where('employment_status', 'active')
                    ->findAll();
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword($user, $password)
    {
        return password_verify($password, $user['password_hash']);
    }
    
    /**
     * Update last login time
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'failed_login_attempts' => 0
        ]);
    }
    
    /**
     * Get staff performance metrics
     */
    public function getStaffPerformance($startDate, $endDate)
    {
        return $this->select('users.*, COUNT(orders.id) as orders_processed, SUM(orders.total_amount) as total_sales')
                    ->join('orders', 'orders.cashier_id = users.id OR orders.waiter_id = users.id', 'left')
                    ->where('users.is_active', true)
                    ->where('orders.ordered_at >=', $startDate)
                    ->where('orders.ordered_at <=', $endDate)
                    ->groupBy('users.id')
                    ->findAll();
    }
}

