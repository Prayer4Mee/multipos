<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .tenant-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        .tenant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .tenant-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f8f9fa;
        }
        .tenant-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .tenant-type {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .access-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .access-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .header-section {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }
        .header-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .no-tenants {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="header-section">
            <h1 class="header-title">
                <i class="fas fa-store me-3"></i>MultiPOS
            </h1>
            <p class="header-subtitle">레스토랑을 선택하여 POS 시스템에 접속하세요</p>
        </div>

        <?php if (!empty($tenants)): ?>
            <div class="row g-4">
                <?php foreach ($tenants as $tenant): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="tenant-card h-100 p-4 text-center">
                            <div class="mb-3">
                                <?php if (!empty($tenant['logo_url'])): ?>
                                    <img src="<?= $tenant['logo_url'] ?>" alt="<?= esc($tenant['restaurant_name']) ?>" class="tenant-logo">
                                <?php else: ?>
                                    <div class="tenant-logo d-flex align-items-center justify-content-center bg-primary text-white">
                                        <i class="fas fa-store fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="tenant-name"><?= esc($tenant['restaurant_name']) ?></h3>
                            <p class="tenant-type">
                                <i class="fas fa-tag me-1"></i>
                                <?= ucfirst($tenant['business_type'] ?? 'restaurant') ?>
                            </p>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <?= esc($tenant['owner_name']) ?>
                                </small>
                            </div>
                            
                            <a href="<?= base_url("restaurant/{$tenant['tenant_slug']}/dashboard") ?>" 
                               class="btn access-btn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                접속하기
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="no-tenants">
                        <i class="fas fa-store-slash fa-4x text-muted mb-4"></i>
                        <h3>등록된 레스토랑이 없습니다</h3>
                        <p class="text-muted mb-4">
                            새로운 레스토랑을 등록하려면 관리자에게 문의하세요.
                        </p>
                        <a href="<?= base_url('admin') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-cog me-2"></i>
                            관리자 페이지
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
