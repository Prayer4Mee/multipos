<?php
// Get user role from session or default to 'manager'
$userRole = session()->get('role') ?? 'manager';
$tenant = $tenant ?? null;
$tenantName = $tenant['restaurant_name'] ?? 'Restaurant';
$tenantSlug = $tenant['tenant_slug'] ?? 'default';
?>

<div class="col-md-3 col-lg-2 px-0">
    <div class="sidebar">
        <div class="p-3">
            <h4 class="text-white mb-4">
                <i class="fas fa-store"></i> <?= esc($tenantName) ?>
            </h4>
            
            <!-- User Role Badge -->
            <div class="mb-3">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-user-tag"></i> <?= ucfirst($userRole) ?>
                </span>
            </div>
            
            <nav class="nav flex-column">
                <?php if ($userRole === 'manager' || $userRole === 'owner'): ?>
                    <!-- Manager/Owner Menu -->
                    <a class="nav-link <?= (uri_string() == "restaurant/{$tenantSlug}/dashboard") ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/dashboard") ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/staff") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/staff") ?>">
                        <i class="fas fa-users"></i> Staff Management
                        <span class="badge bg-warning ms-auto">3</span>
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/menu") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/menu") ?>">
                        <i class="fas fa-utensils"></i> Menu Management
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/inventory") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/inventory") ?>">
                        <i class="fas fa-boxes"></i> Inventory
                        <span class="badge bg-danger ms-auto">5</span>
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/reports") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/reports") ?>">
                        <i class="fas fa-chart-bar"></i> Reports & Analytics
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/settings") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/settings") ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    
                <?php elseif ($userRole === 'cashier'): ?>
                    <!-- Cashier Menu -->
                    <a class="nav-link <?= (uri_string() == "restaurant/{$tenantSlug}/pos") ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/pos") ?>">
                        <i class="fas fa-cash-register"></i> POS System
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/transactions") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/transactions") ?>">
                        <i class="fas fa-receipt"></i> Transactions
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/customers") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/customers") ?>">
                        <i class="fas fa-users"></i> Customers
                    </a>
                    
                <?php elseif ($userRole === 'kitchen_staff'): ?>
                    <!-- Kitchen Menu -->
                    <a class="nav-link <?= (uri_string() == "restaurant/{$tenantSlug}/kitchen") ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/kitchen") ?>">
                        <i class="fas fa-tv"></i> Kitchen Display
                        <span class="badge bg-warning ms-auto">8</span>
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/kitchen/orders") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/kitchen/orders") ?>">
                        <i class="fas fa-clipboard-list"></i> Order Management
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/kitchen/inventory") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/kitchen/inventory") ?>">
                        <i class="fas fa-warehouse"></i> Kitchen Inventory
                    </a>
                    
                <?php elseif ($userRole === 'waiter'): ?>
                    <!-- Waiter/Server Menu -->
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/tables") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/tables") ?>">
                        <i class="fas fa-chair"></i> Table Management
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/orders") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/orders") ?>">
                        <i class="fas fa-clipboard-check"></i> Take Orders
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenantSlug}/reservations") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenantSlug}/reservations") ?>">
                        <i class="fas fa-calendar-alt"></i> Reservations
                    </a>
                    
                <?php else: ?>
                    <!-- Default Menu -->
                    <a class="nav-link active" href="<?= base_url("restaurant/{$tenantSlug}/dashboard") ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                <?php endif; ?>
                
                <!-- Common Menu Items -->
                <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                
                <a class="nav-link" href="<?= base_url("restaurant/{$tenantSlug}/profile") ?>">
                    <i class="fas fa-user"></i> Profile
                </a>
                
                <a class="nav-link" href="<?= base_url("restaurant/{$tenantSlug}/help") ?>">
                    <i class="fas fa-question-circle"></i> Help
                </a>
                
                <a class="nav-link" href="<?= base_url('auth/logout') ?>">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </div>
</div>

<style>
.sidebar {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.sidebar .nav-link {
    color: rgba(255,255,255,0.8);
    padding: 12px 20px;
    border-radius: 8px;
    margin: 5px 10px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    color: white;
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

.sidebar .nav-link i {
    width: 20px;
    margin-right: 10px;
}

.sidebar .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
}

.sidebar hr {
    margin: 15px 10px;
}
</style>