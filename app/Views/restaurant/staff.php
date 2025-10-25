<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="staff-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users"></i> Staff Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshStaff()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="fas fa-user-plus"></i> Add Staff
            </button>
        </div>
    </div>

    <!-- Staff Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Total Staff</h4>
                            <h2 class="mb-0"><?= $staff_stats['total_staff'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
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
                            <h4 class="card-title">Active Staff</h4>
                            <h2 class="mb-0"><?= $staff_stats['active_staff'] ?></h2>
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
                        <i class="fas fa-users-cog"></i> Staff by Role
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($staff_stats['by_role'] as $role => $count): ?>
                        <div class="col-6 mb-2">
                            <span class="badge bg-secondary me-2"><?= ucfirst($role) ?></span>
                            <span class="text-muted"><?= $count ?> staff</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Staff Members
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Employee ID</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($staff_members)): ?>
                            <?php foreach ($staff_members as $staff): ?>
                                <?php
                                $status = $staff->employment_status === 'active' ? 'Active' : 'Inactive';
                                $statusClass = $staff->employment_status === 'active' ? 'bg-success' : 'bg-secondary';
                                $lastLogin = $staff->last_login_at ? date('M d, Y H:i', strtotime($staff->last_login_at)) : 'Never';
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?= strtoupper(substr($staff->first_name, 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= esc($staff->first_name . ' ' . $staff->last_name) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= esc($staff->username) ?></td>
                                    <td><?= esc($staff->email) ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= ucfirst($staff->role) ?></span>
                                    </td>
                                    <td><?= esc($staff->employee_id) ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= $status ?></span></td>
                                    <td><?= $lastLogin ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editStaff(<?= $staff->id ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" onclick="viewStaff(<?= $staff->id ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteStaff(<?= $staff->id ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No staff members found.
                                </td>
                            </tr>
                        <?php endif; ?>
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
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="manager">Manager</option>
                                    <option value="cashier">Cashier</option>
                                    <option value="kitchen_staff">Kitchen Staff</option>
                                    <option value="waiter">Waiter</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-control" name="employee_id" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employment Status</label>
                                <select class="form-select" name="employment_status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStaff()">Save Staff</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Staff Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editStaffForm">
                    <input type="hidden" id="editStaffId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" id="editUsername" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-select" id="editRole" required>
                                    <option value="">Select Role</option>
                                    <option value="manager">Manager</option>
                                    <option value="cashier">Cashier</option>
                                    <option value="kitchen_staff">Kitchen Staff</option>
                                    <option value="waiter">Waiter</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="editEmployeeId" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="editPhone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employment Status</label>
                                <select class="form-select" id="editEmploymentStatus" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="editHireDate">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Salary</label>
                                <input type="number" class="form-control" id="editSalary" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="editAddress" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="editPassword">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateStaff()">Update Staff</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}
</style>

<script>
function editStaff(staffId) {
    // Find the staff member data from the table
    const row = document.querySelector(`button[onclick="editStaff(${staffId})"]`).closest('tr');
    if (!row) {
        alert('Staff member not found');
        return;
    }
    
    // Extract data from table row (correct column order)
    const nameCell = row.cells[0].querySelector('strong');
    const nameText = nameCell ? nameCell.textContent.trim() : '';
    const nameParts = nameText.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';
    const username = row.cells[1].textContent.trim();
    const email = row.cells[2].textContent.trim();
    const roleBadge = row.cells[3].querySelector('.badge');
    const role = roleBadge ? roleBadge.textContent.trim().toLowerCase() : '';
    const employeeId = row.cells[4].textContent.trim();
    const statusBadge = row.cells[5].querySelector('.badge');
    const status = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
    
    // Populate edit form
    document.getElementById('editStaffId').value = staffId;
    document.getElementById('editFirstName').value = firstName;
    document.getElementById('editLastName').value = lastName;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value = role;
    document.getElementById('editEmployeeId').value = employeeId;
    document.getElementById('editEmploymentStatus').value = status;
    
    // Clear password field
    document.getElementById('editPassword').value = '';
    
    // Show modal
    $('#editStaffModal').modal('show');
}

function viewStaff(staffId) {
    alert('View staff details: ' + staffId);
    // TODO: Implement view functionality
}

function deleteStaff(staffId) {
    if (confirm('Are you sure you want to delete this staff member?')) {
        alert('Staff deleted: ' + staffId);
        // TODO: Implement delete functionality
    }
}

function saveStaff() {
    const form = document.getElementById('addStaffForm');
    const formData = new FormData(form);
    
    // Validate form
    const requiredFields = ['first_name', 'last_name', 'username', 'email', 'role', 'employee_id', 'password'];
    for (let field of requiredFields) {
        if (!formData.get(field)) {
            alert('Please fill in all required fields');
            return;
        }
    }
    
    // Show loading
    const saveBtn = $('#addStaffModal .btn-primary');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Simulate API call (replace with actual AJAX call)
    setTimeout(() => {
        console.log('Saving staff:', Object.fromEntries(formData));
        alert('Staff member added successfully!');
        $('#addStaffModal').modal('hide');
        form.reset();
        
        // Reset button
        saveBtn.prop('disabled', false).html('Save Staff');
        
        // Refresh page to show new staff
        location.reload();
    }, 1000);
}

function updateStaff() {
    const staffId = document.getElementById('editStaffId').value;
    const firstName = document.getElementById('editFirstName').value;
    const lastName = document.getElementById('editLastName').value;
    const username = document.getElementById('editUsername').value;
    const email = document.getElementById('editEmail').value;
    const role = document.getElementById('editRole').value;
    const employeeId = document.getElementById('editEmployeeId').value;
    const phone = document.getElementById('editPhone').value;
    const employmentStatus = document.getElementById('editEmploymentStatus').value;
    const hireDate = document.getElementById('editHireDate').value;
    const salary = document.getElementById('editSalary').value;
    const address = document.getElementById('editAddress').value;
    const password = document.getElementById('editPassword').value;
    
    // Validate required fields
    if (!firstName || !lastName || !username || !email || !role || !employeeId) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Show loading
    const updateBtn = $('#editStaffModal .btn-primary');
    updateBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    
    // Prepare form data
    const formData = {
        staff_id: staffId,
        first_name: firstName,
        last_name: lastName,
        username: username,
        email: email,
        role: role,
        employee_id: employeeId,
        phone: phone,
        employment_status: employmentStatus,
        hire_date: hireDate,
        salary: salary,
        address: address,
        password: password,
        csrf_test_name: '<?= csrf_hash() ?>'
    };
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/update-staff") ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Hide modal
                $('#editStaffModal').modal('hide');
                
                // Show success message
                alert('Staff member updated successfully!');
                
                // Refresh page to show updated data
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to update staff member'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to update staff member: ' + xhr.responseText));
        },
        complete: function() {
            // Reset button
            updateBtn.prop('disabled', false).html('Update Staff');
        }
    });
}

function refreshStaff() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
