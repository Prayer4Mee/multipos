<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin">
                <i class="fas fa-store"></i> MultiPOS Admin
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> Admin
                </span>
                <a class="nav-link" href="/admin/tenants">
                    <i class="fas fa-building"></i> Tenants
                </a>
                <a class="nav-link" href="/admin">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="/">
                    <i class="fas fa-home"></i> Home
                </a>
                <a class="nav-link" href="/auth/logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus"></i> <?= $page_title ?>
                    </h1>
                    <a href="/admin/tenants" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Tenants
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-building"></i> Tenant Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="/admin/tenant/store" method="post">
                            <?= csrf_field() ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tenant_slug" class="form-label">Restaurant Identifier <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="tenant_slug" name="tenant_slug" 
                                           value="<?= old('tenant_slug') ?>" required>
                                    <div class="form-text">Unique identifier for the restaurant (e.g., jollibee, mcdonalds)</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="restaurant_name" class="form-label">Restaurant Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="restaurant_name" name="restaurant_name" 
                                           value="<?= old('restaurant_name') ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="business_type" class="form-label">Business Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="business_type" name="business_type" required>
                                        <option value="">Select Business Type</option>
                                        <option value="restaurant" <?= old('business_type') === 'restaurant' ? 'selected' : '' ?>>Restaurant</option>
                                        <option value="cafe" <?= old('business_type') === 'cafe' ? 'selected' : '' ?>>Cafe</option>
                                        <option value="retail" <?= old('business_type') === 'retail' ? 'selected' : '' ?>>Retail</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="subscription_plan" class="form-label">Subscription Plan</label>
                                    <select class="form-select" id="subscription_plan" name="subscription_plan">
                                        <option value="startup" <?= old('subscription_plan') === 'startup' ? 'selected' : '' ?>>Startup</option>
                                        <option value="sme" <?= old('subscription_plan') === 'sme' ? 'selected' : '' ?>>SME</option>
                                        <option value="enterprise" <?= old('subscription_plan') === 'enterprise' ? 'selected' : '' ?>>Enterprise</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3">Owner Information</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" 
                                           value="<?= old('owner_name') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="owner_email" class="form-label">Owner Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="owner_email" name="owner_email" 
                                           value="<?= old('owner_email') ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="owner_phone" class="form-label">Owner Phone</label>
                                    <input type="tel" class="form-control" id="owner_phone" name="owner_phone" 
                                           value="<?= old('owner_phone') ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="business_address" class="form-label">Business Address</label>
                                    <textarea class="form-control" id="business_address" name="business_address" rows="2"><?= old('business_address') ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="/admin/tenants" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Tenant
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>What happens when you create a tenant?</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i> A new database will be created</li>
                            <li><i class="fas fa-check text-success me-2"></i> Default tables and data will be set up</li>
                            <li><i class="fas fa-check text-success me-2"></i> Admin user will be created</li>
                            <li><i class="fas fa-check text-success me-2"></i> Restaurant will be accessible via URL</li>
                        </ul>
                        
                        <hr>
                        
                        <h6>URL Format</h6>
                        <p class="text-muted">
                            <code>http://yourdomain.com/restaurant/{tenant_slug}</code>
                        </p>
                        
                        <h6>Example</h6>
                        <p class="text-muted">
                            If tenant slug is "jollibee", the restaurant will be accessible at:<br>
                            <code>http://yourdomain.com/restaurant/jollibee</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-generate tenant slug from restaurant name
        document.getElementById('restaurant_name').addEventListener('input', function() {
            const restaurantName = this.value;
            const tenantSlug = restaurantName
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '-')
                .substring(0, 50);
            
            document.getElementById('tenant_slug').value = tenantSlug;
        });
    </script>
</body>
</html>
