<?php
// ============================================================================
// Navigation Component
// app/Views/components/navigation.php
// ============================================================================
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Brand/Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url("restaurant/{$tenant->tenant_slug}/dashboard") ?>">
            <?php if (isset($tenant->logo_url)): ?>
                <img src="<?= $tenant->logo_url ?>" alt="Logo" height="40" class="me-2">
            <?php endif; ?>
            <span class="d-none d-md-inline"><?= $tenant->restaurant_name ?? 'Restaurant' ?></span>
        </a>
        
        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- User menu -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <span class="d-none d-md-inline"><?= $current_user->name ?? 'User' ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url("restaurant/{$tenant->tenant_slug}/profile") ?>"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="<?= base_url("restaurant/{$tenant->tenant_slug}/settings") ?>"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* 네비게이션 바 메뉴 아이템 정렬 */
.navbar-nav .nav-link {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    text-align: left;
}

.navbar-nav .nav-link i {
    width: 16px;
    margin-right: 8px;
    text-align: center;
    flex-shrink: 0;
}

/* 드롭다운 메뉴 아이템 정렬 */
.dropdown-item {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    text-align: left;
}

.dropdown-item i {
    width: 16px;
    margin-right: 8px;
    text-align: center;
    flex-shrink: 0;
}
</style>
