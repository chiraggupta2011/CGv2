<?php 
$page_title = 'Home';
require_once 'includes/header.php';

// Get featured products
$featured_products = getProducts(null, 8);

// Get categories
$categories = getCategories();
?>

<!-- Hero Slider Section -->
<section class="hero-slider-section">
    <div class="hero-slider">
        <!-- Slide 1 -->
        <div class="slide active" style="background-image: url('assets/images/hero-sneaker-men.jpg');">
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <h1 class="slide-title">Step into Style</h1>
                <p class="slide-description">Discover our latest collection of premium footwear designed for comfort and elegance.</p>
                <a href="shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Shop Now
                </a>
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80');">
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <h1 class="slide-title">Premium Quality</h1>
                <p class="slide-description">Crafted with the finest materials for ultimate comfort and durability.</p>
                <a href="shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-star me-2"></i>Explore Collection
                </a>
            </div>
        </div>
        
        <!-- Slide 3 -->
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80');">
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <h1 class="slide-title">Free Shipping</h1>
                <p class="slide-description">Free shipping on all orders over ₹1000. Limited time offer!</p>
                <a href="shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-truck me-2"></i>Shop & Save
                </a>
            </div>
        </div>
        
        <!-- Slider Controls -->
        <button class="slider-arrow prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-arrow next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="slider-controls">
            <div class="slider-dot active" onclick="currentSlide(1)"></div>
            <div class="slider-dot" onclick="currentSlide(2)"></div>
            <div class="slider-dot" onclick="currentSlide(3)"></div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="animate-fade-in-up">Why Choose CG Shoes?</h2>
                <p class="text-muted animate-fade-in-up">Experience the perfect blend of style, comfort, and quality</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="feature-card animate-fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h5>Free Shipping</h5>
                    <p class="text-muted">Free shipping on all orders over $50</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="feature-card animate-fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h5>Easy Returns</h5>
                    <p class="text-muted">30-day return policy for your peace of mind</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="feature-card animate-fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Secure Payment</h5>
                    <p class="text-muted">100% secure payment processing</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="feature-card animate-fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">Round the clock customer support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="animate-fade-in-up">Shop by Category</h2>
                <p class="text-muted animate-fade-in-up">Find your perfect style in our curated collections</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-lg-4 col-md-6">
                <div class="category-card animate-fade-in-up" onclick="window.location.href='shop.php?category=<?php echo $category['id']; ?>'">
                    <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="<?php echo sanitize($category['name']); ?>">
                    <div class="category-overlay">
                        <h4><?php echo sanitize($category['name']); ?></h4>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="animate-fade-in-up">Featured Products</h2>
                <p class="text-muted animate-fade-in-up">Discover our most popular and trending footwear</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featured_products as $product): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card product-card animate-fade-in-up">
                    <div class="position-relative">
                        <img src="assets/images/<?php echo $product['image'] ?: 'default-shoe.jpg'; ?>" 
                             class="card-img-top" alt="<?php echo sanitize($product['name']); ?>">
                        <span class="category-badge"><?php echo sanitize($product['category_name']); ?></span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo sanitize($product['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo substr(sanitize($product['description']), 0, 100); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price"><?php echo formatPrice($product['price']); ?></span>
                            <a href="products/details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>View All Products
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6">
                <h3 class="text-white mb-3">Stay Updated</h3>
                <p class="text-white-50 mb-4">Subscribe to our newsletter for exclusive offers and latest updates</p>
                <form class="d-flex gap-2">
                    <input type="email" class="form-control" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-paper-plane me-2"></i>Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Hero Slider Functionality
let currentSlideIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.slider-dot');

function showSlide(index) {
    // Hide all slides
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    // Show current slide
    if (slides[index]) {
        slides[index].classList.add('active');
        dots[index].classList.add('active');
    }
}

function changeSlide(direction) {
    currentSlideIndex += direction;
    
    if (currentSlideIndex >= slides.length) {
        currentSlideIndex = 0;
    } else if (currentSlideIndex < 0) {
        currentSlideIndex = slides.length - 1;
    }
    
    showSlide(currentSlideIndex);
}

function currentSlide(index) {
    currentSlideIndex = index - 1;
    showSlide(currentSlideIndex);
}

// Auto-advance slides
setInterval(() => {
    changeSlide(1);
}, 5000);

// Scroll to Top Functionality
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/hide scroll to top button
window.addEventListener('scroll', function() {
    const scrollButton = document.querySelector('.scroll-to-top');
    if (window.pageYOffset > 300) {
        scrollButton.classList.add('visible');
    } else {
        scrollButton.classList.remove('visible');
    }
});

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all animated elements
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.animate-fade-in-up, .animate-fade-in-left, .animate-fade-in-right');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 