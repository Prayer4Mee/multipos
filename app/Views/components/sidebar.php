<?php
// ============================================================================
// Sidebar Component
// app/Views/components/sidebar.php
// ============================================================================
?>
<div class="col-md-3 col-lg-2 px-0">
    <div class="sidebar bg-primary">
        <div class="p-3">
            <h4 class="text-white mb-4">
                <i class="fas fa-store"></i> <?= $tenant->restaurant_name ?? 'Restaurant' ?>
            </h4>
            
            <!-- User Role Badge -->
            <div class="mb-3">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-user-tag"></i> <?= ucfirst($current_user->role ?? 'staff') ?>
                </span>
            </div>
            
            <nav class="nav flex-column">
                <!-- Main Navigation - All Users -->
                <a class="nav-link <?= (uri_string() == "restaurant/{$tenant->tenant_slug}/dashboard") ? 'active' : '' ?>" 
                   href="<?= base_url("restaurant/{$tenant->tenant_slug}/dashboard") ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <?php if (in_array($current_user->role ?? 'staff', ['cashier', 'manager', 'owner', 'staff'])): ?>
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/pos") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/pos") ?>">
                        <i class="fas fa-cash-register"></i> POS System
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($current_user->role ?? 'staff', ['kitchen_staff', 'manager', 'owner'])): ?>
                    <a class="nav-link <?= (uri_string() == "restaurant/{$tenant->tenant_slug}/kitchen") ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/kitchen") ?>">
                        <i class="fas fa-utensils"></i> Kitchen Display
                        <span class="badge bg-warning ms-auto">8</span>
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($current_user->role ?? 'staff', ['waiter', 'manager', 'owner', 'staff'])): ?>
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/tables") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/tables") ?>">
                        <i class="fas fa-chair"></i> Tables
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($current_user->role ?? 'staff', ['manager', 'owner', 'accountant'])): ?>
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/reports") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/reports") ?>">
                        <i class="fas fa-chart-line"></i> Reports
                    </a>
                <?php endif; ?>
                
                <!-- Management Section -->
                <?php if (in_array($current_user->role ?? 'staff', ['manager', 'owner'])): ?>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <h6 class="text-white-50 px-3 mb-2">Management</h6>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/staff") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/staff") ?>">
                        <i class="fas fa-users"></i> Staff Management
                        <span class="badge bg-warning ms-auto">3</span>
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/menu") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/menu") ?>">
                        <i class="fas fa-utensils"></i> Menu Management
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/inventory") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/inventory") ?>">
                        <i class="fas fa-boxes"></i> Inventory
                        <span class="badge bg-danger ms-auto">5</span>
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/settings") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/settings") ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                <?php endif; ?>
                
                <!-- Cashier Specific -->
                <?php if (($current_user->role ?? 'staff') === 'cashier'): ?>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <h6 class="text-white-50 px-3 mb-2">Cashier Tools</h6>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/transactions") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/transactions") ?>">
                        <i class="fas fa-receipt"></i> Transactions
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/customers") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/customers") ?>">
                        <i class="fas fa-users"></i> Customers
                    </a>
                <?php endif; ?>
                
                <!-- Kitchen Staff Specific -->
                <?php if (($current_user->role ?? 'staff') === 'kitchen_staff'): ?>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <h6 class="text-white-50 px-3 mb-2">Kitchen Tools</h6>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/kitchen/orders") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/kitchen/orders") ?>">
                        <i class="fas fa-clipboard-list"></i> Order Management
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/kitchen/inventory") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/kitchen/inventory") ?>">
                        <i class="fas fa-warehouse"></i> Kitchen Inventory
                    </a>
                <?php endif; ?>
                
                <!-- Waiter Specific -->
                <?php if (($current_user->role ?? 'staff') === 'waiter'): ?>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <h6 class="text-white-50 px-3 mb-2">Service Tools</h6>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/orders") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/orders") ?>">
                        <i class="fas fa-clipboard-check"></i> Take Orders
                    </a>
                    
                    <a class="nav-link <?= (strpos(uri_string(), "restaurant/{$tenant->tenant_slug}/reservations") !== false) ? 'active' : '' ?>" 
                       href="<?= base_url("restaurant/{$tenant->tenant_slug}/reservations") ?>">
                        <i class="fas fa-calendar-alt"></i> Reservations
                    </a>
                <?php endif; ?>
                
                <!-- Common Menu Items -->
                <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                
                <a class="nav-link" href="<?= base_url("restaurant/{$tenant->tenant_slug}/profile") ?>">
                    <i class="fas fa-user"></i> Profile
                </a>
                
                <a class="nav-link" href="<?= base_url("restaurant/{$tenant->tenant_slug}/help") ?>">
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
    justify-content: flex-start;
    text-align: left;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    color: white;
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

.sidebar .nav-link i {
    width: 20px;
    margin-right: 12px;
    text-align: center;
    flex-shrink: 0;
}

.sidebar .nav-link .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
    margin-left: auto;
    flex-shrink: 0;
}

.sidebar .nav-link span:not(.badge) {
    flex-grow: 1;
    text-align: left;
}

.sidebar hr {
    margin: 15px 10px;
}

/* 메뉴 텍스트 정렬 개선 */
.sidebar .nav-link {
    text-align: left;
    justify-content: flex-start;
}

/* 아이콘과 텍스트 간격 조정 */
.sidebar .nav-link i {
    min-width: 20px;
    text-align: center;
}

/* 섹션 헤더 스타일 */
.sidebar h6 {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

/* 구분선 스타일 */
.sidebar hr {
    border-top: 1px solid rgba(255,255,255,0.2);
    margin: 15px 10px;
}
</style>