<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
$cart_total = getCartTotal($conn, $user_id);
$cart_count = getCartCount($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - E-commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
            <h3 class="text-muted">Your cart is empty</h3>
            <p class="text-muted mb-4">Looks like you haven't added any products to your cart yet.</p>
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
            </a>
        </div>
        <?php else: ?>
        
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Cart Items (<?php echo $cart_count; ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="row align-items-center mb-3 pb-3 border-bottom cart-item" data-cart-id="<?php echo $item['id']; ?>">
                            <div class="col-md-2">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted mb-0">Price: $<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Quantity</label>
                                <select class="form-select update-cart-quantity" data-cart-id="<?php echo $item['id']; ?>">
                                    <?php for ($i = 1; $i <= min(10, $item['stock']); $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($i == $item['quantity']) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total</label>
                                <p class="fw-bold mb-0">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-danger btn-sm remove-cart-item" data-cart-id="<?php echo $item['id']; ?>">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>
                            Order Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<?php echo $cart_count; ?> items):</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>$<?php echo number_format($cart_total * 0.08, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary fs-5">$<?php echo number_format($cart_total * 1.08, 2); ?></strong>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>
                        
                        <!-- Promo Code -->
                        <div class="mt-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Promo code" id="promo-code">
                                <button class="btn btn-outline-secondary" type="button" onclick="applyPromoCode()">
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Info -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-shield-alt text-success me-2"></i>Secure Checkout</h6>
                        <p class="text-muted small mb-0">
                            Your payment information is encrypted and secure. We never store your credit card details.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recently Viewed Products -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>You might also like</h3>
                <div class="row">
                    <?php 
                    $featured_products = getFeaturedProducts($conn, 4);
                    foreach ($featured_products as $product): 
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card product-card h-100">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="h5 text-primary mb-0">$<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            View Details
                                        </a>
                                        <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Promo code functionality
        function applyPromoCode() {
            const promoCode = document.getElementById('promo-code').value.trim();
            if (!promoCode) {
                alert('Please enter a promo code');
                return;
            }
            
            // Here you would validate the promo code with the server
            alert('Promo code applied successfully!');
        }

        // Update cart item total when quantity changes
        document.querySelectorAll('.update-cart-quantity').forEach(select => {
            select.addEventListener('change', function() {
                const cartItem = this.closest('.cart-item');
                const price = parseFloat(cartItem.querySelector('.text-muted').textContent.replace('Price: $', ''));
                const quantity = parseInt(this.value);
                const total = price * quantity;
                
                cartItem.querySelector('.fw-bold').textContent = `$${total.toFixed(2)}`;
            });
        });

        // Remove cart item with confirmation
        document.querySelectorAll('.remove-cart-item').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    const cartId = this.dataset.cartId;
                    removeFromCart(cartId);
                }
            });
        });
    </script>
</body>
</html> 