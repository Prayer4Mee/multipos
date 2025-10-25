<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    protected $session;
    protected $db;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect('default');
    }

    /**
     * Login page (index method for root redirect)
     */
    public function index()
    {
        return $this->login();
    }

    /**
     * Login page
     */
    public function login()
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->session->get('user_id')) {
            $role = $this->session->get('role');
            $tenantId = $this->session->get('tenant_id');
            
            if ($role === 'admin' || $role === 'manager' && $tenantId === null) {
                return redirect()->to('/admin');
            } elseif ($tenantId) {
                return redirect()->to("/restaurant/{$tenantId}/dashboard");
            }
        }

        $data = [
            'title' => 'Login - MultiPOS'
        ];

        return view('auth/login', $data);
    }

    /**
     * Process login
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        try {
            // Query user from database
            $user = $this->db->table('users')
                           ->where('username', $username)
                           ->where('is_active', 1)
                           ->where('employment_status', 'active')
                           ->get()
                           ->getRow();

            if (!$user) {
                return redirect()->back()->with('error', 'Invalid username or password');
            }

            // Verify password (using password_verify for hashed passwords)
            if (password_verify($password, $user->password_hash)) {
                // Update last login
                $this->db->table('users')
                        ->where('id', $user->id)
                        ->update(['last_login_at' => date('Y-m-d H:i:s')]);

                // Set session data
                $sessionData = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'full_name' => $user->first_name . ' ' . $user->last_name,
                    'role' => $user->role,
                    'tenant_id' => $user->tenant_id,
                    'restaurant_name' => $user->restaurant_name,
                    'employee_id' => $user->employee_id
                ];

                $this->session->set($sessionData);

                log_message('info', "User {$username} logged in successfully");

                // Redirect based on role and tenant
                if ($user->role === 'manager' && $user->tenant_id === null) {
                    // System admin
                    return redirect()->to('/admin')->with('success', 'Admin login successful');
                } elseif ($user->tenant_id) {
                    // Restaurant user
                    return redirect()->to("/restaurant/{$user->tenant_id}/dashboard")->with('success', 'Login successful');
                } else {
                    return redirect()->back()->with('error', 'Invalid user configuration');
                }
            } else {
                return redirect()->back()->with('error', 'Invalid username or password');
            }

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Login failed. Please try again.');
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $username = $this->session->get('username');
        
        $this->session->remove([
            'user_id',
            'username',
            'email',
            'full_name',
            'role',
            'tenant_id',
            'restaurant_name',
            'employee_id'
        ]);

        log_message('info', "User {$username} logged out");

        return redirect()->to('/auth/login')
                        ->with('message', 'You have been logged out successfully');
    }
}