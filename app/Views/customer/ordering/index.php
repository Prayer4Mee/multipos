<?php
// ============================================================================
// Customer QR Ordering View
// app/Views/customer/ordering/index.php
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tenant['restaurant_name'] ?> - Digital Menu</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/customer-ordering.css') ?>" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?= $tenant['theme_color'] ?? '#4facfe' ?>;
        }
    </style>
</head>
<body class="customer-ordering">
    <!-- Header -->
    <header class="sticky-top bg-primary text-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center">
                    <?php if ($tenant['logo_url']): ?>
                        <img src="<?= $tenant['logo_url'] ?>" alt="Logo" height="40" class="me-2">
                    <?php endif; ?>
                    <div>
                        <h5 class="mb-0"><?= $tenant['restaurant_name'] ?></h5>
                        <small>Table <?= $table_number ?? 'N/A' ?></small>
                    </div>
                </div>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#cartModal">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge bg-danger" id="cart-count">0</span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Menu Categories -->
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <div class="category-tabs">
                    <div class="btn-group w-100 mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-category="all">All</button>
                        <?php foreach ($menu_categories as $category): ?>
                        <button type="button" class="btn btn-outline-primary" data-category="<?= $category['id'] ?>">
                            <?= $category['name'] ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Menu Items Grid -->
        <div class="row" id="menu-items-grid">
            <?php foreach ($menu_items as $item): ?>
            <div class="col-6 col-md-4 col-lg-3 mb-3 menu-item-container" data-category="<?= $item['category_id'] ?>">
                <div class="card menu-item-card h-100" data-item='<?= json_encode($item) ?>'>
                    <?php if ($item['image_url']): ?>
                        <img src="<?= $item['image_url'] ?>" alt="<?= $item['name'] ?>" class="card-img-top menu-item-image">
                    <?php else: ?>
                        <div class="card-img-top menu-item-placeholder">
                            <i class="fas fa-utensils fa-2x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body p-3">
                        <h6 class="card-title"><?= $item['name'] ?></h6>
                        <p class="card-text text-muted small"><?= substr($item['description'], 0, 60) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price text-primary fw-bold">₱<?= number_format($item['price'], 2) ?></span>
                            <button class="btn btn-primary btn-sm add-to-cart">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Shopping Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Your Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="cart-items">
                        <!-- Cart items will be populated here -->
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <div class="order-summary w-100 mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="cart-subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Service Charge:</span>
                            <span id="cart-service-charge">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>VAT:</span>
                            <span id="cart-vat">₱0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span id="cart-total">₱0.00</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success w-100" onclick="proceedToCheckout()">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        const customerOrder = {
            items: [],
            vatRate: <?= $settings['vat_rate'] ?? 0.12 ?>,
            serviceChargeRate: <?= $settings['service_charge_rate'] ?? 0.10 ?>,
            
            addItem: function(item) {
                const existingItem = this.items.find(i => i.id === item.id);
                if (existingItem) {
                    existingItem.quantity++;
                } else {
                    this.items.push({...item, quantity: 1});
                }
                this.updateDisplay();
            },
            
            removeItem: function(itemId) {
                this.items = this.items.filter(i => i.id !== itemId);
                this.updateDisplay();
            },
            
            updateQuantity: function(itemId, quantity) {
                const item = this.items.find(i => i.id === itemId);
                if (item) {
                    if (quantity <= 0) {
                        this.removeItem(itemId);
                    } else {
                        item.quantity = quantity;
                        this.updateDisplay();
                    }
                }
            },
            
            calculateTotals: function() {
                const subtotal = this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const serviceCharge = subtotal * this.serviceChargeRate;
                const vat = (subtotal + serviceCharge) * this.vatRate;
                const total = subtotal + serviceCharge + vat;
                
                return { subtotal, serviceCharge, vat, total };
            },
            
            updateDisplay: function() {
                const totals = this.calculateTotals();
                
                // Update cart count
                const itemCount = this.items.reduce((sum, item) => sum + item.quantity, 0);
                $('#cart-count').text(itemCount);
                
                // Update cart items
                const cartItems = $('#cart-items');
                cartItems.empty();
                
                if (this.items.length === 0) {
                    cartItems.html('<p class="text-center text-muted">Your cart is empty</p>');
                } else {
                    this.items.forEach(item => {
                        cartItems.append(this.createCartItemHTML(item));
                    });
                }
                
                // Update totals
                $('#cart-subtotal').text('₱' + totals.subtotal.toFixed(2));
                $('#cart-service-charge').text('₱' + totals.serviceCharge.toFixed(2));
                $('#cart-vat').text('₱' + totals.vat.toFixed(2));
                $('#cart-total').text('₱' + totals.total.toFixed(2));
            },
            
            createCartItemHTML: function(item) {
                return `
                    <div class="cart-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">${item.name}</h6>
                                <small class="text-muted">₱${item.price.toFixed(2)} each</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-outline-secondary btn-sm" onclick="customerOrder.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                                <span class="mx-2">${item.quantity}</span>
                                <button class="btn btn-outline-secondary btn-sm" onclick="customerOrder.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                                <button class="btn btn-outline-danger btn-sm ms-2" onclick="customerOrder.removeItem(${item.id})">×</button>
                            </div>
                        </div>
                    </div>
                `;
            }
        };
        
        // Event handlers
        $(document).ready(function() {
            // Add to cart button
            $('.add-to-cart').click(function() {
                const card = $(this).closest('.menu-item-card');
                const item = card.data('item');
                customerOrder.addItem(item);
                
                // Visual feedback
                $(this).html('<i class="fas fa-check"></i>').removeClass('btn-primary').addClass('btn-success');
                setTimeout(() => {
                    $(this).html('<i class="fas fa-plus"></i>').removeClass('btn-success').addClass('btn-primary');
                }, 1000);
            });
            
            // Category filter
            $('[data-category]').click(function() {
                const category = $(this).data('category');
                
                $('[data-category]').removeClass('active');
                $(this).addClass('active');
                
                if (category === 'all') {
                    $('.menu-item-container').show();
                } else {
                    $('.menu-item-container').hide();
                    $(`.menu-item-container[data-category="${category}"]`).show();
                }
            });
        });
        
        function proceedToCheckout() {
            if (customerOrder.items.length === 0) {
                alert('Please add items to your cart');
                return;
            }
            
            // Redirect to checkout page or submit order
            const orderData = {
                table_number: '<?= $table_number ?? '' ?>',
                items: customerOrder.items,
                totals: customerOrder.calculateTotals()
            };
            
            // Submit order via AJAX
            $.ajax({
                url: '<?= base_url("restaurant/{$tenant_slug}/customer/submit-order") ?>',
                method: 'POST',
                data: JSON.stringify(orderData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        alert('Order submitted successfully!');
                        customerOrder.items = [];
                        customerOrder.updateDisplay();
                        $('#cartModal').modal('hide');
                    } else {
                        alert('Failed to submit order: ' + response.message);
                    }
                },
                error: function() {
                    alert('Failed to submit order. Please try again.');
                }
            });
        }
    </script>
</body>
</html>
