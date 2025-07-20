<?php
// User Authentication Functions
function registerUser($conn, $username, $email, $password, $first_name, $last_name, $phone = '', $address = '', $city = '', $state = '', $zip_code = '') {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone, address, city, state, zip_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $username, $email, $hashed_password, $first_name, $last_name, $phone, $address, $city, $state, $zip_code);
    
    return $stmt->execute();
}

function loginUser($conn, $username, $password) {
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

function loginAdmin($conn, $username, $password) {
    $sql = "SELECT * FROM admins WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            return $admin;
        }
    }
    return false;
}

// Product Functions
function getFeaturedProducts($conn, $limit = 8) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.featured = 1 AND p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllProducts($conn, $category_id = null, $search = null, $min_price = null, $max_price = null, $limit = 12, $offset = 0) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    
    $params = [];
    $types = "";
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    
    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }
    
    if ($min_price !== null && $min_price != 0) {
        $sql .= " AND p.price >= ?";
        $params[] = $min_price;
        $types .= "d";
    }
    
    if ($max_price !== null && $max_price != 0) {
        $sql .= " AND p.price <= ?";
        $params[] = $max_price;
        $types .= "d";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getProductById($conn, $id) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.status = 'active'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows === 1 ? $result->fetch_assoc() : false;
}

function getCategories($conn) {
    $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Cart Functions
function addToCart($conn, $user_id, $product_id, $quantity = 1) {
    // Check if product already in cart
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $cart_item = $result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
        return $stmt->execute();
    } else {
        // Add new item
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        return $stmt->execute();
    }
}

function getCartItems($conn, $user_id) {
    $sql = "SELECT c.*, p.name, p.price, p.image, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateCartQuantity($conn, $cart_id, $quantity) {
    if ($quantity <= 0) {
        return removeFromCart($conn, $cart_id);
    }
    
    $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $cart_id);
    return $stmt->execute();
}

function removeFromCart($conn, $cart_id) {
    $sql = "DELETE FROM cart WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    return $stmt->execute();
}

function getCartTotal($conn, $user_id) {
    $sql = "SELECT SUM(c.quantity * p.price) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

function getCartCount($conn, $user_id) {
    $sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] ?? 0;
}

// Order Functions
function createOrder($conn, $user_id, $total_amount, $shipping_address, $payment_method = 'cash_on_delivery') {
    $conn->begin_transaction();
    
    try {
        // Create order
        $sql = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idss", $user_id, $total_amount, $shipping_address, $payment_method);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Get cart items
        $cart_items = getCartItems($conn, $user_id);
        
        // Add order items
        foreach ($cart_items as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            // Update product stock
            $new_stock = $item['stock'] - $item['quantity'];
            $sql = "UPDATE products SET stock = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $new_stock, $item['product_id']);
            $stmt->execute();
        }
        
        // Clear cart
        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function getUserOrders($conn, $user_id) {
    $sql = "SELECT o.*, COUNT(oi.id) as item_count 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ? 
            GROUP BY o.id 
            ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getOrderDetails($conn, $order_id, $user_id = null) {
    $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
    
    if ($user_id) {
        $sql .= " AND o.user_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($user_id) {
        $stmt->bind_param("ii", $order_id, $user_id);
    } else {
        $stmt->bind_param("i", $order_id);
    }
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) return false;
    
    // Get order items
    $sql = "SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return $order;
}

function getAllOrders($conn) {
    $sql = "SELECT o.*, u.first_name, u.last_name, u.email, COUNT(oi.id) as item_count 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            GROUP BY o.id 
            ORDER BY o.created_at DESC";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateOrderStatus($conn, $order_id, $status) {
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    return $stmt->execute();
}

// Admin Functions
function addProduct($conn, $name, $description, $price, $stock, $image, $category_id, $featured = 0) {
    $sql = "INSERT INTO products (name, description, price, stock, image, category_id, featured) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $image, $category_id, $featured);
    return $stmt->execute();
}

function updateProduct($conn, $id, $name, $description, $price, $stock, $image, $category_id, $featured = 0) {
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ?, category_id = ?, featured = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiisii", $name, $description, $price, $stock, $image, $category_id, $featured, $id);
    return $stmt->execute();
}

function deleteProduct($conn, $id) {
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getAllProductsAdmin($conn) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Utility Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-warning',
        'approved' => 'bg-info',
        'shipped' => 'bg-primary',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger'
    ];
    
    return $badges[$status] ?? 'bg-secondary';
}

// DEMO: Update existing categories and products with online demo images
function updateDemoImages($conn) {
    // Update category images
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/1041/1041372.png' WHERE name = 'Electronics'");
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/892/892458.png' WHERE name = 'Clothing'");
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/1946/1946436.png' WHERE name = 'Home & Garden'");
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/3004/3004613.png' WHERE name = 'Sports'");
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/29/29302.png' WHERE name = 'Books'");
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/2921/2921822.png' WHERE name = 'Beauty'");
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/167/167707.png' WHERE name = 'Toys'");
    $conn->query("UPDATE categories SET image = 'https://cdn-icons-png.flaticon.com/512/743/743131.png' WHERE name = 'Automotive'");
    // Update product images
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9' WHERE name = 'Smartphone X1'");
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1511367461989-f85a21fda167' WHERE name = 'Wireless Headphones'");
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f' WHERE name = 'Casual T-Shirt'");
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1528701800484-905dffb7c6b4' WHERE name = 'Running Shoes'");
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1464983953574-0892a716854b' WHERE name = 'Garden Tool Set'");
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1519864600265-abb23847ef2c' WHERE name = 'Yoga Mat'");
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1512820790803-83ca734da794' WHERE name = 'Programming Book'");
    $conn->query("UPDATE products SET image = 'https://images.unsplash.com/photo-1506744038136-46273834b3fb' WHERE name = 'Skincare Set'");
}
?> 