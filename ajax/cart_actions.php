<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit();
        }
        
        // Check if product exists and is in stock
        $product = getProductById($conn, $product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit();
        }
        
        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            exit();
        }
        
        if (addToCart($conn, $user_id, $product_id, $quantity)) {
            $cart_count = getCartCount($conn, $user_id);
            echo json_encode([
                'success' => true, 
                'message' => 'Product added to cart successfully',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
        break;
        
    case 'remove':
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        
        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
            exit();
        }
        
        if (removeFromCart($conn, $cart_id)) {
            $cart_count = getCartCount($conn, $user_id);
            $cart_total = getCartTotal($conn, $user_id);
            echo json_encode([
                'success' => true, 
                'message' => 'Item removed from cart',
                'cart_count' => $cart_count,
                'cart_total' => $cart_total
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
        }
        break;
        
    case 'update':
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
            exit();
        }
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or negative
            if (removeFromCart($conn, $cart_id)) {
                $cart_count = getCartCount($conn, $user_id);
                $cart_total = getCartTotal($conn, $user_id);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Item removed from cart',
                    'cart_count' => $cart_count,
                    'cart_total' => $cart_total
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
            }
        } else {
            if (updateCartQuantity($conn, $cart_id, $quantity)) {
                $cart_count = getCartCount($conn, $user_id);
                $cart_total = getCartTotal($conn, $user_id);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cart updated successfully',
                    'cart_count' => $cart_count,
                    'cart_total' => $cart_total
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
        }
        break;
        
    case 'get_cart_info':
        $cart_count = getCartCount($conn, $user_id);
        $cart_total = getCartTotal($conn, $user_id);
        $cart_items = getCartItems($conn, $user_id);
        
        echo json_encode([
            'success' => true,
            'cart_count' => $cart_count,
            'cart_total' => $cart_total,
            'cart_items' => $cart_items
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?> 