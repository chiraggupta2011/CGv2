<?php 
$page_title = 'Register';

// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php'; 
require_once $includes_dir . '/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
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
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered";
        }
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    // If no errors, create user
    if (empty($errors)) {
        try {
            $hashed_password = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, is_admin, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
            $stmt->execute([$name, $email, $hashed_password, $phone, $address]);
            
            redirect('login.php', 'Registration successful! Please login.', 'success');
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header text-center">
                <h3 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </h3>
                <p class="text-muted mb-0">Join CG Shoes today!</p>
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
                                   value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" required>
                            <div class="invalid-feedback">
                                Please provide your full name.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" required>
                            <div class="invalid-feedback">
                                Password must be at least 6 characters.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="invalid-feedback">
                                Please confirm your password.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" required>
                        <div class="invalid-feedback">
                            Please provide your phone number.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?></textarea>
                        <div class="invalid-feedback">
                            Please provide your address.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> *
                            </label>
                            <div class="invalid-feedback">
                                You must agree to the terms and conditions.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Account Registration</h6>
                <p>By creating an account with CG Shoes, you agree to provide accurate and complete information.</p>
                
                <h6>2. Privacy Policy</h6>
                <p>Your personal information will be handled according to our privacy policy and will not be shared with third parties without your consent.</p>
                
                <h6>3. Order Processing</h6>
                <p>Orders are processed on a first-come, first-served basis. We reserve the right to cancel orders if products are out of stock.</p>
                
                <h6>4. Returns and Refunds</h6>
                <p>We offer a 30-day return policy for unused items in original packaging. Shipping costs for returns are the responsibility of the customer.</p>
                
                <h6>5. Account Security</h6>
                <p>You are responsible for maintaining the security of your account credentials and for all activities that occur under your account.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Check if passwords match
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
            event.preventDefault();
            event.stopPropagation();
        } else {
            confirmPassword.setCustomValidity('');
        }
        
        form.classList.add('was-validated');
    });
    
    // Real-time password confirmation validation
    const confirmPassword = document.getElementById('confirm_password');
    confirmPassword.addEventListener('input', function() {
        const password = document.getElementById('password');
        if (this.value !== password.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php require_once $includes_dir . '/footer.php'; ?> 