<?php 
$page_title = 'Contact Us';
require_once 'includes/header.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For demo purposes, we'll just show a success message
        $success_message = "Thank you for your message! We'll get back to you soon.";
    }
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1>Contact Us</h1>
                <p>Have questions or need assistance? We're here to help! Reach out to us and we'll get back to you as soon as possible.</p>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <!-- Contact Form -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-envelope me-2"></i>Send us a Message
                </h4>
            </div>
            <div class="card-body">
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
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
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="<?php echo isset($_POST['subject']) ? $_POST['subject'] : ''; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a subject.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? $_POST['message'] : ''; ?></textarea>
                        <div class="invalid-feedback">
                            Please provide your message.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Address</h6>
                    <p class="text-muted mb-0">
                        123 Shoe Street<br>
                        Fashion District<br>
                        City, State 12345
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-phone text-primary me-2"></i>Phone</h6>
                    <p class="text-muted mb-0">
                        <a href="tel:+1234567890" class="text-decoration-none">+1 (555) 123-4567</a>
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-envelope text-primary me-2"></i>Email</h6>
                    <p class="text-muted mb-0">
                        <a href="mailto:info@cgshoes.com" class="text-decoration-none">info@cgshoes.com</a>
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-clock text-primary me-2"></i>Business Hours</h6>
                    <p class="text-muted mb-0">
                        Monday - Friday: 9:00 AM - 6:00 PM<br>
                        Saturday: 10:00 AM - 4:00 PM<br>
                        Sunday: Closed
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Quick Contact -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-headset me-2"></i>Quick Contact
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Customer Support</h6>
                    <p class="text-muted small mb-2">For order inquiries and general questions</p>
                    <a href="mailto:support@cgshoes.com" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-envelope me-2"></i>support@cgshoes.com
                    </a>
                </div>
                
                <div class="mb-3">
                    <h6>Sales Team</h6>
                    <p class="text-muted small mb-2">For bulk orders and business inquiries</p>
                    <a href="mailto:sales@cgshoes.com" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-envelope me-2"></i>sales@cgshoes.com
                    </a>
                </div>
                
                <div>
                    <h6>Technical Support</h6>
                    <p class="text-muted small mb-2">For website and account issues</p>
                    <a href="mailto:tech@cgshoes.com" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-envelope me-2"></i>tech@cgshoes.com
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2>Frequently Asked Questions</h2>
                <p class="lead">Find answers to common questions</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                What is your return policy?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer a 30-day return policy for all unused items in their original packaging. Return shipping costs are the responsibility of the customer.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                How long does shipping take?
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Standard shipping takes 3-5 business days. Express shipping (1-2 business days) is available for an additional fee.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                Do you ship internationally?
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Currently, we only ship within the United States. We're working on expanding our international shipping options.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                How can I track my order?
                            </button>
                        </h2>
                        <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Once your order ships, you'll receive a tracking number via email. You can also track your order in your account dashboard.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq5">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and Apple Pay.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2>Find Us</h2>
                <p class="lead">Visit our store location</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <!-- Placeholder for map -->
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                            <div class="text-center">
                                <i class="fas fa-map fa-3x text-muted mb-3"></i>
                                <h5>Interactive Map</h5>
                                <p class="text-muted">Map integration would be added here in a real application</p>
                                <p class="text-muted">123 Shoe Street, Fashion District, City, State 12345</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
            form.classList.add('was-validated');
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 