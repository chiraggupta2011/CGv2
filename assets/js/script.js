// Modern CG Shoes JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeAnimations();
    initializeCartFunctionality();
    initializeFormValidation();
    initializeScrollEffects();
    initializeProductInteractions();
});

// Animation System
function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observe elements with animation classes
    const animatedElements = document.querySelectorAll('.animate-fade-in-up, .animate-fade-in-left, .animate-fade-in-right');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
}

// Cart Functionality
function initializeCartFunctionality() {
    // Add to cart with animation
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const quantity = this.closest('form')?.querySelector('input[name="quantity"]')?.value || 1;
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="loading-spinner"></span> Adding...';
            this.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                addToCart(productId, quantity);
                
                // Show success animation
                this.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
                this.classList.add('btn-success');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                    this.disabled = false;
                }, 2000);
                
                // Update cart count
                updateCartCount();
                
                // Show cart notification
                showNotification('Product added to cart!', 'success');
            }, 1000);
        });
    });
    
    // Quantity controls
    const quantityControls = document.querySelectorAll('.quantity-control');
    quantityControls.forEach(control => {
        const minusBtn = control.querySelector('.btn:first-child');
        const plusBtn = control.querySelector('.btn:last-child');
        const input = control.querySelector('input');
        
        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    triggerInputEvent(input);
                }
            });
            
            plusBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.max) || 999;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    triggerInputEvent(input);
                }
            });
        }
    });
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Real-time validation
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
}

// Scroll Effects
function initializeScrollEffects() {
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }
    
    // Scroll to top button
    const scrollToTopBtn = document.querySelector('.scroll-to-top');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Product Interactions
function initializeProductInteractions() {
    // Product image gallery
    const productImages = document.querySelectorAll('.product-image');
    const mainImage = document.querySelector('#main-product-image');
    
    if (productImages.length && mainImage) {
        productImages.forEach(img => {
            img.addEventListener('click', function() {
                // Update main image
                mainImage.src = this.src;
                
                // Update active state
                productImages.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Wishlist functionality
    const wishlistButtons = document.querySelectorAll('.wishlist-btn');
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const icon = this.querySelector('i');
            
            // Toggle wishlist state
            if (this.classList.contains('active')) {
                this.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Removed from wishlist', 'info');
            } else {
                this.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Added to wishlist', 'success');
            }
        });
    });
}

// Utility Functions
function addToCart(productId, quantity) {
    // This would typically make an AJAX call to the server
    // For now, we'll simulate it
    console.log(`Adding product ${productId} with quantity ${quantity} to cart`);
    
    // Update cart count in navbar
    const cartBadge = document.querySelector('.navbar .badge');
    if (cartBadge) {
        const currentCount = parseInt(cartBadge.textContent) || 0;
        cartBadge.textContent = currentCount + parseInt(quantity);
    }
}

function updateCartCount() {
    // This would typically fetch the current cart count from the server
    // For now, we'll just update the badge
    const cartBadge = document.querySelector('.navbar .badge');
    if (cartBadge) {
        const currentCount = parseInt(cartBadge.textContent) || 0;
        cartBadge.textContent = currentCount;
    }
}

function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldName = field.name;
    
    let isValid = true;
    let errorMessage = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    if (fieldType === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    }
    
    // Password validation
    if (fieldName === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            errorMessage = 'Password must be at least 6 characters';
        }
    }
    
    // Phone validation
    if (fieldName === 'phone' && value) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number';
        }
    }
    
    // Update field state
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        removeFieldError(field);
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        showFieldError(field, errorMessage);
    }
}

function showFieldError(field, message) {
    removeFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function removeFieldError(field) {
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

function triggerInputEvent(input) {
    const event = new Event('input', { bubbles: true });
    input.dispatchEvent(event);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
            ${message}
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('#search-input');
    const searchResults = document.querySelector('#search-results');
    
    if (searchInput && searchResults) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
}

function performSearch(query) {
    // This would typically make an AJAX call to search the database
    // For now, we'll simulate it
    console.log(`Searching for: ${query}`);
    
    // Show loading state
    const searchResults = document.querySelector('#search-results');
    if (searchResults) {
        searchResults.innerHTML = '<div class="text-center p-3"><div class="loading-spinner"></div></div>';
        searchResults.style.display = 'block';
    }
    
    // Simulate search results
    setTimeout(() => {
        if (searchResults) {
            searchResults.innerHTML = `
                <div class="search-result-item">
                    <img src="assets/images/default-shoe.jpg" alt="Product" class="search-result-image">
                    <div class="search-result-info">
                        <h6>Sample Product</h6>
                        <p class="text-muted">$99.99</p>
                    </div>
                </div>
            `;
        }
    }, 1000);
}

// Initialize search when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
});

// Add CSS for notifications
const notificationStyles = `
<style>
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    z-index: 9999;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 400px;
}

.notification.show {
    transform: translateX(0);
}

.notification-success {
    border-left: 4px solid #10b981;
}

.notification-error {
    border-left: 4px solid #ef4444;
}

.notification-warning {
    border-left: 4px solid #f59e0b;
}

.notification-info {
    border-left: 4px solid #3b82f6;
}

.notification-content {
    flex: 1;
}

.notification-close {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
}

.notification-close:hover {
    color: #374151;
}

.navbar-scrolled {
    background: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-result-item:hover {
    background-color: #f9fafb;
}

.search-result-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.search-result-info h6 {
    margin: 0;
    font-size: 0.875rem;
}

.search-result-info p {
    margin: 0;
    font-size: 0.75rem;
}

.animate-in {
    opacity: 1 !important;
    transform: translateY(0) !important;
}
</style>
`;

// Add styles to head
document.head.insertAdjacentHTML('beforeend', notificationStyles); 