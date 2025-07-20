<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '' && $_GET['min_price'] != 0) ? (float)$_GET['min_price'] : null;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '' && $_GET['max_price'] != 0) ? (float)$_GET['max_price'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Determine sort SQL
$sort_sql = 'p.created_at DESC';
switch ($sort) {
    case 'oldest': $sort_sql = 'p.created_at ASC'; break;
    case 'price_low': $sort_sql = 'p.price ASC'; break;
    case 'price_high': $sort_sql = 'p.price DESC'; break;
    case 'name_asc': $sort_sql = 'p.name ASC'; break;
    case 'name_desc': $sort_sql = 'p.name DESC'; break;
    default: $sort_sql = 'p.created_at DESC';
}

function getAllProductsSorted($conn, $category_id, $search, $min_price, $max_price, $limit, $offset, $sort_sql) {
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
    if ($min_price !== null) {
        $sql .= " AND p.price >= ?";
        $params[] = $min_price;
        $types .= "d";
    }
    if ($max_price !== null) {
        $sql .= " AND p.price <= ?";
        $params[] = $max_price;
        $types .= "d";
    }
    $sql .= " ORDER BY $sort_sql LIMIT ? OFFSET ?";
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

$products = getAllProductsSorted($conn, $category_id, $search, $min_price, $max_price, $limit, $offset, $sort_sql);

if (empty($products)) {
    echo '<div class="text-center py-5"><i class="fas fa-search fa-3x text-muted mb-3"></i><h4 class="text-muted">No products found</h4><p class="text-muted">Try adjusting your search criteria or browse all products.</p></div>';
    exit;
}

foreach ($products as $product): ?>
    <div class="col-6 col-md-3 mb-3">
        <div class="card product-card h-100 text-center p-2">
            <img src="<?php 
                // Use Unsplash thumbnail if possible
                $img = htmlspecialchars($product['image']);
                if (strpos($img, 'images.unsplash.com') !== false) {
                    $img .= (strpos($img, '?') === false ? '?' : '&') . 'w=300&h=300&fit=crop';
                }
                echo $img;
            ?>" class="card-img-top mb-2" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height:120px;object-fit:contain;" loading="lazy">
            <h6 class="card-title mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
            <div class="mb-2 text-muted" style="font-size:0.95rem;"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></div>
            <div class="mb-2"><span class="text-primary fw-bold">$<?php echo number_format($product['price'], 2); ?></span></div>
            <a href="../product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View</a>
            <?php if ($product['stock'] > 0): ?>
            <button class="btn btn-primary btn-sm add-to-cart mt-1" data-product-id="<?php echo $product['id']; ?>">
                <i class="fas fa-shopping-cart"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?> 