<?php
// =====================================
// app/Views/manager/inventory/index.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-boxes"></i> Inventory Management</h2>
            <p class="text-muted">Track ingredients, supplies, and stock levels</p>
        </div>
        <div>
            <button class="btn btn-warning me-2" onclick="generateStockReport()">
                <i class="fas fa-file-excel"></i> Stock Report
            </button>
            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">
                <i class="fas fa-edit"></i> Stock Adjustment
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
    </div>
    
    <!-- Inventory Alerts -->
    <?php if (!empty($low_stock_alerts)): ?>
    <div class="alert alert-warning alert-dismissible fade show mb-4">
        <strong><i class="fas fa-exclamation-triangle"></i> Low Stock Alert!</strong>
        <?= count($low_stock_alerts) ?> items are running low on stock.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Inventory Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card border-primary">
                <div class="stat-icon text-primary">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $total_items ?></h4>
                    <p>Total Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-success">
                <div class="stat-icon text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $in_stock_items ?></h4>
                    <p>In Stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-warning">
                <div class="stat-icon text-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $low_stock_items ?></h4>
                    <p>Low Stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-danger">
                <div class="stat-icon text-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $out_of_stock_items ?></h4>
                    <p>Out of Stock</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inventory Table -->
    <div class="card">
        <div class="card-header">
            <h5>Inventory Items</h5>
            <div class="card-tools">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search inventory..." id="inventory-search">
                    <select class="form-select" id="category-filter">
                        <option value="">All Categories</option>
                        <option value="ingredients">Ingredients</option>
                        <option value="beverages">Beverages</option>
                        <option value="supplies">Supplies</option>
                        <option value="condiments">Condiments</option>
                    </select>
                    <select class="form-select" id="status-filter">
                        <option value="">All Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="inventory-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Min. Level</th>
                            <th>Unit Cost</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory_items as $item): ?>
                        <tr class="<?= $item['stock_status'] === 'low_stock' ? 'table-warning' : ($item['stock_status'] === 'out_of_stock' ? 'table-danger' : '') ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= base_url($item['image'] ?? 'assets/img/default-ingredient.jpg') ?>" 
                                         alt="<?= $item['name'] ?>" 
                                         class="inventory-image me-3">
                                    <div>
                                        <strong><?= $item['name'] ?></strong>
                                        <?php if ($item['brand']): ?>
                                        <br><small class="text-muted">Brand: <?= $item['brand'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($item['category']) ?></span>
                            </td>
                            <td>
                                <strong class="text-<?= $item['stock_status'] === 'in_stock' ? 'success' : ($item['stock_status'] === 'low_stock' ? 'warning' : 'danger') ?>">
                                    <?= number_format($item['current_stock'], 2) ?>
                                </strong>
                            </td>
                            <td><?= $item['unit'] ?></td>
                            <td><?= number_format($item['min_level'], 2) ?></td>
                            <td>₱<?= number_format($item['unit_cost'], 2) ?></td>
                            <td>₱<?= number_format($item['current_stock'] * $item['unit_cost'], 2) ?></td>
                            <td>
                                <?php
                                $status_badges = [
                                    'in_stock' => 'bg-success',
                                    'low_stock' => 'bg-warning',
                                    'out_of_stock' => 'bg-danger'
                                ];
                                ?>
                                <span class="badge <?= $status_badges[$item['stock_status']] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $item['stock_status'])) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M j, Y', strtotime($item['updated_at'])) ?>
                                    <br><?= date('H:i', strtotime($item['updated_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewInventoryItem('<?= $item['id'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="editInventoryItem('<?= $item['id'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="adjustStock('<?= $item['id'] ?>')">
                                        <i class="fas fa-plus-minus"></i>
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

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="stockAdjustmentForm">
                    <div class="form-group mb-3">
                        <label>Select Item *</label>
                        <select class="form-select" name="item_id" id="adjustment-item" required>
                            <option value="">Choose an item...</option>
                            <?php foreach ($inventory_items as $item): ?>
                            <option value="<?= $item['id'] ?>" data-current="<?= $item['current_stock'] ?>" data-unit="<?= $item['unit'] ?>">
                                <?= $item['name'] ?> (Current: <?= $item['current_stock'] ?> <?= $item['unit'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Current Stock</label>
                                <input type="text" class="form-control" id="current-stock-display" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Unit</label>
                                <input type="text" class="form-control" id="unit-display" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Adjustment Type *</label>
                        <select class="form-select" name="adjustment_type" required>
                            <option value="">Select type...</option>
                            <option value="addition">Addition (+)</option>
                            <option value="subtraction">Subtraction (-)</option>
                            <option value="set_exact">Set Exact Amount</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Quantity *</label>
                        <input type="number" class="form-control" name="quantity" step="0.01" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Reason *</label>
                        <select class="form-select" name="reason" required>
                            <option value="">Select reason...</option>
                            <option value="purchase">New Purchase</option>
                            <option value="waste">Waste/Spoilage</option>
                            <option value="usage">Kitchen Usage</option>
                            <option value="damage">Damage</option>
                            <option value="recount">Stock Recount</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStockAdjustment()">
                    <i class="fas fa-save"></i> Save Adjustment
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.inventory-image {
    width: 40px;
    height: 40px;
    border-radius: 5px;
    object-fit: cover;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #dee2e6;
    display: flex;
    align-items: center;
}

.stat-icon {
    font-size: 2rem;
    margin-right: 15px;
}

.stat-content h4 {
    margin: 0;
    font-weight: bold;
}

.stat-content p {
    margin: 0;
    color: #6c757d;
}

.border-primary { border-left-color: #007bff !important; }
.border-success { border-left-color: #28a745 !important; }
.border-warning { border-left-color: #ffc107 !important; }
.border-danger { border-left-color: #dc3545 !important; }
</style>

<script>
// Stock adjustment form handling
$('#adjustment-item').change(function() {
    const selected = $(this).find(':selected');
    $('#current-stock-display').val(selected.data('current') || '');
    $('#unit-display').val(selected.data('unit') || '');
});

function saveStockAdjustment() {
    const formData = new FormData($('#stockAdjustmentForm')[0]);
    
    $.ajax({
        url: '<?= base_url('api/inventory/adjust-stock') ?>',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#stockAdjustmentModal').modal('hide');
                location.reload();
                alert('Stock adjustment saved successfully');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error saving stock adjustment');
        }
    });
}

// Search and filter functionality
$('#inventory-search, #category-filter, #status-filter').on('change keyup', function() {
    filterInventoryTable();
});

function filterInventoryTable() {
    const search = $('#inventory-search').val().toLowerCase();
    const category = $('#category-filter').val();
    const status = $('#status-filter').val();
    
    $('#inventory-table tbody tr').each(function() {
        const row = $(this);
        const itemName = row.find('td:first-child').text().toLowerCase();
        const itemCategory = row.find('.badge').text().toLowerCase();
        const itemStatus = row.find('td:nth-child(8) .badge').text().toLowerCase().replace(' ', '_');
        
        let show = true;
        
        if (search && !itemName.includes(search)) show = false;
        if (category && !itemCategory.includes(category)) show = false;
        if (status && !itemStatus.includes(status)) show = false;
        
        row.toggle(show);
    });
}
</script>
<?= $this->endSection() ?>