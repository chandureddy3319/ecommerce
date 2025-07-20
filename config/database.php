<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    $conn->select_db(DB_NAME);
} else {
    die("Error creating database: " . $conn->error);
}

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        city VARCHAR(50),
        state VARCHAR(50),
        zip_code VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        icon VARCHAR(50) DEFAULT 'box',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image VARCHAR(255),
        category_id INT,
        featured BOOLEAN DEFAULT FALSE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'approved', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )"
];

// Execute table creation queries
foreach ($tables as $sql) {
    if ($conn->query($sql) !== TRUE) {
        die("Error creating table: " . $conn->error);
    }
}

// Insert default admin user
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_sql = "INSERT IGNORE INTO admins (username, email, password, first_name, last_name) 
              VALUES ('admin', 'admin@ecommerce.com', '$admin_password', 'Admin', 'User')";
$conn->query($admin_sql);

// Insert default categories with demo images
$categories_sql = "INSERT IGNORE INTO categories (name, description, icon, image) VALUES 
    ('Electronics', 'Latest electronic gadgets and devices', 'mobile-alt', 'https://cdn-icons-png.flaticon.com/512/1041/1041372.png'),
    ('Clothing', 'Fashion and apparel for all ages', 'tshirt', 'https://cdn-icons-png.flaticon.com/512/892/892458.png'),
    ('Home & Garden', 'Home improvement and garden supplies', 'home', 'https://cdn-icons-png.flaticon.com/512/1946/1946436.png'),
    ('Sports', 'Sports equipment and accessories', 'futbol', 'https://cdn-icons-png.flaticon.com/512/3004/3004613.png'),
    ('Books', 'Books, magazines, and educational materials', 'book', 'https://cdn-icons-png.flaticon.com/512/29/29302.png'),
    ('Beauty', 'Beauty and personal care products', 'spa', 'https://cdn-icons-png.flaticon.com/512/2921/2921822.png'),
    ('Toys', 'Toys and games for all ages', 'gamepad', 'https://cdn-icons-png.flaticon.com/512/167/167707.png'),
    ('Automotive', 'Car parts and accessories', 'car', 'https://cdn-icons-png.flaticon.com/512/743/743131.png')";
$conn->query($categories_sql);

// Insert sample products with demo images
$products_sql = "INSERT IGNORE INTO products (name, description, price, stock, image, category_id, featured) VALUES 
    ('Smartphone X1', 'Latest smartphone with advanced features and high-quality camera', 599.99, 50, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9', 1, 1),
    ('Wireless Headphones', 'Premium wireless headphones with noise cancellation', 199.99, 30, 'https://images.unsplash.com/photo-1511367461989-f85a21fda167', 1, 1),
    ('Casual T-Shirt', 'Comfortable cotton t-shirt available in multiple colors', 29.99, 100, 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f', 2, 1),
    ('Running Shoes', 'Professional running shoes with excellent comfort and support', 89.99, 75, 'https://images.unsplash.com/photo-1528701800484-905dffb7c6b4', 2, 1),
    ('Garden Tool Set', 'Complete set of essential garden tools for home gardening', 149.99, 25, 'https://images.unsplash.com/photo-1464983953574-0892a716854b', 3, 1),
    ('Yoga Mat', 'Premium yoga mat for home workouts and meditation', 39.99, 60, 'https://images.unsplash.com/photo-1519864600265-abb23847ef2c', 4, 1),
    ('Programming Book', 'Comprehensive guide to modern programming techniques', 49.99, 40, 'https://images.unsplash.com/photo-1512820790803-83ca734da794', 5, 1),
    ('Skincare Set', 'Complete skincare routine with natural ingredients', 79.99, 35, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb', 6, 1)";
$conn->query($products_sql);
?> 