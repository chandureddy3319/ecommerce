<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get filter parameters
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
    case 'oldest':
        $sort_sql = 'p.created_at ASC';
        break;
    case 'price_low':
        $sort_sql = 'p.price ASC';
        break;
    case 'price_high':
        $sort_sql = 'p.price DESC';
        break;
    case 'name_asc':
        $sort_sql = 'p.name ASC';
        break;
    case 'name_desc':
        $sort_sql = 'p.name DESC';
        break;
    default:
        $sort_sql = 'p.created_at DESC';
}

// Get products
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

// Get total count for pagination
function getAllProductsCount($conn, $category_id, $search, $min_price, $max_price) {
    $sql = "SELECT COUNT(*) as cnt FROM products p WHERE p.status = 'active'";
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
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['cnt'] ?? 0;
}

$total_products = getAllProductsCount($conn, $category_id, $search, $min_price, $max_price);
$total_pages = ceil($total_products / $limit);

// Get categories for filter
$categories = getCategories($conn);

// Get selected category name
$selected_category = null;
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $selected_category = $cat;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - E-commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar">
                    <h5 class="mb-3">Filters</h5>
                    
                    <form method="GET" action="" id="filter-form">
                        <!-- Removed search and category filter fields -->
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label for="min_price" class="form-label">Min Price</label>
                            <input type="number" class="form-control" id="min_price" name="min_price" 
                                   value="<?php echo $min_price ?? ''; ?>" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="max_price" class="form-label">Max Price</label>
                            <input type="number" class="form-control" id="max_price" name="max_price" 
                                   value="<?php echo $max_price ?? ''; ?>" min="0" step="0.01">
                        </div>
                        <!-- Sort -->
                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo ($sort == 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                                <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Name: Z to A</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                        <a href="products.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">
                            <?php if ($selected_category): ?>
                                <?php echo htmlspecialchars($selected_category['name']); ?>
                            <?php elseif ($search): ?>
                                Search Results for "<?php echo htmlspecialchars($search); ?>"
                            <?php else: ?>
                                All Products
                            <?php endif; ?>
                        </h2>
                        <p class="text-muted mb-0">
                            Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products
                        </p>
                    </div>
                    
                    <!-- View Toggle -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="grid-view">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="list-view">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="row" id="products-container">
                    <?php if (empty($products)): ?>
                        <div class="text-center py-5 w-100">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No products found</h4>
                            <p class="text-muted">Try adjusting your search criteria or browse all products.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="card product-card h-100 text-center p-2">
                                    <img src="<?php 
                                        $img = htmlspecialchars($product['image']);
                                        if (strpos($img, 'images.unsplash.com') !== false) {
                                            $img .= (strpos($img, '?') === false ? '?' : '&') . 'w=300&h=300&fit=crop';
                                        }
                                        echo $img;
                                    ?>" class="card-img-top mb-2" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height:120px;object-fit:contain;" loading="lazy">
                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                    <div class="mb-2 text-muted" style="font-size:0.95rem;"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></div>
                                    <div class="mb-2"><span class="text-primary fw-bold">$<?php echo number_format($product['price'], 2); ?></span></div>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View</a>
                                    <?php if ($product['stock'] > 0): ?>
                                    <button class="btn btn-primary btn-sm add-to-cart mt-1" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Products pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // View toggle functionality
        document.getElementById('grid-view').addEventListener('click', function() {
            document.getElementById('products-container').className = 'row';
            this.classList.add('active');
            document.getElementById('list-view').classList.remove('active');
        });
        document.getElementById('list-view').addEventListener('click', function() {
            document.getElementById('products-container').className = 'row list-view';
            this.classList.add('active');
            document.getElementById('grid-view').classList.remove('active');
        });
        // Auto-submit form when sort changes
        document.getElementById('sort').addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    </script>
</body>
</html> 