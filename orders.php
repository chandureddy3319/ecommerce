<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$user_id = $_SESSION['user_id'];
$orders = getUserOrders($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - E-commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">My Orders</h1>

        <?php if (empty($orders)): ?>
        <!-- No Orders -->
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
            <h3 class="text-muted">No orders yet</h3>
            <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to see your orders here.</p>
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
            </a>
        </div>
        <?php else: ?>
        
        <!-- Orders List -->
        <div class="row">
            <?php foreach ($orders as $order): ?>
            <div class="col-12 mb-4">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>
                                Order #<?php echo $order['id']; ?>
                            </h5>
                            <small class="text-muted">
                                Placed on <?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge <?php echo getStatusBadge($order['status']); ?> fs-6">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                            <div class="mt-1">
                                <strong class="text-primary">$<?php echo number_format($order['total_amount'], 2); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Items:</strong> <?php echo $order['item_count']; ?> item(s)</p>
                                <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                
                                <!-- Order Status Timeline -->
                                <div class="mt-3">
                                    <h6>Order Status</h6>
                                    <div class="order-timeline">
                                        <?php
                                        $statuses = ['pending', 'approved', 'shipped', 'delivered'];
                                        $current_status_index = array_search($order['status'], $statuses);
                                        
                                        foreach ($statuses as $index => $status):
                                            $is_completed = $index <= $current_status_index;
                                            $is_current = $index == $current_status_index;
                                        ?>
                                        <div class="timeline-item <?php echo $is_completed ? 'completed' : ''; ?> <?php echo $is_current ? 'current' : ''; ?>">
                                            <div class="timeline-marker">
                                                <?php if ($is_completed): ?>
                                                <i class="fas fa-check"></i>
                                                <?php else: ?>
                                                <i class="fas fa-circle"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="timeline-content">
                                                <strong><?php echo ucfirst($status); ?></strong>
                                                <?php if ($is_current): ?>
                                                <small class="text-primary">Current</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                <br>
                                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-print me-2"></i>Print Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        .order-timeline {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }
        
        .timeline-item {
            display: flex;
            align-items: center;
            flex: 1;
            position: relative;
        }
        
        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 20px;
            right: -10px;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }
        
        .timeline-item.completed:not(:last-child)::after {
            background-color: #28a745;
        }
        
        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            z-index: 2;
            position: relative;
        }
        
        .timeline-item.completed .timeline-marker {
            background-color: #28a745;
            color: white;
        }
        
        .timeline-item.current .timeline-marker {
            background-color: #007bff;
            color: white;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-content strong {
            display: block;
            font-size: 0.9rem;
        }
        
        .timeline-content small {
            font-size: 0.8rem;
        }
    </style>
</body>
</html> 