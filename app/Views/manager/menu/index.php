<?php
// =====================================
// app/Views/manager/menu/index.php
// =====================================
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-utensils"></i> Menu Management</h2>
            <p class="text-muted">Manage your restaurant menu items and categories</p>
        </div>
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Add Category
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                <i class="fas fa-plus"></i> Add Menu Item
            </button>
        </div>
    </div>
    
    <!-- Menu Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <h4><?= $total_items ?></h4>
                <p>Total Menu Items</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h4><?= $active_items ?></h4>
                <p>Active Items</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h4><?= $categories_count ?></h4>
                <p>Categories</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h4>₱<?= number_format($avg_price, 2) ?></h4>
                <p>Average Price</p>
            </div>
        </div>
    </div>
    
    <!-- Menu Categories Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="menu-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#all-items">All Items</a>
                </li>
                <?php foreach ($categories as $category): ?>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#category-<?= $category['id'] ?>">
                        <?= $category['name'] ?> (<?= $category['item_count'] ?>)
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content">
                <!-- All Items Tab -->
                <div class="tab-pane fade show active" id="all-items">
                    <div class="row">
                        <?php foreach ($menu_items as $item): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="menu-item-card">
                                <div class="item-image">
                                    <img src="<?= base_url($item['image'] ?? 'assets/img/default-food.jpg') ?>" alt="<?= $item['name'] ?>">
                                    <div class="item-status">
                                        <span class="badge bg-<?= $item['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="item-details">
                                    <h5><?= $item['name'] ?></h5>
                                    <p class="text-muted"><?= $item['description'] ?></p>
                                    <div class="item-meta">
                                        <div class="price">₱<?= number_format($item['price'], 2) ?></div>
                                        <div class="category-badge">
                                            <span class="badge bg-secondary"><?= $item['category_name'] ?></span>
                                        </div>
                                    </div>
                                    <div class="item-stats">
                                        <small class="text-muted">
                                            <i class="fas fa-chart-line"></i> Sold: <?= $item['orders_count'] ?> times
                                            | <i class="fas fa-star"></i> Rating: <?= $item['avg_rating'] ?>/5
                                        </small>
                                    </div>
                                    <div class="item-actions mt-3">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewMenuItem('<?= $item['id'] ?>')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editMenuItem('<?= $item['id'] ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-<?= $item['is_active'] ? 'danger' : 'success' ?>" 
                                                onclick="toggleItemStatus('<?= $item['id'] ?>', '<?= $item['is_active'] ?>')">
                                            <i class="fas fa-toggle-<?= $item['is_active'] ? 'off' : 'on' ?>"></i>
                                            <?= $item['is_active'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Category-specific tabs would be generated similarly -->
            </div>
        </div>
    </div>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMenuItemForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Item Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Category *</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Price (₱) *</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Cost (₱)</label>
                                <input type="number" class="form-control" name="cost" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Preparation Time (mins)</label>
                                <input type="number" class="form-control" name="prep_time">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Item Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="text-muted">Recommended size: 800x600px</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">Active (available for ordering)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_featured">
                                <label class="form-check-label">Featured Item</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nutritional Information -->
                    <h6>Nutritional Information (Optional)</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Calories</label>
                                <input type="number" class="form-control" name="calories">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Protein (g)</label>
                                <input type="number" class="form-control" name="protein" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Carbs (g)</label>
                                <input type="number" class="form-control" name="carbs" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Fat (g)</label>
                                <input type="number" class="form-control" name="fat" step="0.1">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Allergen Information -->
                    <div class="form-group mb-3">
                        <label>Allergens</label>
                        <div class="row">
                            <?php 
                            $allergens = ['Gluten', 'Dairy', 'Nuts', 'Eggs', 'Soy', 'Fish', 'Shellfish', 'Sesame'];
                            foreach ($allergens as $allergen): 
                            ?>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allergens[]" value="<?= $allergen ?>" id="allergen-<?= strtolower($allergen) ?>">
                                    <label class="form-check-label" for="allergen-<?= strtolower($allergen) ?>"><?= $allergen ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveMenuItem()">
                    <i class="fas fa-save"></i> Save Menu Item
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.menu-item-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.menu-item-card:hover {
    transform: translateY(-5px);
}

.item-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-status {
    position: absolute;
    top: 10px;
    right: 10px;
}

.item-details {
    padding: 20px;
}

.item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px 0;
}

.price {
    font-size: 1.25rem;
    font-weight: bold;
    color: #28a745;
}

.item-actions {
    display: flex;
    gap: 5px;
}

.item-actions .btn {
    flex: 1;
}
</style>
<?= $this->endSection() ?>