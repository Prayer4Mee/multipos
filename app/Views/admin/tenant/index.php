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
                <a class="nav-link active" href="/admin/tenants">
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
                        <i class="fas fa-building"></i> <?= $page_title ?>
                    </h1>
                    <a href="/admin/tenant/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Tenant
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
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> All Tenants
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tenants)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Restaurant Name</th>
                                            <th>Owner</th>
                                            <th>Email</th>
                                            <th>Business Type</th>
                                            <th>Status</th>
                                            <th>Subscription</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tenants as $tenant): ?>
                                        <tr>
                                            <td><?= $tenant['id'] ?></td>
                                            <td>
                                                <strong><?= esc($tenant['restaurant_name']) ?></strong><br>
                                                <small class="text-muted"><?= esc($tenant['tenant_slug']) ?></small>
                                            </td>
                                            <td><?= esc($tenant['owner_name']) ?></td>
                                            <td><?= esc($tenant['owner_email']) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= ucfirst($tenant['business_type']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($tenant['status'] === 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php elseif ($tenant['status'] === 'suspended'): ?>
                                                    <span class="badge bg-warning">Suspended</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= ucfirst($tenant['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= ucfirst($tenant['subscription_plan'] ?? 'startup') ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/admin/tenant/edit/<?= $tenant['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($tenant['status'] === 'active'): ?>
                                                        <a href="/admin/tenant/suspend/<?= $tenant['id'] ?>" class="btn btn-sm btn-outline-warning" 
                                                           onclick="return confirm('Are you sure you want to suspend this tenant?')">
                                                            <i class="fas fa-pause"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="/admin/tenant/activate/<?= $tenant['id'] ?>" class="btn btn-sm btn-outline-success" 
                                                           onclick="return confirm('Are you sure you want to activate this tenant?')">
                                                            <i class="fas fa-play"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="/admin/tenant/delete/<?= $tenant['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Tenants Found</h5>
                                <p class="text-muted">Get started by creating your first tenant.</p>
                                <a href="/admin/tenant/create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create First Tenant
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
