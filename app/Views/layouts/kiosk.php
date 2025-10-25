<?php
// ============================================================================
// Customer Kiosk Layout Template
// app/Views/layouts/kiosk.php
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Order Now' ?> - <?= $tenant['restaurant_name'] ?? 'Restaurant' ?></title>
    
    <!-- Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Kiosk Styles -->
    <style>
        :root {
            --primary-color: <?= $tenant['theme_color'] ?? '#667eea' ?>;
            --secondary-color: #764ba2;
            --accent-color: #ff6b6b;
            --success-color: #51cf66;
            --warning-color: #ffd43b;
            --danger-color: #ff6b6b;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .kiosk-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .kiosk-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .kiosk-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .restaurant-info h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .restaurant-info p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .cart-summary {
            background: rgba(255,255,255,0.2);
            border-radius: 15px;
            padding: 15px 25px;
            text-align: center;
            min-width: 200px;
        }
        
        .cart-summary .cart-count {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .cart-summary .cart-total {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        /* Main Content */
        .kiosk-main {
            flex: 1;
            padding: 30px 0;
        }
        
        /* Menu Categories */
        .category-tabs {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .category-tabs .nav-pills .nav-link {
            border-radius: 25px;
            padding: 12px 25px;
            margin: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-tabs .nav-pills .nav-link.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .category-tabs .nav-pills .nav-link:hover:not(.active) {
            background: rgba(102, 126, 234, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        /* Menu Items */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .menu-item {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .menu-item-image {
            height: 200px;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #999;
        }
        
        .menu-item-content {
            padding: 20px;
        }
        
        .menu-item-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        
        .menu-item-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .menu-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .menu-item-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .add-to-cart-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .add-to-cart-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.05);
        }
        
        /* Cart Sidebar */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 25px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1001;
            display: flex;
            flex-direction: column;
        }
        
        .cart-sidebar.open {
            right: 0;
        }
        
        .cart-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .close-cart {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .cart-items {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item-image {
            width: 60px;
            height: 60px;
            background: #f0f0f0;
            border-radius: 10px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .quantity-btn:hover {
            background: var(--secondary-color);
        }
        
        .cart-footer {
            padding: 20px;
            border-top: 1px solid #eee;
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .checkout-btn {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 15px;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .checkout-btn:hover {
            background: #40c057;
            transform: translateY(-2px);
        }
        
        /* Overlay */
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            display: none;
        }
        
        .cart-overlay.show {
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .restaurant-info h1 {
                font-size: 2rem;
            }
            
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .kiosk-header .container {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .menu-item {
            animation: slideInUp 0.5s ease forwards;
        }
        
        .menu-item:nth-child(1) { animation-delay: 0.1s; }
        .menu-item:nth-child(2) { animation-delay: 0.2s; }
        .menu-item:nth-child(3) { animation-delay: 0.3s; }
        .menu-item:nth-child(4) { animation-delay: 0.4s; }
    </style>
    
    <?= $this->renderSection('head') ?>
</head>
<body>
    <div class="kiosk-container">
        <!-- Header -->
        <header class="kiosk-header">
            <div class="container">
                <div class="restaurant-info">
                    <h1><i class="fas fa-store"></i> <?= esc($tenant['restaurant_name'] ?? 'Restaurant') ?></h1>
                    <p><?= esc($tenant['description'] ?? 'Order your favorite food') ?></p>
                </div>
                <div class="cart-summary" onclick="toggleCart()">
                    <div class="cart-count" id="cartCount">0</div>
                    <div class="cart-total">₱<span id="cartTotal">0.00</span></div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="kiosk-main">
            <div class="container">
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-cart"></i> Your Order</h3>
            <button class="close-cart" onclick="closeCart()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cart-items" id="cartItems">
            <!-- Cart items will be populated here -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span>₱<span id="cartTotalAmount">0.00</span></span>
            </div>
            <button class="checkout-btn" onclick="proceedToCheckout()">
                <i class="fas fa-credit-card"></i> Proceed to Checkout
            </button>
        </div>
    </div>

    <!-- Core JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Kiosk JavaScript -->
    <script>
        // Cart functionality
        let cart = [];
        let cartTotal = 0;
        
        function addToCart(item) {
            const existingItem = cart.find(cartItem => cartItem.id === item.id);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: 1,
                    image: item.image
                });
            }
            
            updateCartDisplay();
        }
        
        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateCartDisplay();
        }
        
        function updateQuantity(itemId, change) {
            const item = cart.find(cartItem => cartItem.id === itemId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromCart(itemId);
                } else {
                    updateCartDisplay();
                }
            }
        }
        
        function updateCartDisplay() {
            const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
            cartTotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            
            document.getElementById('cartCount').textContent = cartCount;
            document.getElementById('cartTotal').textContent = cartTotal.toFixed(2);
            document.getElementById('cartTotalAmount').textContent = cartTotal.toFixed(2);
            
            // Update cart items display
            const cartItemsContainer = document.getElementById('cartItems');
            cartItemsContainer.innerHTML = '';
            
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<p class="text-center text-muted py-4">Your cart is empty</p>';
            } else {
                cart.forEach(item => {
                    const cartItemHTML = `
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-price">₱${item.price.toFixed(2)}</div>
                            </div>
                            <div class="cart-item-controls">
                                <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                                <span>${item.quantity}</span>
                                <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                            </div>
                        </div>
                    `;
                    cartItemsContainer.innerHTML += cartItemHTML;
                });
            }
        }
        
        function toggleCart() {
            const cartSidebar = document.getElementById('cartSidebar');
            const cartOverlay = document.getElementById('cartOverlay');
            
            cartSidebar.classList.toggle('open');
            cartOverlay.classList.toggle('show');
        }
        
        function closeCart() {
            const cartSidebar = document.getElementById('cartSidebar');
            const cartOverlay = document.getElementById('cartOverlay');
            
            cartSidebar.classList.remove('open');
            cartOverlay.classList.remove('show');
        }
        
        function proceedToCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // Redirect to checkout page
            window.location.href = '<?= base_url("restaurant/{$tenant['tenant_slug']}/checkout") ?>';
        }
        
        // Initialize cart display
        document.addEventListener('DOMContentLoaded', function() {
            updateCartDisplay();
        });
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>