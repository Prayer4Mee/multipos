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
                            <input type="text" class="form-control" name="restaurant_name" value="<?= $tenant->restaurant_name ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Type</label>
                            <select class="form-select" name="business_type">
                                <option value="restaurant" <?= ($tenant->business_type ?? '') === 'restaurant' ? 'selected' : '' ?>>Restaurant</option>
                                <option value="cafe" <?= ($tenant->business_type ?? '') === 'cafe' ? 'selected' : '' ?>>Cafe</option>
                                <option value="fast_food" <?= ($tenant->business_type ?? '') === 'fast_food' ? 'selected' : '' ?>>Fast Food</option>
                                <option value="bar" <?= ($tenant->business_type ?? '') === 'bar' ? 'selected' : '' ?>>Bar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select class="form-select" name="currency">
                                <option value="PHP" <?= ($tenant->currency ?? '') === 'PHP' ? 'selected' : '' ?>>PHP (₱)</option>
                                <option value="USD" <?= ($tenant->currency ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                <option value="EUR" <?= ($tenant->currency ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select class="form-select" name="timezone">
                                <option value="Asia/Manila" <?= ($tenant->timezone ?? '') === 'Asia/Manila' ? 'selected' : '' ?>>Asia/Manila</option>
                                <option value="UTC" <?= ($tenant->timezone ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                <option value="America/New_York" <?= ($tenant->timezone ?? '') === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
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
                            <input type="number" class="form-control" name="vat_rate" step="0.01" value="12.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Charge Rate (%)</label>
                            <input type="number" class="form-control" name="service_charge_rate" step="0.01" value="10.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Minimum Order Amount</label>
                            <input type="number" class="form-control" name="min_order_amount" step="0.01" value="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery Fee</label>
                            <input type="number" class="form-control" name="delivery_fee" step="0.01" value="0.00">
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
                            <input type="text" class="form-control" name="owner_name" value="<?= $tenant->owner_name ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Owner Email</label>
                            <input type="email" class="form-control" name="owner_email" value="<?= $tenant->owner_email ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Owner Phone</label>
                            <input type="tel" class="form-control" name="owner_phone" value="<?= $tenant->owner_phone ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Address</label>
                            <textarea class="form-control" name="business_address" rows="3"><?= $tenant->business_address ?? '' ?></textarea>
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
                            <input type="color" class="form-control form-control-color" name="theme_color" value="<?= $tenant->theme_color ?? '#007bff' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receipt Header</label>
                            <textarea class="form-control" name="receipt_header" rows="3" placeholder="Enter receipt header text..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receipt Footer</label>
                            <textarea class="form-control" name="receipt_footer" rows="3" placeholder="Enter receipt footer text..."></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_print_receipt" id="autoPrintReceipt">
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
                                    <input type="text" class="form-control" name="tin_number" value="<?= $tenant->tin_number ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">BIR Permit Number</label>
                                    <input type="text" class="form-control" name="bir_permit_number" value="<?= $tenant->bir_permit_number ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">VAT Registered</label>
                                    <select class="form-select" name="vat_registered">
                                        <option value="1" <?= ($tenant->vat_registered ?? '') === '1' ? 'selected' : '' ?>>Yes</option>
                                        <option value="0" <?= ($tenant->vat_registered ?? '') === '0' ? 'selected' : '' ?>>No</option>
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
    // Collect all form data
    const generalData = new FormData(document.getElementById('generalSettingsForm'));
    const taxData = new FormData(document.getElementById('taxSettingsForm'));
    const businessData = new FormData(document.getElementById('businessInfoForm'));
    const systemData = new FormData(document.getElementById('systemSettingsForm'));
    const legalData = new FormData(document.getElementById('legalInfoForm'));
    
    // Combine all data
    const allData = {};
    
    // Add general settings
    for (let [key, value] of generalData.entries()) {
        allData[key] = value;
    }
    
    // Add tax settings
    for (let [key, value] of taxData.entries()) {
        allData[key] = value;
    }
    
    // Add business info
    for (let [key, value] of businessData.entries()) {
        allData[key] = value;
    }
    
    // Add system settings
    for (let [key, value] of systemData.entries()) {
        allData[key] = value;
    }
    
    // Add legal info
    for (let [key, value] of legalData.entries()) {
        allData[key] = value;
    }
    
    // Add CSRF token
    allData['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
    
    console.log('Saving settings:', allData);
    
    // Show loading
    const saveBtn = $('.btn-primary');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Simulate save (replace with actual AJAX call)
    setTimeout(() => {
        alert('Settings saved successfully!');
        saveBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save All Settings');
    }, 1000);
}
</script>
<?= $this->endSection() ?>
