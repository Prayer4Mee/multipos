<?php
// =====================================
// app/Views/customer/menu/index.php
// =====================================
?>
<?= $this->extend('layouts/kiosk') ?>

<?= $this->section('content') ?>
<div class="menu-container">
    <!-- Category Tabs -->
    <div class="category-tabs">
        <div class="tab-scroll">
            <button class="category-tab active" data-category="all">
                <i class="fas fa-th"></i> All Items
            </button>
            <?php foreach ($menu_categories as $category): ?>
            <button class="category-tab" data-category="<?= $category['slug'] ?>">
                <i class="<?= $category['icon'] ?>"></i> <?= $category['name'] ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Menu Items Grid -->
    <div class="menu-grid" id="menu-items-container">
        <?php foreach ($menu_items as $item): ?>
        <div class="menu-item-card" data-category="<?= $item['category_slug'] ?>" data-item-id="<?= $item['id'] ?>">
            <div class="item-image">
                <img src="<?= base_url($item['image'] ?? 'assets/img/default-food.jpg') ?>" alt="<?= $item['name'] ?>">
                <?php if ($item['is_featured']): ?>
                <div class="featured-badge">
                    <i class="fas fa-star"></i> Featured
                </div>
                <?php endif; ?>
                <?php if (!$item['available']): ?>
                <div class="unavailable-overlay">
                    <span>Sold Out</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="item-content">
                <h3 class="item-name"><?= $item['name'] ?></h3>
                <p class="item-description"><?= $item['description'] ?></p>
                
                <!-- Dietary Icons -->
                <div class="dietary-icons">
                    <?php if ($item['is_vegetarian']): ?>
                    <span class="dietary-icon vegetarian" title="Vegetarian">🌱</span>
                    <?php endif; ?>
                    <?php if ($item['is_spicy']): ?>
                    <span class="dietary-icon spicy" title="Spicy">🌶️</span>
                    <?php endif; ?>
                    <?php if ($item['is_halal']): ?>
                    <span class="dietary-icon halal" title="Halal">🥩</span>
                    <?php endif; ?>
                </div>
                
                <!-- Price and Rating -->
                <div class="item-meta">
                    <div class="price">₱<?= number_format($item['price'], 2) ?></div>
                    <div class="rating">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $item['avg_rating'] ? 'filled' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-count">(<?= $item['rating_count'] ?>)</span>
                    </div>
                </div>
                
                <!-- Add to Cart Button -->
                <button class="add-to-cart-btn" onclick="addToCart('<?= $item['id'] ?>')" <?= !$item['available'] ? 'disabled' : '' ?>>
                    <i class="fas fa-plus"></i> Add to Order
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Search & Filter -->
    <div class="search-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search for food..." id="menu-search">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn" data-filter="vegetarian">
                🌱 Vegetarian
            </button>
            <button class="filter-btn" data-filter="spicy">
                🌶️ Spicy
            </button>
            <button class="filter-btn" data-filter="halal">
                🥩 Halal
            </button>
        </div>
    </div>
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="itemModalImage" src="" alt="" class="img-fluid rounded">
                    </div>
                    <div class="col-md-6">
                        <p id="itemModalDescription"></p>
                        <div class="nutrition-info" id="itemNutrition">
                            <!-- Nutrition facts will be loaded here -->
                        </div>
                        <div class="allergen-info" id="itemAllergens">
                            <!-- Allergen information will be loaded here -->
                        </div>
                        <div class="customization-options" id="itemCustomizations">
                            <!-- Customization options will be loaded here -->
                        </div>
                        <div class="quantity-selector">
                            <label>Quantity:</label>
                            <div class="qty-input">
                                <button type="button" class="qty-btn minus">-</button>
                                <input type="number" id="itemQuantity" value="1" min="1" max="10">
                                <button type="button" class="qty-btn plus">+</button>
                            </div>
                        </div>
                        <div class="special-instructions">
                            <label>Special Instructions:</label>
                            <textarea id="specialInstructions" placeholder="Any special requests..." rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="total-price">
                    Total: <span id="modalTotalPrice">₱0.00</span>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addItemToCart()">
                    <i class="fas fa-cart-plus"></i> Add to Order
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.menu-container {
    padding-bottom: 100px; /* Space for cart footer */
}

.category-tabs {
    background: white;
    padding: 15px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    position: sticky;
    top: 0;
    z-index: 100;
}

.tab-scroll {
    display: flex;
    overflow-x: auto;
    padding: 0 20px;
    gap: 10px;
}

.category-tab {
    background: #f8f9fa;
    border: none;
    padding: 12px 20px;
    border-radius: 25px;
    white-space: nowrap;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 120px;
}

.category-tab.active {
    background: var(--primary-color);
    color: white;
}

.category-tab:hover {
    background: rgba(102, 126, 234, 0.1);
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 0 20px;
}

.menu-item-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.menu-item-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
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
    transition: transform 0.3s ease;
}

.menu-item-card:hover .item-image img {
    transform: scale(1.05);
}

.featured-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(45deg, #ffd700, #ffed4e);
    color: #333;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
}

.unavailable-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.item-content {
    padding: 20px;
}

.item-name {
    font-size: 1.3rem;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}

.item-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.dietary-icons {
    margin-bottom: 15px;
}

.dietary-icon {
    font-size: 1.2rem;
    margin-right: 8px;
    cursor: help;
}

.item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.price {
    font-size: 1.4rem;
    font-weight: bold;
    color: var(--primary-color);
}

.rating {
    text-align: right;
}

.stars {
    color: #ffc107;
    font-size: 0.9rem;
}

.stars .filled {
    color: #ffc107;
}

.rating-count {
    font-size: 0.8rem;
    color: #666;
}

.add-to-cart-btn {
    width: 100%;
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: bold;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover:not(:disabled) {
    background: #5a6fd8;
    transform: translateY(-2px);
}

.add-to-cart-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.search-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1001;
}

.search-box {
    position: relative;
    margin-bottom: 10px;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.search-box input {
    width: 250px;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e1e8ed;
    border-radius: 25px;
    font-size: 0.9rem;
    background: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.filter-buttons {
    display: flex;
    gap: 5px;
}

.filter-btn {
    background: white;
    border: 1px solid #e1e8ed;
    padding: 8px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-btn.active {
    background: var(--primary-color);
    color: white;
}

.qty-input {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}

.qty-btn {
    width: 35px;
    height: 35px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.qty-input input {
    width: 60px;
    text-align: center;
    border: 1px solid #ddd;
    padding: 8px;
    border-radius: 5px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .menu-grid {
        grid-template-columns: 1fr;
        padding: 0 15px;
    }
    
    .search-container {
        position: static;
        margin: 20px;
    }
    
    .search-box input {
        width: 100%;
    }
    
    .filter-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
}
</style>

<script>
let cart = JSON.parse(localStorage.getItem('touchpoint_cart') || '[]');
let currentItem = null;

// Category filtering
$('.category-tab').click(function() {
    $('.category-tab').removeClass('active');
    $(this).addClass('active');
    
    const category = $(this).data('category');
    filterMenuItems(category);
});

function filterMenuItems(category) {
    $('.menu-item-card').each(function() {
        if (category === 'all' || $(this).data('category') === category) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Search functionality
$('#menu-search').on('input', function() {
    const searchTerm = $(this).val().toLowerCase();
    $('.menu-item-card').each(function() {
        const itemName = $(this).find('.item-name').text().toLowerCase();
        const itemDesc = $(this).find('.item-description').text().toLowerCase();
        
        if (itemName.includes(searchTerm) || itemDesc.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Add to cart
function addToCart(itemId) {
    // Get item details and show modal
    $.get(`<?= base_url('api/menu/item/') ?>${itemId}`, function(item) {
        currentItem = item;
        showItemModal(item);
    });
}

function showItemModal(item) {
    $('#itemModalTitle').text(item.name);
    $('#itemModalImage').attr('src', item.image);
    $('#itemModalDescription').text(item.description);
    $('#itemQuantity').val(1);
    $('#specialInstructions').val('');
    
    // Load nutrition info
    if (item.nutrition) {
        let nutritionHtml = '<h6>Nutrition Facts</h6>';
        nutritionHtml += `<p>Calories: ${item.nutrition.calories || 'N/A'}</p>`;
        $('#itemNutrition').html(nutritionHtml);
    }
    
    // Load allergens
    if (item.allergens && item.allergens.length > 0) {
        let allergensHtml = '<h6>Contains:</h6>';
        item.allergens.forEach(allergen => {
            allergensHtml += `<span class="badge bg-warning me-1">${allergen}</span>`;
        });
        $('#itemAllergens').html(allergensHtml);
    }
    
    updateModalPrice();
    $('#itemDetailsModal').modal('show');
}

function updateModalPrice() {
    if (currentItem) {
        const quantity = parseInt($('#itemQuantity').val());
        const total = currentItem.price * quantity;
        $('#modalTotalPrice').text(`₱${total.toFixed(2)}`);
    }
}

$('#itemQuantity').on('input', updateModalPrice);

$('.qty-btn.plus').click(function() {
    const input = $('#itemQuantity');
    const current = parseInt(input.val());
    if (current < 10) {
        input.val(current + 1);
        updateModalPrice();
    }
});

$('.qty-btn.minus').click(function() {
    const input = $('#itemQuantity');
    const current = parseInt(input.val());
    if (current > 1) {
        input.val(current - 1);
        updateModalPrice();
    }
});

function addItemToCart() {
    if (!currentItem) return;
    
    const cartItem = {
        id: currentItem.id,
        name: currentItem.name,
        price: currentItem.price,
        quantity: parseInt($('#itemQuantity').val()),
        specialInstructions: $('#specialInstructions').val(),
        image: currentItem.image
    };
    
    // Check if item already exists in cart
    const existingIndex = cart.findIndex(item => item.id === cartItem.id);
    if (existingIndex >= 0) {
        cart[existingIndex].quantity += cartItem.quantity;
    } else {
        cart.push(cartItem);
    }
    
    localStorage.setItem('touchpoint_cart', JSON.stringify(cart));
    updateCartDisplay();
    $('#itemDetailsModal').modal('hide');
    
    // Show success message
    showToast('Item added to cart!', 'success');
}

function updateCartDisplay() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    $('#cart-total').text(`₱${total.toFixed(2)}`);
    
    // Update cart count
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    if (itemCount > 0) {
        $('.cart-footer').addClass('has-items');
    } else {
        $('.cart-footer').removeClass('has-items');
    }
}

function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast-notification ${type}">
            <i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle"></i>
            ${message}
        </div>
    `);
    
    $('body').append(toast);
    setTimeout(() => toast.addClass('show'), 100);
    setTimeout(() => {
        toast.removeClass('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize cart display
$(document).ready(function() {
    updateCartDisplay();
});
</script>

<style>
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    z-index: 9999;
    transform: translateX(400px);
    transition: transform 0.3s ease;
}

.toast-notification.show {
    transform: translateX(0);
}

.toast-notification.success {
    border-left: 4px solid #28a745;
}

.toast-notification i {
    margin-right: 10px;
    color: #28a745;
}
</style>
<?= $this->endSection() ?>