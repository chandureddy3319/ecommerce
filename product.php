<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProductById($conn, $product_id);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get related products
$related_products = getAllProducts($conn, $product['category_id'], null, null, null, 4);
$related_products = array_filter($related_products, function($p) use ($product_id) {
    return $p['id'] != $product_id;
});
$related_products = array_slice($related_products, 0, 4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - E-commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <?php if ($product['category_name']): ?>
                <li class="breadcrumb-item">
                    <a href="products.php?category=<?php echo $product['category_id']; ?>">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-6 mb-4">
                <div class="product-gallery">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         class="img-fluid rounded product-image" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         id="main-image">
                    
                    <!-- Thumbnail Gallery (if multiple images) -->
                    <div class="row mt-3">
                        <div class="col-3">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 class="img-fluid rounded thumbnail-image active" 
                                 alt="Thumbnail 1"
                                 onclick="changeImage(this.src)">
                        </div>
                        <!-- Add more thumbnails here if you have multiple product images -->
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <?php if ($product['category_name']): ?>
                <p class="text-muted mb-3">
                    <i class="fas fa-tag me-2"></i>Category: 
                    <a href="products.php?category=<?php echo $product['category_id']; ?>" class="text-decoration-none">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </p>
                <?php endif; ?>
                
                <!-- Price -->
                <div class="mb-4">
                    <span class="h2 text-primary fw-bold">$<?php echo number_format($product['price'], 2); ?></span>
                </div>
                
                <!-- Stock Status -->
                <div class="mb-4">
                    <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-check-circle me-1"></i>In Stock (<?php echo $product['stock']; ?> available)
                    </span>
                    <?php else: ?>
                    <span class="badge bg-danger fs-6">
                        <i class="fas fa-times-circle me-1"></i>Out of Stock
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Product Description -->
                <div class="mb-4">
                    <h5>Description</h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <!-- Add to Cart Form -->
                <?php if ($product['stock'] > 0): ?>
                <form class="mb-4" id="add-to-cart-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <select class="form-select" id="quantity" name="quantity">
                                <?php for ($i = 1; $i <= min(10, $product['stock']); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                        
                        <?php if (isLoggedIn()): ?>
                        <button type="button" class="btn btn-outline-primary" id="buy-now">
                            <i class="fas fa-bolt me-2"></i>Buy Now
                        </button>
                        <?php else: ?>
                        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This product is currently out of stock. Please check back later.
                </div>
                <?php endif; ?>
                
                <!-- Product Features -->
                <div class="mb-4">
                    <h5>Features</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>High quality product</li>
                        <li><i class="fas fa-check text-success me-2"></i>Fast shipping available</li>
                        <li><i class="fas fa-check text-success me-2"></i>30-day return policy</li>
                        <li><i class="fas fa-check text-success me-2"></i>Secure payment options</li>
                    </ul>
                </div>
                
                <!-- Share Product -->
                <div class="mb-4">
                    <h5>Share this product</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-outline-info btn-sm" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-success btn-sm" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="btn btn-outline-secondary btn-sm" onclick="copyLink()">
                            <i class="fas fa-link"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Reviews Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>Customer Reviews</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="text-center py-4">
                            <i class="fas fa-star fa-2x text-warning mb-2"></i>
                            <h4>No reviews yet</h4>
                            <p class="text-muted">Be the first to review this product!</p>
                            <?php if (isLoggedIn()): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                Write a Review
                            </button>
                            <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Login to Review</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3>Related Products</h3>
                <div class="row">
                    <?php foreach ($related_products as $related): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card product-card h-100">
                            <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($related['description'], 0, 80)) . '...'; ?></p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="h5 text-primary mb-0">$<?php echo number_format($related['price'], 2); ?></span>
                                        <?php if ($related['stock'] > 0): ?>
                                        <span class="badge bg-success">In Stock</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            View Details
                                        </a>
                                        <?php if ($related['stock'] > 0): ?>
                                        <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?php echo $related['id']; ?>">
                                            Add to Cart
                                        </button>
                                        <?php endif; ?>
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

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Write a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="review-form">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating">
                                <i class="fas fa-star" data-rating="1"></i>
                                <i class="fas fa-star" data-rating="2"></i>
                                <i class="fas fa-star" data-rating="3"></i>
                                <i class="fas fa-star" data-rating="4"></i>
                                <i class="fas fa-star" data-rating="5"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="review-title" class="form-label">Review Title</label>
                            <input type="text" class="form-control" id="review-title" required>
                        </div>
                        <div class="mb-3">
                            <label for="review-content" class="form-label">Review</label>
                            <textarea class="form-control" id="review-content" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitReview()">Submit Review</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Image gallery functionality
        function changeImage(src) {
            document.getElementById('main-image').src = src;
            document.querySelectorAll('.thumbnail-image').forEach(img => img.classList.remove('active'));
            event.target.classList.add('active');
        }

        // Buy now functionality
        document.getElementById('buy-now')?.addEventListener('click', function() {
            const quantity = document.getElementById('quantity').value;
            window.location.href = `checkout.php?product_id=<?php echo $product['id']; ?>&quantity=${quantity}`;
        });

        // Share functionality
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('<?php echo addslashes($product['name']); ?>');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Check out this product: <?php echo addslashes($product['name']); ?>');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
        }

        function shareOnWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Check out this product: <?php echo addslashes($product['name']); ?>');
            window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copied to clipboard!');
            });
        }

        // Rating functionality
        document.querySelectorAll('.rating i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.querySelectorAll('.rating i').forEach(s => {
                    s.classList.remove('text-warning');
                });
                for (let i = 1; i <= rating; i++) {
                    document.querySelector(`[data-rating="${i}"]`).classList.add('text-warning');
                }
            });
        });

        function submitReview() {
            const rating = document.querySelectorAll('.rating i.text-warning').length;
            const title = document.getElementById('review-title').value;
            const content = document.getElementById('review-content').value;

            if (rating === 0) {
                alert('Please select a rating');
                return;
            }

            if (!title || !content) {
                alert('Please fill in all fields');
                return;
            }

            // Here you would submit the review to the server
            alert('Review submitted successfully!');
            bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
        }
    </script>

    <style>
        .thumbnail-image {
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .thumbnail-image:hover,
        .thumbnail-image.active {
            opacity: 1;
        }
        
        .rating i {
            cursor: pointer;
            font-size: 1.5rem;
            margin-right: 0.25rem;
        }
        
        .rating i:hover {
            color: #ffc107;
        }
    </style>
</body>
</html> 