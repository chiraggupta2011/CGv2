<?php 
$page_title = 'My Profile';
require_once '../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php', 'Please login to view your profile.', 'warning');
}

// Get current user data
$user = getUserById($_SESSION['user_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered by another user";
        }
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    // Password change validation
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } elseif (!verifyPassword($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            $update_fields = "name = ?, email = ?, phone = ?, address = ?";
            $params = [$name, $email, $phone, $address];
            
            // Add password update if provided
            if (!empty($new_password)) {
                $update_fields .= ", password = ?";
                $params[] = hashPassword($new_password);
            }
            
            $params[] = $_SESSION['user_id'];
            $stmt = $pdo->prepare("UPDATE users SET $update_fields WHERE id = ?");
            $stmt->execute($params);
            
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            redirect('profile.php', 'Profile updated successfully!', 'success');
        } catch (PDOException $e) {
            $errors[] = "Error updating profile. Please try again.";
        }
    }
}

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND status != 'cancelled'");
$stmt->execute([$_SESSION['user_id']]);
$total_spent = $stmt->fetch()['total_spent'] ?: 0;

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                </div>
                <h5 class="card-title"><?php echo sanitize($user['name']); ?></h5>
                <p class="text-muted"><?php echo sanitize($user['email']); ?></p>
                <p class="text-muted">
                    <i class="fas fa-calendar me-2"></i>
                    Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                </p>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">My Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="mb-2">
                            <h4 class="text-primary mb-0"><?php echo $total_orders; ?></h4>
                            <small class="text-muted">Orders</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <h4 class="text-success mb-0"><?php echo formatPrice($total_spent); ?></h4>
                            <small class="text-muted">Total Spent</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="orders.php" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-bag me-2"></i>My Orders
                    </a>
                    <a href="../shop.php" class="btn btn-outline-success">
                        <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                    </a>
                    <a href="../user/cart.php" class="btn btn-outline-info">
                        <i class="fas fa-cart-plus me-2"></i>View Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Profile Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo sanitize($user['name']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide your full name.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo sanitize($user['email']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo sanitize($user['phone']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide your phone number.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?php echo sanitize($user['address']); ?></textarea>
                            <div class="invalid-feedback">
                                Please provide your address.
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Change Password (Optional)</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Recent Orders
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                <div class="text-center py-3">
                    <i class="fas fa-shopping-bag fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No orders yet</p>
                    <a href="../shop.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="orders.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Check if passwords match
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
                event.preventDefault();
                event.stopPropagation();
            } else {
                confirmPassword.setCustomValidity('');
            }
            
            form.classList.add('was-validated');
        });
    }
    
    // Real-time password confirmation validation
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            const newPassword = document.getElementById('new_password');
            if (this.value !== newPassword.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 