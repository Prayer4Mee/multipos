<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="menu-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-utensils"></i> Menu Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshMenu()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Add Category
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus"></i> Add Menu Item
            </button>
        </div>
    </div>

    <!-- Debug Information -->
    <div class="alert alert-info mb-3">
        <strong>Debug Info:</strong><br>
        Tenant: <?= $tenant->tenant_slug ?? 'N/A' ?><br>
        Categories Count: <?= count($menu_categories ?? []) ?><br>
        Items Count: <?= count($menu_items ?? []) ?><br>
        <?php if (!empty($menu_categories)): ?>
            Categories: <?= implode(', ', array_column($menu_categories, 'name')) ?>
        <?php endif; ?>
    </div>

    <!-- Menu Categories Tabs -->
    <ul class="nav nav-tabs mb-4" id="menuTabs" role="tablist">
        <?php if (!empty($menu_categories)): ?>
            <?php foreach ($menu_categories as $index => $category): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                            id="<?= $category->name ?>-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#<?= $category->name ?>" 
                            type="button" role="tab">
                        <i class="fas fa-utensils"></i> <?= esc($category->name) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="no-categories-tab" data-bs-toggle="tab" data-bs-target="#no-categories" type="button" role="tab">
                    <i class="fas fa-info-circle"></i> No Categories
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Menu Items Content -->
    <div class="tab-content" id="menuTabsContent">
        <?php if (!empty($menu_categories)): ?>
            <?php foreach ($menu_categories as $index => $category): ?>
                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                     id="<?= $category->name ?>" role="tabpanel">
                    <div class="row">
                        <?php 
                        $categoryItems = array_filter($menu_items, function($item) use ($category) {
                            return $item->category_id == $category->id;
                        });
                        ?>
                        <?php if (!empty($categoryItems)): ?>
                            <?php foreach ($categoryItems as $item): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card menu-item-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title"><?= esc($item->name) ?></h6>
                                                <span class="badge bg-<?= $item->is_available ? 'success' : 'danger' ?>">
                                                    <?= $item->is_available ? 'Available' : 'Unavailable' ?>
                                                </span>
                                            </div>
                                            <p class="card-text text-muted small"><?= esc($item->description) ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h6 text-primary mb-0">₱<?= number_format($item->price, 2) ?></span>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="editMenuItem(<?= $item->id ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteMenuItem(<?= $item->id ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle"></i> No items in this category yet.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="tab-pane fade show active" id="no-categories" role="tabpanel">
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle"></i> No menu categories found. Please add a category first.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Menu Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Menu Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Code</label>
                        <input type="color" class="form-control form-control-color" name="color_code" value="#007bff">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveMenuCategory()">Save Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Item Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($menu_categories as $category): ?>
                                        <option value="<?= $category->id ?>"><?= esc($category->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cost Price</label>
                                <input type="number" class="form-control" name="cost_price" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">VAT Type</label>
                                <select class="form-select" name="vat_type">
                                    <option value="vatable">VATable</option>
                                    <option value="non_vatable">Non-VATable</option>
                                    <option value="zero_rated">Zero Rated</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Preparation Time (minutes)</label>
                                <input type="number" class="form-control" name="preparation_time" value="15">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMenuItem()">Save Item</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize tabs
    $('#menuTabs button').click(function() {
        $('#menuTabs button').removeClass('active');
        $(this).addClass('active');
    });
});

function editMenuItem(itemId) {
    // TODO: Implement edit functionality
    alert('Edit menu item: ' + itemId);
}

function deleteMenuItem(itemId) {
    if (confirm('Are you sure you want to delete this menu item?')) {
        // TODO: Implement delete functionality
        alert('Menu item deleted: ' + itemId);
    }
}

function saveMenuCategory() {
    const form = document.getElementById('addCategoryForm');
    const formData = new FormData(form);
    
    const data = {
        name: formData.get('name'),
        description: formData.get('description'),
        color_code: formData.get('color_code'),
        display_order: formData.get('display_order'),
        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
    };
    
    // Show loading
    const saveBtn = $('#addCategoryModal .btn-success');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/add-menu-category") ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addCategoryModal').modal('hide');
                alert('Menu category added successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to add category'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to add category'));
        },
        complete: function() {
            saveBtn.prop('disabled', false).html('Save Category');
        }
    });
}

function saveMenuItem() {
    const form = document.getElementById('addItemForm');
    const formData = new FormData(form);
    
    const data = {
        category_id: formData.get('category_id'),
        name: formData.get('name'),
        description: formData.get('description'),
        price: formData.get('price'),
        cost_price: formData.get('cost_price'),
        vat_type: formData.get('vat_type'),
        preparation_time: formData.get('preparation_time'),
        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
    };
    
    // Show loading
    const saveBtn = $('#addItemModal .btn-primary');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Send AJAX request
    $.ajax({
        url: '<?= base_url("restaurant/{$tenant->tenant_slug}/add-menu-item") ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addItemModal').modal('hide');
                alert('Menu item added successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to add menu item'));
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            alert('Error: ' + (response?.error || 'Failed to add menu item'));
        },
        complete: function() {
            saveBtn.prop('disabled', false).html('Save Item');
        }
    });
}

function refreshMenu() {
    location.reload();
}
</script>
<?= $this->endSection() ?>