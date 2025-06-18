<?php 
$page_title = 'Manage Users';

// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php'; 
require_once $includes_dir . '/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect(getBasePath() . 'index.php', 'Access denied. Admin privileges required.', 'error');
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        
        // Don't allow admin to delete themselves
        if ($user_id == $_SESSION['user_id']) {
            redirect('manage-users.php', 'You cannot delete your own account.', 'error');
        }
        
        try {
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND is_admin = 0");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                logAdminAction($_SESSION['user_id'], "Deleted user: " . $user['name']);
                redirect('manage-users.php', 'User deleted successfully!', 'success');
            }
        } catch (PDOException $e) {
            redirect('manage-users.php', 'Error deleting user.', 'error');
        }
    }
    
    if (isset($_POST['toggle_admin'])) {
        $user_id = (int)$_POST['user_id'];
        
        // Don't allow admin to remove their own admin privileges
        if ($user_id == $_SESSION['user_id']) {
            redirect('manage-users.php', 'You cannot modify your own admin status.', 'error');
        }
        
        try {
            $stmt = $pdo->prepare("SELECT name, is_admin FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user) {
                $new_admin_status = $user['is_admin'] ? 0 : 1;
                $stmt = $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
                $stmt->execute([$new_admin_status, $user_id]);
                
                $action = $new_admin_status ? "granted admin privileges to" : "removed admin privileges from";
                logAdminAction($_SESSION['user_id'], "$action user: " . $user['name']);
                redirect('manage-users.php', 'User admin status updated successfully!', 'success');
            }
        } catch (PDOException $e) {
            redirect('manage-users.php', 'Error updating user status.', 'error');
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT u.*, 
                     COUNT(o.id) as total_orders,
                     SUM(o.total_amount) as total_spent
                     FROM users u 
                     LEFT JOIN orders o ON u.id = o.user_id 
                     GROUP BY u.id 
                     ORDER BY u.created_at DESC");
$users = $stmt->fetchAll();

// Get user statistics
$total_users = count($users);
$total_customers = count(array_filter($users, function($u) { return !$u['is_admin']; }));
$total_admins = count(array_filter($users, function($u) { return $u['is_admin']; }));
$total_revenue = array_sum(array_column($users, 'total_spent'));
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
        </div>
    </div>
</div>

<!-- User Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-number"><?php echo $total_users; ?></div>
            <h6>Total Users</h6>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-success">
                <i class="fas fa-user-friends"></i>
            </div>
            <div class="card-number"><?php echo $total_customers; ?></div>
            <h6>Customers</h6>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-warning">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="card-number"><?php echo $total_admins; ?></div>
            <h6>Administrators</h6>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-info">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-number"><?php echo formatPrice($total_revenue); ?></div>
            <h6>Total Revenue</h6>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Users</h5>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4>No users found</h4>
            <p class="text-muted">Users will appear here once they register.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <strong><?php echo sanitize($user['name']); ?></strong>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <span class="badge bg-primary ms-2">You</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo sanitize($user['email']); ?></td>
                        <td><?php echo sanitize($user['phone']); ?></td>
                        <td>
                            <?php if ($user['is_admin']): ?>
                                <span class="badge bg-warning">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-success">Customer</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $user['total_orders']; ?></span>
                        </td>
                        <td><?php echo formatPrice($user['total_spent'] ?: 0); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#userModal<?php echo $user['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to toggle admin status for this user?')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="toggle_admin" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-user-shield"></i>
                                    </button>
                                </form>
                                
                                <?php if (!$user['is_admin']): ?>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Detail Modals -->
<?php foreach ($users as $user): ?>
<div class="modal fade" id="userModal<?php echo $user['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details - <?php echo sanitize($user['name']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Personal Information</h6>
                        <p><strong>Name:</strong> <?php echo sanitize($user['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo sanitize($user['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo sanitize($user['phone']); ?></p>
                        <p><strong>Address:</strong><br><?php echo nl2br(sanitize($user['address'])); ?></p>
                        <p><strong>Role:</strong> 
                            <?php if ($user['is_admin']): ?>
                                <span class="badge bg-warning">Administrator</span>
                            <?php else: ?>
                                <span class="badge bg-success">Customer</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Joined:</strong> <?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></p>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Order Statistics</h6>
                        <p><strong>Total Orders:</strong> <?php echo $user['total_orders']; ?></p>
                        <p><strong>Total Spent:</strong> <?php echo formatPrice($user['total_spent'] ?: 0); ?></p>
                        
                        <?php if ($user['total_orders'] > 0): ?>
                        <h6 class="mt-4">Recent Orders</h6>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                        $stmt->execute([$user['id']]);
                        $recent_orders = $stmt->fetchAll();
                        ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_orders as $order): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Order #<?php echo $order['id']; ?></strong><br>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span><br>
                                    <small><?php echo formatPrice($order['total_amount']); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?> 