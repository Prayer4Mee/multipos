<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #4facfe;
            --secondary-color: #00f2fe;
            --dark-bg: #1a1a2e;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
        }
        
        .hero-section {
            padding: 80px 0 60px;
            color: white;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero-section p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }
        
        .restaurant-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }
        
        .restaurant-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 20px;
            font-weight: bold;
        }
        
        .restaurant-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .restaurant-type {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .restaurant-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-maintenance {
            background: #fff3cd;
            color: #856404;
        }
        
        .content-section {
            padding: 40px 0;
            background: rgba(255,255,255,0.95);
            margin-top: -30px;
            border-radius: 30px 30px 0 0;
        }
        
        .stat-box {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .footer {
            background: var(--dark-bg);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        
        .btn-access {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-access:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
            color: white;
        }
        
        .no-restaurants {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-restaurants i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <!-- Login Status -->
            <?php if ($current_user['user_id']): ?>
                <div class="alert alert-success text-center mb-4">
                    <i class="bi bi-check-circle"></i> 
                    Welcome back, <strong><?= esc($current_user['username']) ?></strong>! 
                    You are logged in as <strong><?= esc($current_user['role']) ?></strong>.
                    <a href="/auth/logout" class="btn btn-sm btn-outline-danger ms-3">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center mb-4">
                    <a href="/auth/login" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Staff Login
                    </a>
                </div>
            <?php endif; ?>
            
            <h1>
                <i class="bi bi-shop"></i> <?= esc($system_name) ?>
            </h1>
            <p>BIR-Certified Multi-Tenant Restaurant Management System</p>
            
            <!-- Stats -->
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number"><?= $total_tenants ?></div>
                        <div class="stat-label">Active Restaurants</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">BIR Compliant</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">System Uptime</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content-section">
        <div class="container">
            
            <h2 class="text-center mb-4">Select Your Restaurant</h2>
            
            <?php if (!empty($tenants)): ?>
                <div class="row">
                    <?php foreach ($tenants as $tenant): ?>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= base_url('restaurant/' . esc($tenant['tenant_slug'])) ?>" class="text-decoration-none">
                                <div class="restaurant-card">
                                    <!-- Logo -->
                                    <div class="restaurant-logo">
                                        <?php if (!empty($tenant['logo_url'])): ?>
                                            <img src="<?= esc($tenant['logo_url']) ?>" alt="Logo" style="width:100%; height:100%; object-fit:cover; border-radius:15px;">
                                        <?php else: ?>
                                            <?= strtoupper(substr($tenant['restaurant_name'], 0, 2)) ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Restaurant Info -->
                                    <div class="restaurant-name"><?= esc($tenant['restaurant_name']) ?></div>
                                    
                                    <div class="restaurant-type">
                                        <i class="bi bi-tag"></i> <?= ucfirst(esc($tenant['business_type'])) ?>
                                    </div>
                                    
                                    <?php if (!empty($tenant['business_address'])): ?>
                                        <div class="text-muted small mb-2">
                                            <i class="bi bi-geo-alt"></i> <?= esc($tenant['business_address']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Status Badge -->
                                    <div class="mt-3">
                                        <span class="restaurant-status status-<?= esc($tenant['status']) ?>">
                                            <?= ucfirst(esc($tenant['status'])) ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Access Button -->
                                    <div class="mt-3">
                                        <button class="btn btn-access w-100">
                                            <i class="bi bi-box-arrow-in-right"></i> Access System
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            <?php else: ?>
                <div class="no-restaurants">
                    <i class="bi bi-inbox"></i>
                    <h3>No Restaurants Available</h3>
                    <p class="text-muted">Please contact support to set up your restaurant.</p>
                    <a href="<?= base_url('contact') ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-envelope"></i> Contact Support
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Features Section -->
            <div class="row mt-5 pt-5">
                <div class="col-12">
                    <h3 class="text-center mb-4">System Features</h3>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="feature-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <h5>BIR Compliant</h5>
                    <p class="text-muted">Certified POS system with complete BIR reporting</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="feature-icon">
                        <i class="bi bi-phone"></i>
                    </div>
                    <h5>GCash & Maya</h5>
                    <p class="text-muted">Integrated payment gateway support</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="feature-icon">
                        <i class="bi bi-kitchen"></i>
                    </div>
                    <h5>Kitchen Display</h5>
                    <p class="text-muted">Real-time order management system</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h5>Analytics</h5>
                    <p class="text-muted">Comprehensive sales and performance reports</p>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-2">&copy; <?= date('Y') ?> <?= esc($company_name) ?>. All rights reserved.</p>
            <p class="small text-muted">
                Version <?= esc($system_version) ?> | 
                <a href="<?= base_url('about') ?>" class="text-white-50">About</a> | 
                <a href="<?= base_url('contact') ?>" class="text-white-50">Contact</a> |
                <a href="<?= base_url('auth/login') ?>" class="text-white-50">Staff Login</a>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add smooth scroll effect
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        // Add loading animation when clicking restaurant cards
        document.querySelectorAll('.restaurant-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.opacity = '0.6';
            });
        });
    </script>
</body>
</html>