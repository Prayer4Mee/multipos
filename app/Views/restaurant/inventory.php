<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="inventory-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-boxes"></i> Inventory Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshInventory()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
    </div>

    <!-- Inventory Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Total Items</h4>
                            <h2 class="mb-0"><?= $summary_stats['total_items'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x"></i>
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
                            <h4 class="card-title">In Stock</h4>
                            <h2 class="mb-0"><?= $summary_stats['in_stock'] ?></h2>
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
                            <h4 class="card-title">Low Stock</h4>
                            <h2 class="mb-0"><?= $summary_stats['low_stock'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Out of Stock</h4>
                            <h2 class="mb-0"><?= $summary_stats['out_of_stock'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Inventory Items
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Item Code</th>
                            <th>Category</th>
                            <th>Unit of Measure</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Unit Cost</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($inventory_items)): ?>
                            <?php foreach ($inventory_items as $item): ?>
                                <?php
                                // Determine status and badge class
                                $status = 'In Stock';
                                $badgeClass = 'bg-success';
                                if ($item->current_stock == 0) {
                                    $status = 'Out of Stock';
                                    $badgeClass = 'bg-danger';
                                } elseif ($item->current_stock <= $item->reorder_level) {
                                    $status = 'Low Stock';
                                    $badgeClass = 'bg-warning';
                                }
                                ?>
                                <tr>
                                    <td><?= esc($item->item_name) ?></td>
                                    <td><?= esc($item->item_code) ?: 'N/A' ?>
                                    <td>
                                        <span class="badge bg-info"><?= ucfirst(esc($item->category)) ?></span>
                                    </td>
                                    <td><?= esc($item->unit_of_measure) ?></td>
                                    <td id="stock-<?= $item->id ?>">
                                        <strong><?= number_format($item->current_stock, 3) ?></strong>
                                    </td>
                                    <td><?= number_format($item->reorder_level, 3) ?></td>
                                    <td>₱<?= number_format($item->unit_cost, 2) ?></td>
                                    <td><?= esc($item->storage_location) ?: 'N/A' ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>" id="status-<?= $item->id ?>">
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td>
                                         <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                onclick="editQuantity(<?= $item->id ?>, '<?= esc($item->item_name) ?>', 
                                                <?= $item->current_stock ?>)"
                                                title="Edit Stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteItem(<?= $item->id ?>)"
                                                title="Delete Item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No inventory items found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Form Field -->
<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <!-- Add this hidden field at the start -->
                    <?= csrf_field() ?>
    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Item Name *</label>
                                <input type="text" class="form-control" name="item_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Item Code</label>
                                <input type="text" class="form-control" name="item_code">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="meat">Meat</option>
                                    <option value="vegetables">Vegetables</option>
                                    <option value="grains">Grains</option>
                                    <option value="beverages">Beverages</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unit of Measure</label>
                                <input type="text" class="form-control" name="unit_of_measure" placeholder="pcs, kg, liters" value="pcs">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Current Stock *</label>
                                <input type="number" class="form-control" name="current_stock" step="0.01" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Reorder Level *</label>
                                <input type="number" class="form-control" name="reorder_level" step="0.01" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit Cost *</label>
                                <input type="number" class="form-control" name="unit_cost" step="0.01" value="0.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Storage Location</label>
                        <input type="text" class="form-control" name="storage_location" placeholder="e.g., Freezer A, Shelf B">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier ID</label>
                        <input type="number" class="form-control" name="supplier_id">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveItem()">Save Item</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Quantity Modal -->
<div class="modal fade" id="editQuantityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editQuantityForm">
                    <input type="hidden" id="editItemId" name="item_id">
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="editItemName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" class="form-control" id="editCurrentStock" step="0.01" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Stock Quantity *</label>
                        <input type="number" class="form-control" id="editNewStock" name="new_stock" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select class="form-select" id="adjustmentType" onchange="updateNewStock()">
                            <option value="set">Set to specific amount</option>
                            <option value="add">Add to current stock</option>
                            <option value="subtract">Subtract from current stock</option>
                        </select>
                    </div>
                    <div class="mb-3" id="adjustmentAmountDiv" style="display: none;">
                        <label class="form-label">Adjustment Amount</label>
                        <input type="number" class="form-control" id="adjustmentAmount" step="0.01" min="0" onchange="updateNewStock()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="adjustmentReason" name="reason" rows="2" placeholder="Enter reason for stock adjustment..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateStock()">Update Stock</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>





<?= $this->section('scripts') ?>

<script>

let currentItemId = null;
let currentStock = 0;
/**
 * Save new inventory item
 */
function saveItem() {
    const form = document.getElementById('addItemForm');
    const formData = new FormData(form);

    // Validate required fields
    const requiredFields = ['item_name', 'category', 'current_stock', 'reorder_level', 'unit_cost'];
    for (let field of requiredFields) {
        if (!formData.get(field)) {
            alert('Please fill in all required fields');
            return;
        }
    }

    // Show loading
    const saveBtn = $('#addItemModal .btn-primary');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/add-inventory-item") ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert('Inventory item added successfully!');
                $('#addItemModal').modal('hide');
                form.reset();
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to add item'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            
            // Handle array of errors
            if (typeof response.error === 'object') {
                let errorMsg = 'Validation errors:\n';
                for (let field in response.error) {
                    errorMsg += `${field}: ${response.error[field]}\n`;
                }
                alert(errorMsg);
            } else {
                alert('Error: ' + (response?.error || 'Failed to add item'));
            }
        },
        complete: function() {
            saveBtn.prop('disabled', false).html('Save Item');
        }
    });
}

/**
 * Open edit quantity modal
 */
function editQuantity(itemId, itemName, currentStockAmount) {
    currentItemId = itemId;
    currentStock = currentStockAmount;

    // Populate modal fields
    document.getElementById('editItemId').value = itemId;
    document.getElementById('editItemName').value = itemName;
    document.getElementById('editCurrentStock').value = currentStockAmount;
    document.getElementById('editNewStock').value = currentStockAmount;
    document.getElementById('adjustmentType').value = 'set';
    document.getElementById('adjustmentAmount').value = '';
    document.getElementById('adjustmentReason').value = '';

    // Reset adjustment amount div visibility
    document.getElementById('adjustmentAmountDiv').style.display = 'none';

    // Show modal
    $('#editQuantityModal').modal('show');
}

/**
 * Update new stock based on adjustment type
 */
function updateNewStock() {
    const adjustmentType = document.getElementById('adjustmentType').value;
    const adjustmentAmountDiv = document.getElementById('adjustmentAmountDiv');
    const adjustmentAmount = document.getElementById('adjustmentAmount').value;
    const newStockInput = document.getElementById('editNewStock');

    if (adjustmentType === 'set') {
        adjustmentAmountDiv.style.display = 'none';
    } else {
        adjustmentAmountDiv.style.display = 'block';

        if (adjustmentAmount) {
            let newStock = currentStock;
            if (adjustmentType === 'add') {
                newStock = parseFloat(currentStock) + parseFloat(adjustmentAmount);
            } else if (adjustmentType === 'subtract') {
                newStock = Math.max(0, parseFloat(currentStock) - parseFloat(adjustmentAmount));
            }
            newStockInput.value = newStock.toFixed(3);
        }
    }
}

/**
 * Update stock via AJAX
 */
function updateStock() {
    
    const newStock = document.getElementById('editNewStock').value;
    const reason = document.getElementById('adjustmentReason').value;

    if (!newStock || newStock < 0) {
        alert('Please enter a valid stock quantity');
        return;
    }

    // Show loading
    const updateBtn = $('#editQuantityModal .btn-primary');
    updateBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

    // Show console debug
    console.log('Updating stock - ID: ' + currentItemId + ', New Stock: ' + newStock);

    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/update-inventory-stock") ?>/' + currentItemId,
        type: 'POST',
        data: {
            inventory_id: currentItemId,
            new_stock: newStock,
            reason: reason,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update the table
                document.getElementById('stock-' + currentItemId).textContent = parseFloat(newStock).toFixed(3);

                // Hide modal
                $('#editQuantityModal').modal('hide');

                // Show success message
                alert('Stock updated successfully!');

                // Refresh to update summary
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to update stock'));
            }
        },
        error: function(xhr) {
            console.log('Error response:', xhr);
            const response = xhr.responseJSON;
            console.log('Full response:', response);
            
            // Handle validation errors
            if (typeof response.error === 'object') {
                let errorMsg = 'Validation errors:\n';
                for (let field in response.error) {
                    errorMsg += `${field}: ${response.error[field]}\n`;
                    console.log(`${field}: ${response.error[field]}`);
                }
                alert(errorMsg);
            } else {
                alert('Error: ' + (response?.error || 'Failed to update stock'));
            }
        },
        complete: function() {
            updateBtn.prop('disabled', false).html('Update Stock');
        }
    });
}

/**
 * Delete inventory item
 */
function deleteItem(itemId) {
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }

    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/delete-inventory-item/") ?>' + itemId,
        type: 'POST',
        data: {
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Item deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to delete item'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to delete item'));
        }
    });
}

/**
 * Refresh inventory page
 */
function refreshInventory() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
