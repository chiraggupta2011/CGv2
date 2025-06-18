<?php 
$page_title = 'Admin Dashboard';

// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php'; 
require_once $includes_dir . '/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect(getBasePath() . 'index.php', 'Access denied. Admin privileges required.', 'error');
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
$total_products = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'");
$total_revenue = $stmt->fetch()['total_revenue'] ?: 0;

// Get recent orders
$stmt = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// Get low stock products
$stmt = $pdo->query("SELECT * FROM products WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC LIMIT 5");
$low_stock_products = $stmt->fetchAll();

// Get recent admin logs
$stmt = $pdo->query("SELECT al.*, u.name as admin_name FROM admin_logs al 
                     LEFT JOIN users u ON al.admin_id = u.id 
                     ORDER BY al.timestamp DESC LIMIT 10");
$recent_logs = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
            <div>
                <a href="manage-products.php" class="btn btn-primary me-2">
                    <i class="fas fa-plus me-2"></i>Add Product
                </a>
                <a href="manage-orders.php" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>View Orders
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-primary">
                <i class="fas fa-shoe-prints"></i>
            </div>
            <div class="card-number"><?php echo $total_products; ?></div>
            <h6>Total Products</h6>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-success">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="card-number"><?php echo $total_orders; ?></div>
            <h6>Total Orders</h6>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-info">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-number"><?php echo $total_users; ?></div>
            <h6>Total Customers</h6>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-warning">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-number"><?php echo formatPrice($total_revenue); ?></div>
            <h6>Total Revenue</h6>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Recent Orders
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                <p class="text-muted text-center py-3">No orders yet</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo sanitize($order['customer_name']); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="manage-orders.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="manage-orders.php" class="btn btn-outline-primary">View All Orders</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Products -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($low_stock_products)): ?>
                <p class="text-muted text-center py-3">All products are well stocked</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($low_stock_products as $product): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1"><?php echo sanitize($product['name']); ?></h6>
                            <small class="text-muted">Stock: <?php echo $product['stock_quantity']; ?></small>
                        </div>
                        <a href="manage-products.php?edit=<?php echo $product['id']; ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="manage-products.php" class="btn btn-outline-warning">Manage Products</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Admin Logs -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recent Admin Actions
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_logs)): ?>
                <p class="text-muted text-center py-3">No admin actions logged</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                            <tr>
                                <td><?php echo sanitize($log['admin_name']); ?></td>
                                <td><?php echo sanitize($log['action']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($log['timestamp'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="logs.php" class="btn btn-outline-secondary">View All Logs</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="manage-products.php" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="manage-orders.php" class="btn btn-success w-100">
                            <i class="fas fa-list me-2"></i>Manage Orders
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="manage-users.php" class="btn btn-info w-100">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="logs.php" class="btn btn-secondary w-100">
                            <i class="fas fa-history me-2"></i>View Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 