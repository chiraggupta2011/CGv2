<?php 
$page_title = 'Login';

// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php'; 
require_once $includes_dir . '/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $errors = [];
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    
                    // Store token in database (you might want to create a remember_tokens table)
                    // For now, we'll just use the session
                }
                
                // Log admin action if admin login
                if ($user['is_admin']) {
                    logAdminAction($user['id'], 'Admin logged in');
                }
                
                // Redirect based on user type
                if ($user['is_admin']) {
                    redirect('../admin/', 'Welcome back, ' . $user['name'] . '!', 'success');
                } else {
                    redirect('../index.php', 'Welcome back, ' . $user['name'] . '!', 'success');
                }
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Login failed. Please try again.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header text-center">
                <h3 class="mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </h3>
                <p class="text-muted mb-0">Welcome back to CG Shoes!</p>
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
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                        </div>
                        <div class="invalid-feedback">
                            Please provide a valid email address.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Please provide your password.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">Don't have an account? <a href="register.php">Register here</a></p>
                    <p class="mb-0">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                            Forgot your password?
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Demo Accounts -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Demo Accounts</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Admin Account:</strong><br>
                    Email: admin@cgshoes.com<br>
                    Password: admin123
                </div>
                <div>
                    <strong>Customer Account:</strong><br>
                    Email: customer@cgshoes.com<br>
                    Password: customer123
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Forgot Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Enter your email address and we'll send you a link to reset your password.</p>
                <form id="forgotPasswordForm">
                    <div class="mb-3">
                        <label for="resetEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="resetEmail" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendResetLink">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
    
    // Forgot password functionality
    const sendResetLink = document.getElementById('sendResetLink');
    sendResetLink.addEventListener('click', function() {
        const email = document.getElementById('resetEmail').value;
        
        if (!email) {
            alert('Please enter your email address.');
            return;
        }
        
        // For demo purposes, just show a message
        alert('Password reset link would be sent to ' + email + ' in a real application.');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
        modal.hide();
    });
});
</script>

<?php require_once $includes_dir . '/footer.php'; ?> 