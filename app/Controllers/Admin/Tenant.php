<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Master\TenantModel;

class Tenant extends BaseController
{
    protected $tenantModel;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
    }

    public function index()
    {
        $tenants = $this->tenantModel->findAll();

        $data = [
            'title' => 'Tenant Management - MultiPOS',
            'page_title' => 'Tenant Management',
            'tenants' => $tenants
        ];

        return view('admin/tenant/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Create Tenant - MultiPOS',
            'page_title' => 'Create New Tenant'
        ];

        return view('admin/tenant/create', $data);
    }

    public function store()
    {
        $rules = [
            'tenant_slug' => 'required|min_length[3]|max_length[50]|alpha_dash|is_unique[tenants.tenant_slug]',
            'restaurant_name' => 'required|min_length[3]|max_length[100]',
            'owner_name' => 'required|min_length[3]|max_length[100]',
            'owner_email' => 'required|valid_email|is_unique[tenants.owner_email]',
            'business_type' => 'required|in_list[restaurant,cafe,retail,medical,automotive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'tenant_slug' => $this->request->getPost('tenant_slug'),
            'restaurant_name' => $this->request->getPost('restaurant_name'),
            'business_type' => $this->request->getPost('business_type'),
            'owner_name' => $this->request->getPost('owner_name'),
            'owner_email' => $this->request->getPost('owner_email'),
            'owner_phone' => $this->request->getPost('owner_phone'),
            'business_address' => $this->request->getPost('business_address'),
            'subscription_plan' => $this->request->getPost('subscription_plan', FILTER_SANITIZE_STRING) ?: 'startup',
            'subscription_status' => 'active',
            'status' => 'active'
        ];

        try {
            $tenantId = $this->tenantModel->createTenant($data);
            
            return redirect()->to('/admin/tenants')->with('success', 'Tenant created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create tenant: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $tenant = $this->tenantModel->find($id);
        
        if (!$tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Tenant not found');
        }

        $data = [
            'title' => 'Edit Tenant - MultiPOS',
            'page_title' => 'Edit Tenant',
            'tenant' => $tenant
        ];

        return view('admin/tenant/edit', $data);
    }

    public function update($id)
    {
        $tenant = $this->tenantModel->find($id);
        
        if (!$tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Tenant not found');
        }

        $rules = [
            'restaurant_name' => 'required|min_length[3]|max_length[100]',
            'owner_name' => 'required|min_length[3]|max_length[100]',
            'owner_email' => "required|valid_email|is_unique[tenants.owner_email,id,{$id}]",
            'business_type' => 'required|in_list[restaurant,cafe,retail,medical,automotive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'restaurant_name' => $this->request->getPost('restaurant_name'),
            'business_type' => $this->request->getPost('business_type'),
            'owner_name' => $this->request->getPost('owner_name'),
            'owner_email' => $this->request->getPost('owner_email'),
            'owner_phone' => $this->request->getPost('owner_phone'),
            'business_address' => $this->request->getPost('business_address'),
            'subscription_plan' => $this->request->getPost('subscription_plan', FILTER_SANITIZE_STRING),
            'subscription_status' => $this->request->getPost('subscription_status', FILTER_SANITIZE_STRING),
            'status' => $this->request->getPost('status', FILTER_SANITIZE_STRING)
        ];

        if ($this->tenantModel->update($id, $data)) {
            return redirect()->to('/admin/tenants')->with('success', 'Tenant updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update tenant');
        }
    }

    public function delete($id)
    {
        $tenant = $this->tenantModel->find($id);
        
        if (!$tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Tenant not found');
        }

        if ($this->tenantModel->delete($id)) {
            return redirect()->to('/admin/tenants')->with('success', 'Tenant deleted successfully!');
        } else {
            return redirect()->to('/admin/tenants')->with('error', 'Failed to delete tenant');
        }
    }

    public function suspend($id)
    {
        $tenant = $this->tenantModel->find($id);
        
        if (!$tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Tenant not found');
        }

        if ($this->tenantModel->update($id, ['status' => 'suspended'])) {
            return redirect()->to('/admin/tenants')->with('success', 'Tenant suspended successfully!');
        } else {
            return redirect()->to('/admin/tenants')->with('error', 'Failed to suspend tenant');
        }
    }

    public function activate($id)
    {
        $tenant = $this->tenantModel->find($id);
        
        if (!$tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Tenant not found');
        }

        if ($this->tenantModel->update($id, ['status' => 'active'])) {
            return redirect()->to('/admin/tenants')->with('success', 'Tenant activated successfully!');
        } else {
            return redirect()->to('/admin/tenants')->with('error', 'Failed to activate tenant');
        }
    }
}
