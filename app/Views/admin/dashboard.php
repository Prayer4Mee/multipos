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
                    <i class="fas fa-user"></i> <?= $current_user['username'] ?? 'Admin' ?>
                </span>
                <a class="nav-link" href="/admin/tenants">
                    <i class="fas fa-building"></i> Tenants
                </a>
                <a class="nav-link" href="/admin/employees">
                    <i class="fas fa-users"></i> Employees
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
                <h1 class="h3 mb-4">
                    <i class="fas fa-tachometer-alt"></i> <?= $page_title ?>
                </h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Total Tenants</h4>
                                <h2 class="mb-0"><?= $total_tenants ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Active Tenants</h4>
                                <h2 class="mb-0"><?= $active_tenants ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Suspended</h4>
                                <h2 class="mb-0"><?= $suspended_tenants ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-pause-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Total Revenue</h4>
                                <h2 class="mb-0">₱<?= number_format($total_revenue ?? 0) ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Statistics -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Total Employees</h4>
                                <h2 class="mb-0"><?= $employee_stats['total_employees'] ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Active Employees</h4>
                                <h2 class="mb-0"><?= $employee_stats['active_employees'] ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users-cog"></i> Employees by Restaurant
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($employee_stats['employees_by_tenant'] ?? [] as $tenant => $count): ?>
                            <div class="col-6 mb-2">
                                <span class="badge bg-primary me-2"><?= ucfirst($tenant) ?></span>
                                <span class="text-muted"><?= $count ?> employees</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="/admin/tenants" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-building"></i><br>
                                    Manage Tenants
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="/admin/tenant/create" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-plus"></i><br>
                                    Create New Tenant
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="/admin/settings" class="btn btn-info btn-lg w-100">
                                    <i class="fas fa-cog"></i><br>
                                    System Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
