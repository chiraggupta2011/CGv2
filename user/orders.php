<?php 
$page_title = 'My Orders';
require_once '../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php', 'Please login to view your orders.', 'warning');
}

// Get user orders
$orders = getUserOrders($_SESSION['user_id']);

// Get specific order details if requested
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
$order_details = null;
$order_items = [];

if ($order_id) {
    // Verify the order belongs to the current user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order_details = $stmt->fetch();
    
    if ($order_details) {
        $order_items = getOrderItems($order_id);
    } else {
        redirect('orders.php', 'Order not found.', 'error');
    }
}
?>

<?php if ($order_details): ?>
<!-- Order Details View -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Order #<?php echo $order_details['id']; ?></h2>
            <a href="orders.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($order_items as $item): ?>
                        <div class="d-flex align-items-center mb-3 p-3 border rounded">
                            <img src="<?php echo getBasePath(); ?>assets/images/<?php echo $item['image'] ?: 'default-shoe.jpg'; ?>" 
                                 alt="<?php echo sanitize($item['name']); ?>" 
                                 class="me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo sanitize($item['name']); ?></h6>
                                <p class="text-muted mb-1">
                                    Quantity: <?php echo $item['quantity']; ?>
                                    <?php if (!empty($item['size'])): ?>
                                        | Size: <span class="badge bg-secondary"><?php echo sanitize($item['size']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-muted mb-0">Price: <?php echo formatPrice($item['price']); ?></p>
                            </div>
                            <div class="text-end">
                                <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Date:</span>
                            <span><?php echo date('M d, Y', strtotime($order_details['created_at'])); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Status:</span>
                            <span class="badge status-<?php echo strtolower($order_details['status']); ?>">
                                <?php echo ucfirst($order_details['status']); ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items:</span>
                            <span><?php echo count($order_items); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount:</strong>
                            <strong class="text-primary"><?php echo formatPrice($order_details['total_amount']); ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Status Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($order_details['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <?php if ($order_details['status'] != 'pending'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Processing</h6>
                                    <small class="text-muted">Order is being processed</small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array($order_details['status'], ['shipped', 'delivered'])): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Shipped</h6>
                                    <small class="text-muted">Order has been shipped</small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($order_details['status'] == 'delivered'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Delivered</h6>
                                    <small class="text-muted">Order has been delivered</small>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Orders List View -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Orders</h2>
            <a href="../shop.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
            </a>
        </div>
        
        <?php if (empty($orders)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <h4>No orders yet</h4>
                <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to see your order history here.</p>
                <a href="../shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $order['id']; ?></strong>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <span class="badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <a href="orders.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding-left: 10px;
}

.status-pending {
    background: var(--warning-color);
    color: #212529;
}

.status-processing {
    background: var(--info-color);
    color: white;
}

.status-shipped {
    background: var(--accent-color);
    color: white;
}

.status-delivered {
    background: var(--success-color);
    color: white;
}

.status-cancelled {
    background: var(--danger-color);
    color: white;
}
</style>

<?php require_once '../includes/footer.php'; ?> 