<?php 
$page_title = 'Manage Orders';

// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php'; 
require_once $includes_dir . '/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect(getBasePath() . 'index.php', 'Access denied. Admin privileges required.', 'error');
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize($_POST['status']);
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        
        logAdminAction($_SESSION['user_id'], "Updated order #$order_id status to $new_status");
        redirect('manage-orders.php', 'Order status updated successfully!', 'success');
    } catch (PDOException $e) {
        redirect('manage-orders.php', 'Error updating order status.', 'error');
    }
}

// Get specific order details if requested
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
$order_details = null;
$order_items = [];

if ($order_id) {
    $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order_details = $stmt->fetch();
    
    if ($order_details) {
        $order_items = getOrderItems($order_id);
    } else {
        redirect('manage-orders.php', 'Order not found.', 'error');
    }
}

// Get all orders for listing
$stmt = $pdo->query("SELECT o.*, u.name as customer_name, u.email as customer_email 
                     FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-shopping-bag me-2"></i>
                <?php echo $order_details ? 'Order Details' : 'Manage Orders'; ?>
            </h2>
            <?php if ($order_details): ?>
            <a href="manage-orders.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($order_details): ?>
<!-- Order Details View -->
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
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Order #:</strong> <?php echo $order_details['id']; ?>
                </div>
                <div class="mb-3">
                    <strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($order_details['created_at'])); ?>
                </div>
                <div class="mb-3">
                    <strong>Status:</strong>
                    <span class="badge status-<?php echo strtolower($order_details['status']); ?>">
                        <?php echo ucfirst($order_details['status']); ?>
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Total Amount:</strong> <?php echo formatPrice($order_details['total_amount']); ?>
                </div>
                
                <hr>
                
                <h6>Customer Information</h6>
                <div class="mb-2">
                    <strong>Name:</strong> <?php echo sanitize($order_details['customer_name']); ?>
                </div>
                <div class="mb-2">
                    <strong>Email:</strong> <?php echo sanitize($order_details['customer_email']); ?>
                </div>
                <div class="mb-2">
                    <strong>Phone:</strong> <?php echo sanitize($order_details['customer_phone']); ?>
                </div>
                <div class="mb-3">
                    <strong>Address:</strong><br>
                    <?php echo nl2br(sanitize($order_details['customer_address'])); ?>
                </div>
                
                <hr>
                
                <h6>Update Order Status</h6>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                    <div class="mb-3">
                        <select name="status" class="form-select" required>
                            <option value="pending" <?php echo $order_details['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order_details['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order_details['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order_details['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order_details['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Orders List View -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Orders</h5>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
            <h4>No orders yet</h4>
            <p class="text-muted">Orders will appear here once customers start placing them.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $order['id']; ?></strong>
                        </td>
                        <td><?php echo sanitize($order['customer_name']); ?></td>
                        <td><?php echo sanitize($order['customer_email']); ?></td>
                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                        <td>
                            <span class="badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="manage-orders.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Order Statistics -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4><?php echo count(array_filter($orders, function($o) { return $o['status'] == 'pending'; })); ?></h4>
                        <p class="mb-0 order-status-label">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4><?php echo count(array_filter($orders, function($o) { return $o['status'] == 'processing'; })); ?></h4>
                        <p class="mb-0 order-status-label">Processing</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4><?php echo count(array_filter($orders, function($o) { return $o['status'] == 'shipped'; })); ?></h4>
                        <p class="mb-0 order-status-label">Shipped</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4><?php echo count(array_filter($orders, function($o) { return $o['status'] == 'delivered'; })); ?></h4>
                        <p class="mb-0 order-status-label">Delivered</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once $includes_dir . '/footer.php'; ?> 