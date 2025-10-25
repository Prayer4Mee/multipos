<?php
// ============================================================================
// TouchPoint POS View Structure
// NTEKSYSTEMS Inc. - User Group Based View Organization
// ============================================================================
// ============================================================================
// Main Layout Template
// app/Views/layouts/main.php
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= $title ?? 'TouchPoint POS' ?> - <?= $tenant->restaurant_name ?? 'Restaurant' ?></title>
    
    <!-- Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* TouchPoint Core Styles */
        :root {
            --primary-color: <?= $tenant->theme_color ?? '#4facfe' ?>;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        .bg-primary { background-color: var(--primary-color) !important; }
        .text-primary { color: var(--primary-color) !important; }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: var(--primary-color); border-color: var(--primary-color); opacity: 0.9; }
        
        .sidebar { 
            min-height: calc(100vh - 56px); 
            position: sticky;
            top: 56px;
        }
        .content-area { 
            padding: 20px;
            min-height: calc(100vh - 56px);
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 10px 15px;
            margin: 2px 0;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            text-align: left;
        }
        .nav-link:hover {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
        }
        .nav-link.active {
            color: white !important;
            background-color: rgba(255,255,255,0.2);
        }
        .nav-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
            flex-shrink: 0;
        }
        .nav-link .badge {
            margin-left: auto;
            flex-shrink: 0;
        }
        @media (max-width: 768px) { 
            .sidebar { 
                position: relative;
                top: 0;
                min-height: auto;
            }
        }
    </style>
    
    <!-- Tenant specific CSS -->
    <?php if (isset($tenant->tenant_slug)): ?>
        <style>
            /* Jollibee specific styles */
            .jollibee-theme { 
                --primary-color: #e74c3c; 
                --secondary-color: #f39c12; 
            }
        </style>
    <?php endif; ?>
    
    <!-- Dynamic theme colors -->
    <style>
        :root {
            --primary-color: <?= $tenant->theme_color ?? '#4facfe' ?>;
            --tenant-name: "<?= $tenant->restaurant_name ?? 'Restaurant' ?>";
        }
    </style>
    
    <?= $this->renderSection('head') ?>
</head>
<body class="<?= $body_class ?? '' ?>">
    <!-- Navigation -->
    <?= $this->include('components/navigation') ?>
    
    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php if (!isset($hide_sidebar)): ?>
                <?= $this->include('components/sidebar') ?>
            <?php endif; ?>
            
            <!-- Content Area -->
            <main class="<?= isset($hide_sidebar) ? 'col-12' : 'col-md-9 col-lg-10' ?> content-area">
            <!-- Breadcrumb -->
            <?php if (isset($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <li class="breadcrumb-item <?= $crumb['active'] ? 'active' : '' ?>">
                                <?php if ($crumb['active']): ?>
                                    <?= $crumb['text'] ?>
                                <?php else: ?>
                                    <a href="<?= $crumb['url'] ?>"><?= $crumb['text'] ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
            
            <!-- Flash Messages -->
            <?php if (session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= session('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Page Content -->
            <div class="page-content">
                <?= $this->renderSection('content') ?>
            </div>
            </main>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // TouchPoint Core JavaScript
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
    
    <!-- Page specific JavaScript -->
    <?= $this->renderSection('scripts') ?>
    
    <!-- Tenant specific JavaScript -->
    <?php if (isset($tenant->tenant_slug)): ?>
        <script>
            // Jollibee specific JavaScript
            console.log('Jollibee theme loaded');
        </script>
    <?php endif; ?>
    
    <script>
        // Global JavaScript variables
        window.TOUCHPOINT = {
            baseUrl: '<?= base_url("restaurant/{$tenant->tenant_slug}") ?>',
            tenantSlug: '<?= $tenant_slug ?>',
            currentUser: <?= json_encode($current_user) ?>,
            csrfToken: '<?= csrf_hash() ?>',
            settings: <?= json_encode($tenant ?? []) ?>
        };
    </script>
</body>
</html>