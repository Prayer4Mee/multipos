<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $page_title ?></h1>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">
                <i class="fas fa-clock"></i> <?= date('M d, Y - h:i A') ?>
            </span>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user"></i> <?= $current_user->name ?? 'User' ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/auth/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-------------------------------
        Stats Cards Section
    ---------------------------------->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary me-3">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <!-- Today's Sales -->
                        <div>
                            <h6 class="card-title mb-1">Today's Sales</h6>
                            <h4 class="mb-0">₱0.00</h4>
                        </div>
                        <!-- End: Today's Sales -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success me-3">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <!-- Orders Today -->
                        <div>
                            <h6 class="card-title mb-1">Orders Today</h6>
                            <h4 class="mb-0">0</h4>
                        </div>
                        <!-- End: Orders Today -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <!-- Pending Orders -->
                        <div>
                            <h6 class="card-title mb-1">Pending Orders</h6>
                            <h4 class="mb-0">0</h4>
                        </div>
                        <!-- End: Pending Orders -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info me-3">
                            <i class="fas fa-table"></i>
                        </div>
                        <!-- Active Tables -->
                        <div>
                            <h6 class="card-title mb-1">Active Tables</h6>
                            <h4 class="mb-0">0</h4>
                        </div>
                        <!-- End: Active Tables -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <!-------------------------------
        Order Status Section
    ---------------------------------->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart"></i> Order Status
                    </h5>
                    <a href="<?= base_url("restaurant/{$tenant->tenant_slug}/orders") ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View All Orders
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-warning"><?= $order_stats['pending_orders'] ?? 0 ?></h3>
                                <p class="text-muted">Pending Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info"><?= $order_stats['preparing_orders'] ?? 0 ?></h3>
                                <p class="text-muted">Preparing</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success"><?= $order_stats['completed_orders'] ?? 0 ?></h3>
                                <p class="text-muted">Completed Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary">₱<?= number_format($order_stats['today_revenue'] ?? 0, 2) ?></h3>
                                <p class="text-muted">Today's Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock"></i> Recent Orders
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_orders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Table</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><strong>#<?= $order->order_number ?></strong></td>
                                            <td>Table <?= $order->table_number ?></td>
                                            <td><?= $order->customer_name ?: 'Walk-in' ?></td>
                                            <td><?= $order->item_count ?> items</td>
                                            <td>₱<?= number_format($order->total_amount, 2) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                switch($order->status) {
                                                    case 'pending': $statusClass = 'bg-warning'; break;
                                                    case 'preparing': $statusClass = 'bg-info'; break;
                                                    case 'ready': $statusClass = 'bg-success'; break;
                                                    case 'completed': $statusClass = 'bg-primary'; break;
                                                    case 'cancelled': $statusClass = 'bg-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= ucfirst($order->status) ?></span>
                                            </td>
                                            <td><?= date('H:i', strtotime($order->created_at)) ?></td>
                                            <td>
                                                <a href="<?= base_url("restaurant/{$tenant->tenant_slug}/orders") ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No recent orders</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Information Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users-cog"></i> Employee Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary"><?= $employee_stats['total_employees'] ?></h3>
                                <p class="text-muted">Total Employees</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success"><?= $employee_stats['active_employees'] ?></h3>
                                <p class="text-muted">Active Employees</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Employees by Role:</h6>
                            <div class="row">
                                <?php foreach ($employee_stats['employees_by_role'] as $role => $count): ?>
                                <div class="col-6 mb-2">
                                    <span class="badge bg-secondary me-2"><?= ucfirst($role) ?></span>
                                    <span class="text-muted"><?= $count ?> employees</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('restaurant/' . $tenant->tenant_slug . '/pos') ?>" class="btn btn-primary w-100">
                                <i class="fas fa-cash-register"></i><br>
                                <small>New Order</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('restaurant/' . $tenant->tenant_slug . '/menu') ?>" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i><br>
                                <small>Add Item</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('restaurant/' . $tenant->tenant_slug . '/tables') ?>" class="btn btn-info w-100">
                                <i class="fas fa-table"></i><br>
                                <small>Table View</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('restaurant/' . $tenant->tenant_slug . '/reports') ?>" class="btn btn-warning w-100">
                                <i class="fas fa-print"></i><br>
                                <small>Print Report</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-3"></i>
                        <p>No recent activity</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    .stat-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }
</style>
<?= $this->endSection() ?>