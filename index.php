<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get categories for navigation
$categories = getCategories($conn);
// Remove duplicate categories by name (if any)
$unique_categories = [];
foreach ($categories as $cat) {
    if (!isset($unique_categories[$cat['name']])) {
        $unique_categories[$cat['name']] = $cat;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern E-commerce Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: #fff;
        }
        .simple-hero {
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 60px 0 30px 0;
        }
        .simple-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #222;
        }
        .simple-hero p {
            color: #555;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        .simple-hero .btn-primary {
            font-size: 1.1rem;
            padding: 0.75rem 2rem;
            border-radius: 4px;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }
        .category-card {
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            border: 1px solid #eee;
        }
        .category-card .card-title {
            color: #222;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="simple-hero">
        <h1>Welcome to Our E-commerce Store</h1>
        <p>Discover amazing products at great prices. Shop with confidence and enjoy fast delivery!</p>
        <a href="products.php" class="btn btn-primary">Shop Now</a>
    </section>

    <section class="py-4">
        <div class="container">
            <div class="section-title">Shop by Category</div>
            <div class="row justify-content-center">
                <?php foreach ($unique_categories as $category): ?>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card category-card h-100 text-center p-3">
                        <i class="fas fa-<?php echo $category['icon']; ?> fa-2x text-primary mb-2"></i>
                        <h6 class="card-title mb-2"><?php echo htmlspecialchars($category['name']); ?></h6>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-primary btn-sm">Browse</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 