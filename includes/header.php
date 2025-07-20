<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-shopping-cart me-2"></i>E-Commerce Store
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Categories
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $category): ?>
                        <li><a class="dropdown-item" href="products.php?category=<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                            <span class="badge bg-danger ms-1 cart-count"><?php echo getCartCount($conn, $_SESSION['user_id']); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end cart-dropdown" style="width: 300px;">
                            <div class="cart-items">
                                <?php 
                                $cart_items = getCartItems($conn, $_SESSION['user_id']);
                                if (empty($cart_items)): 
                                ?>
                                <div class="p-3 text-center text-muted">
                                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                    <p>Your cart is empty</p>
                                </div>
                                <?php else: ?>
                                <?php foreach ($cart_items as $item): ?>
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
                                    <a href="cart.php" class="btn btn-primary btn-sm w-100">View Cart</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 