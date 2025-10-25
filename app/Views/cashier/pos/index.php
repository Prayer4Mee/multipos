<?php
// ============================================================================
// POS Interface View
// app/Views/cashier/pos/index.php
// ============================================================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link href="<?= base_url('assets/css/pos-interface.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="pos-container">
    <div class="row g-0 h-100">
        <!-- Menu Panel -->
        <div class="col-lg-8 pos-menu-panel">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Menu Items</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm active" data-category="all">All</button>
                            <?php foreach ($menu_categories as $category): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-category="<?= $category['id'] ?>">
                                <?= $category['name'] ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2" id="menu-items-grid">
                        <?php foreach ($menu_items as $item): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 menu-item-card" data-category="<?= $item['category_id'] ?>">
                            <div class="card menu-item h-100" data-item-id="<?= $item['id'] ?>" 
                                 data-item-name="<?= $item['name'] ?>" 
                                 data-item-price="<?= $item['price'] ?>">
                                <div class="card-body p-3 text-center">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?= $item['image_url'] ?>" alt="<?= $item['name'] ?>" class="menu-item-image mb-2">
                                    <?php else: ?>
                                        <div class="menu-item-placeholder mb-2">
                                            <i class="fas fa-utensils fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h6 class="card-title"><?= $item['name'] ?></h6>
                                    <p class="card-text text-muted small"><?= substr($item['description'], 0, 50) ?>...</p>
                                    <div class="price-tag">₱<?= number_format($item['price'], 2) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Panel -->
        <div class="col-lg-4 pos-order-panel">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Current Order</h5>
                        <button class="btn btn-outline-danger btn-sm" onclick="clearOrder()">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                    </div>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <!-- Order Type Selection -->
                    <div class="order-type-selection mb-3">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="order_type" id="dine_in" value="dine_in" checked>
                            <label class="btn btn-outline-primary" for="dine_in">Dine In</label>
                            
                            <input type="radio" class="btn-check" name="order_type" id="takeout" value="takeout">
                            <label class="btn btn-outline-primary" for="takeout">Takeout</label>
                            
                            <input type="radio" class="btn-check" name="order_type" id="delivery" value="delivery">
                            <label class="btn btn-outline-primary" for="delivery">Delivery</label>
                        </div>
                    </div>
                    
                    <!-- Table Selection (for dine-in) -->
                    <div class="table-selection mb-3" id="table-selection">
                        <label class="form-label">Select Table:</label>
                        <select class="form-select" id="table_id">
                            <option value="">Choose table...</option>
                            <?php foreach ($tables as $table): ?>
                            <option value="<?= $table['id'] ?>" <?= $table['status'] !== 'available' ? 'disabled' : '' ?>>
                                Table <?= $table['table_number'] ?> 
                                (<?= ucfirst($table['status']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="customer-info mb-3">
                        <div class="row">
                            <div class="col-6">
                                <input type="text" class="form-control form-control-sm" id="customer_name" 
                                       placeholder="Customer Name">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control form-control-sm" id="customer_phone" 
                                       placeholder="Phone Number">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items List -->
                    <div class="order-items flex-grow-1 mb-3">
                        <div id="order-items-list">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                <p>Add items to start an order</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span id="order-subtotal">₱0.00</span>
                        </div>
                        <div class="summary-line">
                            <span>Service Charge (10%):</span>
                            <span id="order-service-charge">₱0.00</span>
                        </div>
                        <div class="summary-line">
                            <span>VAT (12%):</span>
                            <span id="order-vat">₱0.00</span>
                        </div>
                        <hr>
                        <div class="summary-line total">
                            <span><strong>Total:</strong></span>
                            <span><strong id="order-total">₱0.00</strong></span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="order-actions mt-3">
                        <button class="btn btn-success w-100 mb-2" id="submit-order-btn" onclick="submitOrder()" disabled>
                            <i class="fas fa-check"></i> Submit Order
                        </button>
                        <button class="btn btn-primary w-100" onclick="holdOrder()">
                            <i class="fas fa-pause"></i> Hold Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/pos-interface.js') ?>"></script>
<script>
    // Initialize POS system
    const POS = new TouchPointPOS({
        baseUrl: '<?= $base_url ?>',
        vatRate: <?= $settings['vat_rate'] ?? 0.12 ?>,
        serviceChargeRate: <?= $settings['service_charge_rate'] ?? 0.10 ?>
    });
    
    // Event handlers
    $(document).ready(function() {
        POS.init();
        
        // Menu item click handler
        $('.menu-item').click(function() {
            const itemData = {
                id: $(this).data('item-id'),
                name: $(this).data('item-name'),
                price: parseFloat($(this).data('item-price'))
            };
            POS.addItem(itemData);
        });
        
        // Category filter
        $('[data-category]').click(function() {
            const category = $(this).data('category');
            POS.filterByCategory(category);
            
            $('[data-category]').removeClass('active');
            $(this).addClass('active');
        });
        
        // Order type change
        $('input[name="order_type"]').change(function() {
            const orderType = $(this).val();
            POS.setOrderType(orderType);
            
            if (orderType === 'dine_in') {
                $('#table-selection').show();
            } else {
                $('#table-selection').hide();
            }
        });
    });
    
    function submitOrder() {
        POS.submitOrder();
    }
    
    function clearOrder() {
        if (confirm('Are you sure you want to clear the current order?')) {
            POS.clearOrder();
        }
    }
    
    function holdOrder() {
        POS.holdOrder();
    }
</script>
<?= $this->endSection() ?>