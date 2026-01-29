<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="settings-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-cog"></i> Restaurant Settings</h1>
        <button class="btn btn-primary" onclick="saveAllSettings()">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </div>

    <div class="row">
        <!-- General Settings -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> General Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form id="generalSettingsForm">
                        <div class="mb-3">
                            <label class="form-label">Restaurant Name</label>
                            <input type="text" class="form-control" name="restaurant_name" maxlength="100" value="<?= $tenant->restaurant_name ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Type</label>
                            <select class="form-select" name="business_type">
                                <option value="restaurant" <?= ($settings->business_type ?? '') === 'restaurant' ? 'selected' : '' ?>>Restaurant</option>
                                <option value="cafe" <?= ($settings->business_type ?? '') === 'cafe' ? 'selected' : '' ?>>Cafe</option>
                                <option value="fast_food" <?= ($settings->business_type ?? '') === 'fast_food' ? 'selected' : '' ?>>Fast Food</option>
                                <option value="bar" <?= ($settings->bar ?? '') === 'bar' ? 'selected' : '' ?>>Bar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select class="form-select" name="currency">
                                <option value="PHP" <?= ($settings->currency ?? '') === 'PHP' ? 'selected' : '' ?>>PHP (₱)</option>
                                <option value="USD" <?= ($settings->currency ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                <option value="EUR" <?= ($settings->currency ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select class="form-select" name="timezone">
                                <option value="Asia/Manila" <?= ($settings->timezone ?? '') === 'Asia/Manila' ? 'selected' : '' ?>>Asia/Manila</option>
                                <option value="UTC" <?= ($settings->timezone ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                <option value="America/New_York" <?= ($settings->timezone ?? '') === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tax & Pricing Settings -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> Tax & Pricing
                    </h5>
                </div>
                <div class="card-body">
                    <form id="taxSettingsForm">
                        <div class="mb-3">
                            <label class="form-label">VAT Rate (%)</label>
                            <input type="number" class="form-control" name="vat_rate" step="0.01" value="<?= $settings->vat_rate ?? '12.00' ?>">
                       </div>
                        <div class="mb-3">
                            <label class="form-label">Service Charge Rate (%)</label>
                            <input type="number" class="form-control" name="service_charge_rate" step="0.01" value="<?= $settings->service_charge_rate ?? '10.00' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Minimum Order Amount</label>
                            <input type="number" class="form-control" name="min_order_amount" step="0.01" value="<?= $settings->min_order_amount ?? '0.00' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery Fee</label>
                            <input type="number" class="form-control" name="delivery_fee" step="0.01" value="<?= $settings->delivery_fee ?? '0.00' ?>">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Business Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building"></i> Business Information
                    </h5>
                </div>
                <div class="card-body">
                    <form id="businessInfoForm">
                        <div class="mb-3">
                            <label class="form-label">Owner Name</label>
                            <input type="text" class="form-control" name="owner_name" maxlength="100" value="<?= $settings->owner_name ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Owner Email</label>
                            <input type="email" class="form-control" name="owner_email" maxlength="100" value="<?= $settings->owner_email ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Owner Phone</label>
                            <input type="tel" class="form-control" name="owner_phone" pattern="(\+63|0)[0-9\s\-]{9,}" maxlength="20"value="<?= $settings->owner_phone ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Address</label>
                            <textarea class="form-control" name="business_address" maxlength="100" rows="3"><?= $settings->business_address ?? '' ?></textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Settings -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-desktop"></i> System Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form id="systemSettingsForm">
                        <div class="mb-3">
                            <label class="form-label">Theme Color</label>
                            <input type="color" class="form-control form-control-color" name="theme_color" value="<?= $settings->theme_color ?? '#007bff' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receipt Header</label>
                            <textarea class="form-control" name="receipt_header" rows="3" placeholder="Enter receipt header text..."><?= $settings->receipt_header ?? '' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receipt Footer</label>
                            <textarea class="form-control" name="receipt_footer" rows="3" placeholder="Enter receipt footer text..."><?= $settings->receipt_footer ?? '' ?></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_print_receipt" id="autoPrintReceipt" 
                                <?= ($settings->auto_print_receipt ?? '0') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="autoPrintReceipt">
                                Auto Print Receipt
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal Information -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-contract"></i> Legal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form id="legalInfoForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">TIN Number</label>
                                    <input type="text" class="form-control" name="tin_number" pattern="123-456-789-000" maxlength="12" value="<?= $settings->tin_number ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">BIR Permit Number</label>
                                    <input type="text" class="form-control" name="bir_permit_number" maxlength="50" value="<?= $settings->bir_permit_number ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">VAT Registered</label>
                                    <select class="form-select" name="vat_registered">
                                        <option value="1" <?= ($settings->vat_registered ?? '') === '1' ? 'selected' : '' ?>>Yes</option>
                                        <option value="0" <?= ($settings->vat_registered ?? '') === '0' ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function saveAllSettings() {
    // Collect all form data from all forms
    const forms = ['generalSettingsForm', 'taxSettingsForm', 'businessInfoForm', 'systemSettingsForm', 'legalInfoForm'];
    const allData = {};
    
    forms.forEach(formId => {
        const formData = new FormData(document.getElementById(formId));
        for (let [key, value] of formData.entries()) {
            allData[key] = value;
        }
    });
    
    // Show loading state
    const saveBtn = $('.btn-primary');
    const originalHTML = saveBtn.html();
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Add CSRF token to data
    allData['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/save-settings") ?>',
        type: 'POST',
        data: allData,
        dataType: 'json',
        success: function(response) {
            saveBtn.prop('disabled', false).html(originalHTML);
            
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Failed to save settings'));
            }
        },
        error: function(xhr) {
            saveBtn.prop('disabled', false).html(originalHTML);
            console.log('Error response:', xhr);
            const response = xhr.responseJSON;
            
            if (typeof response.error === 'object') {
                let errorMsg = 'Validation errors:\n';
                for (let field in response.error) {
                    errorMsg += `${field}: ${response.error[field]}\n`;
                }
                alert(errorMsg);
            } else {
                alert('Error: ' + (response?.message || response?.error || 'Failed to save settings'));
            }
        }
    });
}
</script>
<?= $this->endSection() ?>
