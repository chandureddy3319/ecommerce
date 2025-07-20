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

// Redirect if cart is empty
if (empty($cart_items)) {
    redirect('cart.php');
}

// Get user details
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    $shipping_city = trim($_POST['shipping_city']);
    $shipping_state = trim($_POST['shipping_state']);
    $shipping_zip = trim($_POST['shipping_zip']);
    $payment_method = $_POST['payment_method'];
    
    if (empty($shipping_address) || empty($shipping_city) || empty($shipping_state) || empty($shipping_zip)) {
        $error = 'Please fill in all shipping details';
    } else {
        $full_address = $shipping_address . ', ' . $shipping_city . ', ' . $shipping_state . ' ' . $shipping_zip;
        $total_with_tax = $cart_total * 1.08; // 8% tax
        
        $order_id = createOrder($conn, $user_id, $total_with_tax, $full_address, $payment_method);
        
        if ($order_id) {
            $success = 'Order placed successfully! Your order number is #' . $order_id;
            // Redirect to order confirmation page
            redirect('order-confirmation.php?order_id=' . $order_id);
        } else {
            $error = 'Failed to place order. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Checkout</h1>

        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shipping-fast me-2"></i>
                            Shipping Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="checkout-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Shipping Address *</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required
                                          placeholder="Enter your complete shipping address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="shipping_city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="shipping_city" name="shipping_city" 
                                           value="<?php echo htmlspecialchars($user['city']); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="shipping_state" class="form-label">State *</label>
                                    <input type="text" class="form-control" id="shipping_state" name="shipping_state" 
                                           value="<?php echo htmlspecialchars($user['state']); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="shipping_zip" class="form-label">ZIP Code *</label>
                                    <input type="text" class="form-control" id="shipping_zip" name="shipping_zip" 
                                           value="<?php echo htmlspecialchars($user['zip_code']); ?>" required>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Payment Method -->
                            <h5 class="mb-3">
                                <i class="fas fa-credit-card me-2"></i>
                                Payment Method
                            </h5>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" 
                                           value="cash_on_delivery" checked>
                                    <label class="form-check-label" for="cash_on_delivery">
                                        <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" 
                                           value="bank_transfer">
                                    <label class="form-check-label" for="bank_transfer">
                                        <i class="fas fa-university me-2"></i>Bank Transfer
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" 
                                           value="paypal">
                                    <label class="form-check-label" for="paypal">
                                        <i class="fab fa-paypal me-2"></i>PayPal
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-lock me-2"></i>Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Order Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                            <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (8%):</span>
                            <span>$<?php echo number_format($cart_total * 0.08, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary fs-5">$<?php echo number_format($cart_total * 1.08, 2); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Security Info -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-shield-alt text-success me-2"></i>Secure Checkout</h6>
                        <p class="text-muted small mb-0">
                            Your order is protected by SSL encryption. We never store your payment information.
                        </p>
                    </div>
                </div>

                <!-- Return Policy -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-undo text-info me-2"></i>Return Policy</h6>
                        <p class="text-muted small mb-0">
                            30-day return policy. Free returns for unused items in original packaging.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const requiredFields = ['shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        });

        // Auto-fill shipping address from user profile
        document.getElementById('shipping_address').addEventListener('focus', function() {
            if (!this.value.trim()) {
                const userAddress = '<?php echo htmlspecialchars($user['address']); ?>';
                if (userAddress) {
                    this.value = userAddress;
                }
            }
        });
    </script>
</body>
</html> 