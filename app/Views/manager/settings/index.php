<?php
// =====================================
// app/Views/manager/settings/index.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-cog"></i> System Settings</h2>
            <p class="text-muted">Configure restaurant and system preferences</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="saveAllSettings()">
                <i class="fas fa-save"></i> Save All Changes
            </button>
        </div>
    </div>
    
    <!-- Settings Tabs -->
    <div class="row">
        <div class="col-md-3">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                <button class="nav-link active" id="v-pills-restaurant-tab" data-bs-toggle="pill" data-bs-target="#v-pills-restaurant">
                    <i class="fas fa-store"></i> Restaurant Info
                </button>
                <button class="nav-link" id="v-pills-pos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-pos">
                    <i class="fas fa-cash-register"></i> POS Settings
                </button>
                <button class="nav-link" id="v-pills-payment-tab" data-bs-toggle="pill" data-bs-target="#v-pills-payment">
                    <i class="fas fa-credit-card"></i> Payment Methods
                </button>
                <button class="nav-link" id="v-pills-tax-tab" data-bs-toggle="pill" data-bs-target="#v-pills-tax">
                    <i class="fas fa-calculator"></i> Tax & BIR Settings
                </button>
                <button class="nav-link" id="v-pills-receipt-tab" data-bs-toggle="pill" data-bs-target="#v-pills-receipt">
                    <i class="fas fa-receipt"></i> Receipt Settings
                </button>
                <button class="nav-link" id="v-pills-notifications-tab" data-bs-toggle="pill" data-bs-target="#v-pills-notifications">
                    <i class="fas fa-bell"></i> Notifications
                </button>
                <button class="nav-link" id="v-pills-backup-tab" data-bs-toggle="pill" data-bs-target="#v-pills-backup">
                    <i class="fas fa-database"></i> Backup & Security
                </button>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                
                <!-- Restaurant Information -->
                <div class="tab-pane fade show active" id="v-pills-restaurant">
                    <div class="card">
                        <div class="card-header">
                            <h5>Restaurant Information</h5>
                        </div>
                        <div class="card-body">
                            <form id="restaurantSettingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Restaurant Name *</label>
                                            <input type="text" class="form-control" name="restaurant_name" 
                                                   value="<?= $settings['restaurant_name'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Business Registration Number</label>
                                            <input type="text" class="form-control" name="business_reg_number" 
                                                   value="<?= $settings['business_reg_number'] ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label>Address *</label>
                                    <textarea class="form-control" name="address" rows="3" required><?= $settings['address'] ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Phone Number *</label>
                                            <input type="tel" class="form-control" name="phone" 
                                                   value="<?= $settings['phone'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Email Address</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?= $settings['email'] ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Operating Hours</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="time" class="form-control" name="opening_time" 
                                                           value="<?= $settings['opening_time'] ?>">
                                                    <small class="text-muted">Opening Time</small>
                                                </div>
                                                <div class="col-6">
                                                    <input type="time" class="form-control" name="closing_time" 
                                                           value="<?= $settings['closing_time'] ?>">
                                                    <small class="text-muted">Closing Time</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Cuisine Type</label>
                                            <select class="form-select" name="cuisine_type">
                                                <option value="filipino" <?= $settings['cuisine_type'] === 'filipino' ? 'selected' : '' ?>>Filipino</option>
                                                <option value="asian" <?= $settings['cuisine_type'] === 'asian' ? 'selected' : '' ?>>Asian</option>
                                                <option value="international" <?= $settings['cuisine_type'] === 'international' ? 'selected' : '' ?>>International</option>
                                                <option value="fast_food" <?= $settings['cuisine_type'] === 'fast_food' ? 'selected' : '' ?>>Fast Food</option>
                                                <option value="casual_dining" <?= $settings['cuisine_type'] === 'casual_dining' ? 'selected' : '' ?>>Casual Dining</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label>Restaurant Logo</label>
                                    <input type="file" class="form-control" name="logo" accept="image/*">
                                    <?php if (!empty($settings['logo'])): ?>
                                    <div class="mt-2">
                                        <img src="<?= base_url($settings['logo']) ?>" alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- POS Settings -->
                <div class="tab-pane fade" id="v-pills-pos">
                    <div class="card">
                        <div class="card-header">
                            <h5>POS System Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form id="posSettingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Default Order Type</label>
                                            <select class="form-select" name="default_order_type">
                                                <option value="dine_in" <?= $settings['default_order_type'] === 'dine_in' ? 'selected' : '' ?>>Dine In</option>
                                                <option value="takeout" <?= $settings['default_order_type'] === 'takeout' ? 'selected' : '' ?>>Takeout</option>
                                                <option value="delivery" <?= $settings['default_order_type'] === 'delivery' ? 'selected' : '' ?>>Delivery</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Order Number Format</label>
                                            <select class="form-select" name="order_number_format">
                                                <option value="sequential" <?= $settings['order_number_format'] === 'sequential' ? 'selected' : '' ?>>Sequential (001, 002, 003...)</option>
                                                <option value="date_sequential" <?= $settings['order_number_format'] === 'date_sequential' ? 'selected' : '' ?>>Date + Sequential (20250928-001)</option>
                                                <option value="random" <?= $settings['order_number_format'] === 'random' ? 'selected' : '' ?>>Random Number</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="auto_print_receipt" 
                                                   <?= $settings['auto_print_receipt'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Auto-print receipts</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="require_customer_info" 
                                                   <?= $settings['require_customer_info'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Require customer information</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="enable_table_service" 
                                                   <?= $settings['enable_table_service'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Enable table service</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Session Timeout (minutes)</label>
                                            <input type="number" class="form-control" name="session_timeout" 
                                                   value="<?= $settings['session_timeout'] ?>" min="5" max="480">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Max Items per Order</label>
                                            <input type="number" class="form-control" name="max_items_per_order" 
                                                   value="<?= $settings['max_items_per_order'] ?>" min="1">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="tab-pane fade" id="v-pills-payment">
                    <div class="card">
                        <div class="card-header">
                            <h5>Payment Method Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form id="paymentSettingsForm">
                                <!-- GCash Settings -->
                                <div class="payment-method-section">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="enable_gcash" 
                                                   <?= $settings['enable_gcash'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <strong>📱 Enable GCash Payments</strong>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="gcash-settings">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label>GCash Merchant ID</label>
                                                    <input type="text" class="form-control" name="gcash_merchant_id" 
                                                           value="<?= $settings['gcash_merchant_id'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label>GCash API Key</label>
                                                    <input type="password" class="form-control" name="gcash_api_key" 
                                                           value="<?= $settings['gcash_api_key'] ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>GCash Fee (%) - charged to customer</label>
                                            <input type="number" class="form-control" name="gcash_fee" 
                                                   value="<?= $settings['gcash_fee'] ?>" step="0.01" min="0" max="10">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Maya Settings -->
                                <div class="payment-method-section">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="enable_maya" 
                                                   <?= $settings['enable_maya'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <strong>📲 Enable Maya Payments</strong>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="maya-settings">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label>Maya Merchant ID</label>
                                                    <input type="text" class="form-control" name="maya_merchant_id" 
                                                           value="<?= $settings['maya_merchant_id'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label>Maya API Key</label>
                                                    <input type="password" class="form-control" name="maya_api_key" 
                                                           value="<?= $settings['maya_api_key'] ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card Payments -->
                                <div class="payment-method-section">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="enable_card_payments" 
                                                   <?= $settings['enable_card_payments'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <strong>💳 Enable Card Payments</strong>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-settings">
                                        <div class="form-group mb-3">
                                            <label>Card Processing Fee (%)</label>
                                            <input type="number" class="form-control" name="card_processing_fee" 
                                                   value="<?= $settings['card_processing_fee'] ?>" step="0.01" min="0" max="5">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Tax & BIR Settings -->
                <div class="tab-pane fade" id="v-pills-tax">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tax & BIR Configuration</h5>
                            <div class="bir-certification">
                                <span class="badge bg-success">
                                    <i class="fas fa-certificate"></i> BIR Certified - Accreditation No: 0400084175222025052341
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="taxSettingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>VAT Registration Status</label>
                                            <select class="form-select" name="vat_status">
                                                <option value="vat_registered" <?= $settings['vat_status'] === 'vat_registered' ? 'selected' : '' ?>>VAT Registered</option>
                                                <option value="non_vat" <?= $settings['vat_status'] === 'non_vat' ? 'selected' : '' ?>>Non-VAT</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>TIN (Tax Identification Number)</label>
                                            <input type="text" class="form-control" name="tin_number" 
                                                   value="<?= $settings['tin_number'] ?>" 
                                                   placeholder="000-000-000-000">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>VAT Rate (%)</label>
                                            <input type="number" class="form-control" name="vat_rate" 
                                                   value="<?= $settings['vat_rate'] ?>" step="0.01" min="0" max="20">
                                            <small class="text-muted">Standard rate in Philippines: 12%</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Service Charge (%)</label>
                                            <input type="number" class="form-control" name="service_charge_rate" 
                                                   value="<?= $settings['service_charge_rate'] ?>" step="0.01" min="0" max="20">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Senior/PWD Discount (%)</label>
                                            <input type="number" class="form-control" name="senior_discount_rate" 
                                                   value="<?= $settings['senior_discount_rate'] ?>" step="0.01" min="0" max="20">
                                            <small class="text-muted">Standard rate: 20%</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="auto_generate_bir_reports" 
                                                   <?= $settings['auto_generate_bir_reports'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Auto-generate BIR reports</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="include_service_charge_in_vat" 
                                                   <?= $settings['include_service_charge_in_vat'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Include service charge in VAT calculation</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>BIR Compliance Note:</strong> This system is certified by the Bureau of Internal Revenue (BIR) 
                                    and automatically generates compliant receipts and reports according to Philippine tax regulations.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Receipt Settings -->
                <div class="tab-pane fade" id="v-pills-receipt">
                    <div class="card">
                        <div class="card-header">
                            <h5>Receipt Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form id="receiptSettingsForm">
                                <div class="form-group mb-3">
                                    <label>Receipt Header Text</label>
                                    <textarea class="form-control" name="receipt_header" rows="3"><?= $settings['receipt_header'] ?></textarea>
                                    <small class="text-muted">Will appear at the top of all receipts</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label>Receipt Footer Text</label>
                                    <textarea class="form-control" name="receipt_footer" rows="3"><?= $settings['receipt_footer'] ?></textarea>
                                    <small class="text-muted">Will appear at the bottom of all receipts</small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="show_logo_on_receipt" 
                                                   <?= $settings['show_logo_on_receipt'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Show logo on receipt</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="show_qr_code_on_receipt" 
                                                   <?= $settings['show_qr_code_on_receipt'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Show QR code for feedback</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Paper Size</label>
                                            <select class="form-select" name="receipt_paper_size">
                                                <option value="58mm" <?= $settings['receipt_paper_size'] === '58mm' ? 'selected' : '' ?>>58mm (Small)</option>
                                                <option value="80mm" <?= $settings['receipt_paper_size'] === '80mm' ? 'selected' : '' ?>>80mm (Standard)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Print Copies</label>
                                            <select class="form-select" name="receipt_copies">
                                                <option value="1" <?= $settings['receipt_copies'] === '1' ? 'selected' : '' ?>>1 Copy</option>
                                                <option value="2" <?= $settings['receipt_copies'] === '2' ? 'selected' : '' ?>>2 Copies</option>
                                                <option value="3" <?= $settings['receipt_copies'] === '3' ? 'selected' : '' ?>>3 Copies</option>
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
    </div>
</div>

<style>
.payment-method-section {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.bir-certification {
    margin-top: 10px;
}

.nav-pills .nav-link {
    color: #495057;
    margin-bottom: 5px;
}

.nav-pills .nav-link.active {
    background-color: #667eea;
}

.nav-pills .nav-link:hover {
    background-color: rgba(102, 126, 234, 0.1);
}

.card-header h5 {
    margin: 0;
    color: #333;
}
</style>

<script>
function saveAllSettings() {
    const forms = ['restaurantSettingsForm', 'posSettingsForm', 'paymentSettingsForm', 'taxSettingsForm', 'receiptSettingsForm'];
    const allData = new FormData();
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                allData.append(key, value);
            }
        }
    });
    
    $.ajax({
        url: '<?= base_url('api/settings/save') ?>',
        method: 'POST',
        data: allData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('button[onclick="saveAllSettings()"]').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        },
        success: function(response) {
            if (response.success) {
                alert('Settings saved successfully!');
                location.reload();
            } else {
                alert('Error saving settings: ' + response.message);
            }
        },
        error: function() {
            alert('Error saving settings. Please try again.');
        },
        complete: function() {
            $('button[onclick="saveAllSettings()"]').html('<i class="fas fa-save"></i> Save All Changes');
        }
    });
}

// Toggle payment method settings based on checkbox
$('input[name="enable_gcash"]').change(function() {
    $('.gcash-settings').toggle(this.checked);
});

$('input[name="enable_maya"]').change(function() {
    $('.maya-settings').toggle(this.checked);
});

$('input[name="enable_card_payments"]').change(function() {
    $('.card-settings').toggle(this.checked);
});
</script>
<?= $this->endSection() ?>