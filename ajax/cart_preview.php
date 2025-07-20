<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo '<div class="p-3 text-center text-muted"><i class="fas fa-shopping-cart fa-2x mb-2"></i><p>Your cart is empty</p></div>';
    exit;
}
$cart_items = getCartItems($conn, $_SESSION['user_id']);
if (empty($cart_items)) {
    echo '<div class="p-3 text-center text-muted"><i class="fas fa-shopping-cart fa-2x mb-2"></i><p>Your cart is empty</p></div>';
    exit;
}
foreach ($cart_items as $item): ?>
    <div class="dropdown-item d-flex align-items-center p-2">
        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
        <div class="flex-grow-1">
            <div class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></div>
            <div class="text-muted">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></div>
        </div>
        <button class="btn btn-sm btn-outline-danger remove-cart-item" data-cart-id="<?php echo $item['id']; ?>">
            <i class="fas fa-trash"></i>
        </button>
    </div>
<?php endforeach; ?>
<div class="dropdown-divider"></div>
<div class="p-2">
    <div class="d-flex justify-content-between mb-2">
        <strong>Total:</strong>
        <strong>$<?php echo number_format(getCartTotal($conn, $_SESSION['user_id']), 2); ?></strong>
    </div>
    <a href="../cart.php" class="btn btn-primary btn-sm w-100">View Cart</a>
</div> 