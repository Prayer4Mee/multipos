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
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Min Stock</th>
                            <th>Unit Price</th>
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
                                    <td><?= esc($item->category) ?></td>
                                    <td id="stock-<?= $item->id ?>"><?= number_format($item->current_stock, 0) ?></td>
                                    <td><?= number_format($item->reorder_level, 0) ?></td>
                                    <td>₱<?= number_format($item->unit_cost, 2) ?></td>
                                    <td><span class="badge <?= $badgeClass ?>" id="status-<?= $item->id ?>"><?= $status ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editQuantity(<?= $item->id ?>, '<?= esc($item->item_name) ?>', <?= $item->current_stock ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item->id ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <option value="">Select Category</option>
                            <option value="meat">Meat</option>
                            <option value="vegetables">Vegetables</option>
                            <option value="grains">Grains</option>
                            <option value="beverages">Beverages</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Current Stock</label>
                                <input type="number" class="form-control" name="current_stock" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Min Stock Level</label>
                                <input type="number" class="form-control" name="min_stock" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" class="form-control" name="unit_price" step="0.01" required>
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
                    <input type="hidden" id="editItemId">
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="editItemName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" class="form-control" id="editCurrentStock" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Stock Quantity</label>
                        <input type="number" class="form-control" id="editNewStock" min="0" required>
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
                        <input type="number" class="form-control" id="adjustmentAmount" min="0" onchange="updateNewStock()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="adjustmentReason" rows="2" placeholder="Enter reason for stock adjustment..."></textarea>
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

function updateNewStock() {
    const adjustmentType = document.getElementById('adjustmentType').value;
    const adjustmentAmountDiv = document.getElementById('adjustmentAmountDiv');
    const adjustmentAmount = document.getElementById('adjustmentAmount').value;
    const newStockInput = document.getElementById('editNewStock');
    
    if (adjustmentType === 'set') {
        adjustmentAmountDiv.style.display = 'none';
        // Keep the current value in newStockInput
    } else {
        adjustmentAmountDiv.style.display = 'block';
        
        if (adjustmentAmount) {
            let newStock = currentStock;
            if (adjustmentType === 'add') {
                newStock = currentStock + parseInt(adjustmentAmount);
            } else if (adjustmentType === 'subtract') {
                newStock = Math.max(0, currentStock - parseInt(adjustmentAmount));
            }
            newStockInput.value = newStock;
        }
    }
}

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
    
    // Simulate API call (replace with actual AJAX call)
    setTimeout(() => {
        // Update the table
        document.getElementById('stock-' + currentItemId).textContent = newStock;
        
        // Update status badge
        const statusElement = document.getElementById('status-' + currentItemId);
        const minStock = getMinStockForItem(currentItemId);
        
        if (newStock == 0) {
            statusElement.textContent = 'Out of Stock';
            statusElement.className = 'badge bg-danger';
        } else if (newStock <= minStock) {
            statusElement.textContent = 'Low Stock';
            statusElement.className = 'badge bg-warning';
        } else {
            statusElement.textContent = 'In Stock';
            statusElement.className = 'badge bg-success';
        }
        
        // Update summary cards
        updateSummaryCards();
        
        // Hide modal
        $('#editQuantityModal').modal('hide');
        
        // Show success message
        alert('Stock updated successfully!');
        
        // Reset button
        updateBtn.prop('disabled', false).html('Update Stock');
    }, 1000);
}

function getMinStockForItem(itemId) {
    // Get the reorder level from the table row
    const row = document.getElementById('stock-' + itemId).closest('tr');
    if (row) {
        const reorderLevelCell = row.cells[3]; // 4th column (index 3) is reorder level
        return parseInt(reorderLevelCell.textContent) || 0;
    }
    return 0;
}

function updateSummaryCards() {
    // Count items by status
    let totalItems = 0;
    let inStock = 0;
    let lowStock = 0;
    let outOfStock = 0;
    
    // Count from table rows
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        totalItems++;
        const statusBadge = row.querySelector('.badge');
        if (statusBadge) {
            if (statusBadge.classList.contains('bg-success')) {
                inStock++;
            } else if (statusBadge.classList.contains('bg-warning')) {
                lowStock++;
            } else if (statusBadge.classList.contains('bg-danger')) {
                outOfStock++;
            }
        }
    });
    
    // Update summary cards (if they exist)
    const totalElement = document.querySelector('.card.bg-primary h2');
    const inStockElement = document.querySelector('.card.bg-success h2');
    const lowStockElement = document.querySelector('.card.bg-warning h2');
    const outOfStockElement = document.querySelector('.card.bg-danger h2');
    
    if (totalElement) totalElement.textContent = totalItems;
    if (inStockElement) inStockElement.textContent = inStock;
    if (lowStockElement) lowStockElement.textContent = lowStock;
    if (outOfStockElement) outOfStockElement.textContent = outOfStock;
}

function deleteItem(itemId) {
    if (confirm('Are you sure you want to delete this item?')) {
        // Find and remove the table row
        const stockElement = document.getElementById('stock-' + itemId);
        if (stockElement) {
            const row = stockElement.closest('tr');
            if (row) {
                row.remove();
                
                // Update summary cards
                updateSummaryCards();
                
                alert('Item deleted successfully!');
            }
        }
        // In real implementation, you would make an AJAX call to delete the item from database
    }
}

function saveItem() {
    const form = document.getElementById('addItemForm');
    const formData = new FormData(form);
    
    // Get form values
    const name = formData.get('name');
    const category = formData.get('category');
    const currentStock = formData.get('current_stock');
    const minStock = formData.get('min_stock');
    const unitPrice = formData.get('unit_price');
    
    // Validate form
    if (!name || !category || !currentStock || !minStock || !unitPrice) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Show loading
    const saveBtn = $('#addItemModal .btn-primary');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Simulate API call (replace with actual AJAX call)
    setTimeout(() => {
        // Generate new item ID (in real app, this would come from server)
        const newItemId = Date.now(); // Simple ID generation
        
        // Determine status based on stock levels
        let status = 'In Stock';
        let statusClass = 'bg-success';
        if (currentStock == 0) {
            status = 'Out of Stock';
            statusClass = 'bg-danger';
        } else if (parseInt(currentStock) <= parseInt(minStock)) {
            status = 'Low Stock';
            statusClass = 'bg-warning';
        }
        
        // Create new table row
        const newRow = `
            <tr>
                <td>${name}</td>
                <td>${category}</td>
                <td id="stock-${newItemId}">${currentStock}</td>
                <td>${minStock}</td>
                <td>₱${parseFloat(unitPrice).toFixed(2)}</td>
                <td><span class="badge ${statusClass}" id="status-${newItemId}">${status}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editQuantity(${newItemId}, '${name}', ${currentStock})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(${newItemId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        // Add row to table
        const tbody = document.querySelector('tbody');
        tbody.insertAdjacentHTML('beforeend', newRow);
        
        // Update summary cards
        updateSummaryCards();
        
        // Hide modal and reset form
        $('#addItemModal').modal('hide');
        form.reset();
        
        // Show success message
        alert('Item added successfully!');
        
        // Reset button
        saveBtn.prop('disabled', false).html('Save Item');
    }, 1000);
}

function refreshInventory() {
    location.reload();
}
</script>
<?= $this->endSection() ?>
