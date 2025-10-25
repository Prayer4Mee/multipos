<?php
// =====================================
// app/Views/manager/staff/index.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-users"></i> Staff Management</h2>
            <p class="text-muted">Manage restaurant staff and their roles</p>
        </div>
        <div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="fas fa-user-plus"></i> Add New Staff
            </button>
        </div>
    </div>
    
    <!-- Staff Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <h4><?= $total_staff ?></h4>
                <p>Total Staff</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h4><?= $active_staff ?></h4>
                <p>Active Today</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h4><?= $on_leave ?></h4>
                <p>On Leave</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h4>₱<?= number_format($total_payroll, 2) ?></h4>
                <p>Monthly Payroll</p>
            </div>
        </div>
    </div>
    
    <!-- Staff List -->
    <div class="card">
        <div class="card-header">
            <h5>Staff Directory</h5>
            <div class="card-tools">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Search staff..." id="staff-search">
                    <select class="form-select" id="role-filter">
                        <option value="">All Roles</option>
                        <option value="manager">Manager</option>
                        <option value="cashier">Cashier</option>
                        <option value="waiter">Waiter</option>
                        <option value="kitchen">Kitchen Staff</option>
                        <option value="chef">Chef</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="staff-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Performance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_members as $staff): ?>
                        <tr>
                            <td>
                                <img src="<?= base_url($staff['avatar'] ?? 'assets/img/default-avatar.png') ?>" 
                                     alt="<?= $staff['full_name'] ?>" 
                                     class="staff-avatar">
                            </td>
                            <td>
                                <strong><?= $staff['full_name'] ?></strong>
                                <br>
                                <small class="text-muted">ID: <?= $staff['employee_id'] ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= ucfirst($staff['role']) ?></span>
                                <?php if ($staff['is_supervisor']): ?>
                                <br><span class="badge bg-warning">Supervisor</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fas fa-phone"></i> <?= $staff['phone'] ?><br>
                                <i class="fas fa-envelope"></i> <?= $staff['email'] ?>
                            </td>
                            <td>
                                <strong><?= $staff['shift_start'] ?> - <?= $staff['shift_end'] ?></strong>
                                <br>
                                <small class="text-muted"><?= $staff['shift_days'] ?></small>
                            </td>
                            <td>
                                <?php
                                $status_class = [
                                    'active' => 'bg-success',
                                    'on_break' => 'bg-warning',
                                    'off_duty' => 'bg-secondary',
                                    'on_leave' => 'bg-danger'
                                ];
                                ?>
                                <span class="badge <?= $status_class[$staff['status']] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $staff['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="performance-score">
                                    <div class="score"><?= $staff['performance_score'] ?>%</div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar" style="width: <?= $staff['performance_score'] ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewStaff('<?= $staff['id'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="editStaff('<?= $staff['id'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="viewSchedule('<?= $staff['id'] ?>')">
                                        <i class="fas fa-calendar"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Staff Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addStaffForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Full Name *</label>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Employee ID *</label>
                                <input type="text" class="form-control" name="employee_id" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Role *</label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="manager">Manager</option>
                                    <option value="cashier">Cashier</option>
                                    <option value="waiter">Waiter</option>
                                    <option value="kitchen">Kitchen Staff</option>
                                    <option value="chef">Chef</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Department</label>
                                <select class="form-select" name="department">
                                    <option value="front_of_house">Front of House</option>
                                    <option value="kitchen">Kitchen</option>
                                    <option value="management">Management</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Phone Number *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Email Address *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Hourly Rate (₱)</label>
                                <input type="number" class="form-control" name="hourly_rate" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Shift Start</label>
                                <input type="time" class="form-control" name="shift_start">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Shift End</label>
                                <input type="time" class="form-control" name="shift_end">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Working Days</label>
                        <div class="row">
                            <?php 
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day): 
                            ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="<?= $day ?>" id="<?= strtolower($day) ?>">
                                    <label class="form-check-label" for="<?= strtolower($day) ?>"><?= $day ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveStaff()">
                    <i class="fas fa-save"></i> Save Staff Member
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.staff-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.performance-score .score {
    font-weight: bold;
    color: #28a745;
}

.progress-sm {
    height: 5px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card h4 {
    color: #667eea;
    font-weight: bold;
    margin: 0;
}
</style>
<?= $this->endSection() ?>