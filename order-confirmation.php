<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order = getOrderDetails($conn, $order_id, $_SESSION['user_id']);

if (!$order) {
    redirect('orders.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - E-commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Message -->
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h1 class="text-success">Order Confirmed!</h1>
                    <p class="lead">Thank you for your purchase. Your order has been successfully placed.</p>
                </div>

                <!-- Order Details Card -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt me-2"></i>
                            Order #<?php echo $order['id']; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p><strong>Order Date:</strong> <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                                <p><strong>Order Status:</strong> 
                                    <span class="badge <?php echo getStatusBadge($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                <p><strong>Total Amount:</strong> <span class="h5 text-primary">$<?php echo number_format($order['total_amount'], 2); ?></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Shipping Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            </div>
                        </div>

                        <hr>

                        <!-- Order Items -->
                        <h6>Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6>What's Next?</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                You will receive an email confirmation shortly
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-truck text-primary me-2"></i>
                                We'll notify you when your order ships
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-primary me-2"></i>
                                Estimated delivery: 3-5 business days
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone text-primary me-2"></i>
                                Contact us if you have any questions
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="orders.php" class="btn btn-primary me-2">
                        <i class="fas fa-list me-2"></i>View All Orders
                    </a>
                    <a href="products.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 