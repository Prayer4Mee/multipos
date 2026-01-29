<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="profile-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-building"></i> Restaurant Profile</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshProfile()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Restaurant Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-store"></i> Restaurant Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Restaurant Name:</strong></div>
                        <div class="col-sm-8"><?= $settings->restaurant_name ?? $tenant->restaurant_name ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Tenant ID:</strong></div>
                        <div class="col-sm-8"><code><?= $tenant_slug ?></code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Currency:</strong></div>
                        <div class="col-sm-8"><?= $settings->currency ?? 'PHP' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Tax Rate:</strong></div>
                        <div class="col-sm-8"><?= ($settings->tax_rate ?? 0.12) * 100 ?>%</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Service Charge:</strong></div>
                        <div class="col-sm-8"><?= ($settings->service_charge_rate ?? 0.10) * 100 ?>%</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Theme Color:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge" style="background-color: <?= $settings->theme_color ?? '#667eea' ?>; color: white;">
                                <?= $tenant->theme_color ?? '#667eea' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current User Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user"></i> Current User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Name:</strong></div>
                        <div class="col-sm-8"><?= $user_info->name ?? 'N/A' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Username:</strong></div>
                        <div class="col-sm-8"><?= $user_info->username ?? 'N/A' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Email:</strong></div>
                        <div class="col-sm-8"><?= $user_info->email ?? 'N/A' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Role:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-primary"><?= ucfirst($user_info->role ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Employee ID:</strong></div>
                        <div class="col-sm-8"><?= $user_info->employee_id ?? 'N/A' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Restaurant:</strong></div>
                        <div class="col-sm-8"><?= $user_info->restaurant_name ?? 'N/A' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Restaurant Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Restaurant Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary"><?= $total_tables ?? 0 ?></h3>
                                <p class="text-muted">Total Tables</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success"><?= $menu_items ?? 0 ?></h3>
                                <p class="text-muted">Menu Items</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-warning"><?= $active_orders ?? 0 ?></h3>
                                <p class="text-muted">Active Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">₱<?= number_format($today_revenue ?? 0, 2) ?></h3>
                                <p class="text-muted">Today's Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Restaurant Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Restaurant Name</label>
                                <input type="text" class="form-control" name="restaurant_name" 
                                       value="<?= $settings->restaurant_name ?? $tenant->restaurant_name ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="PHP" <?= ($settings->currency ?? 'PHP') === 'PHP' ? 'selected' : '' ?>>PHP</option>
                                    <option value="USD" <?= ($settings->currency ?? 'PHP') === 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="EUR" <?= ($settings->currency ?? 'PHP') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control" name="tax_rate" step="0.01" min="0" max="100"
                                       value="<?= ($settings->tax_rate ?? 0.12) * 100 ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Service Charge (%)</label>
                                <input type="number" class="form-control" name="service_charge_rate" step="0.01" min="0" max="100"
                                       value="<?= ($settings->service_charge_rate ?? 0.10) * 100 ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theme Color</label>
                        <input type="color" class="form-control form-control-color" name="theme_color"
                               value="<?= $settings->theme_color ?? '#667eea' ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function refreshProfile() {
    location.reload();
}

function saveProfile() {
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    
    // Convert percentage values to decimal
    const taxRate = parseFloat(formData.get('tax_rate')) / 100;
    const serviceCharge = parseFloat(formData.get('service_charge_rate')) / 100;
    
    const data = {
        restaurant_name: formData.get('restaurant_name'),
        currency: formData.get('currency'),
        tax_rate: taxRate,
        service_charge_rate: serviceCharge,
        theme_color: formData.get('theme_color'),
        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
    };
    
    // Show loading
    const saveBtn = $('#editProfileModal .btn-primary');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/update-profile") ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editProfileModal').modal('hide');
                alert('Profile updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to update profile'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to update profile'));
        },
        complete: function() {
            saveBtn.prop('disabled', false).html('Save Changes');
        }
    });
}
</script>
<?= $this->endSection() ?>
