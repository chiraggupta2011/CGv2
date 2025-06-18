<?php 
$page_title = 'Product Details';
require_once '../includes/header.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    redirect('../shop.php', 'Product not found.', 'error');
}

// Get product details
$product = getProductById($product_id);

if (!$product) {
    redirect('../shop.php', 'Product not found.', 'error');
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int)$_POST['quantity'];
    $size = sanitize($_POST['size'] ?? '');
    
    if ($quantity > 0 && $quantity <= $product['stock_quantity']) {
        addToCart($product_id, $quantity, $size);
        redirect('details.php?id=' . $product_id, 'Product added to cart successfully!', 'success');
    } else {
        $error = 'Invalid quantity or insufficient stock.';
    }
}

$page_title = $product['name'];
?>

<!-- Modern Product Details Section -->
<section class="product-details-section py-5">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb modern-breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo getBasePath(); ?>index.php" class="breadcrumb-link">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?php echo getBasePath(); ?>shop.php" class="breadcrumb-link">Shop</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?php echo getBasePath(); ?>shop.php?category=<?php echo $product['category_id']; ?>" class="breadcrumb-link">
                        <?php echo sanitize($product['category_name']); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo sanitize($product['name']); ?>
                </li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Product Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image-container">
                        <img src="<?php echo getBasePath(); ?>assets/images/<?php echo $product['image'] ?: 'default-shoe.jpg'; ?>" 
                             alt="<?php echo sanitize($product['name']); ?>" 
                             class="main-product-image"
                             id="main-product-image">
                        
                        <!-- Stock Status Badge -->
                        <?php if ($product['stock_quantity'] <= 0): ?>
                            <div class="stock-badge out-of-stock">
                                <i class="fas fa-times-circle me-1"></i>Out of Stock
                            </div>
                        <?php elseif ($product['stock_quantity'] <= 5): ?>
                            <div class="stock-badge low-stock">
                                <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <div class="category-badge">
                            <?php echo sanitize($product['category_name']); ?>
                        </div>
                    </div>
                    
                    <!-- Thumbnail Gallery -->
                    <div class="thumbnail-gallery mt-3">
                        <div class="row g-2">
                            <div class="col-3">
                                <img src="<?php echo getBasePath(); ?>assets/images/<?php echo $product['image'] ?: 'default-shoe.jpg'; ?>" 
                                     class="thumbnail-image active" 
                                     alt="<?php echo sanitize($product['name']); ?>"
                                     onclick="changeMainImage(this.src, this)">
                            </div>
                            <!-- Additional thumbnails would go here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Information -->
            <div class="col-lg-6">
                <div class="product-info-card">
                    <div class="product-header">
                        <h1 class="product-title"><?php echo sanitize($product['name']); ?></h1>
                        
                        <!-- Price Section -->
                        <div class="price-section">
                            <div class="current-price"><?php echo formatPrice($product['price']); ?></div>
                            <?php if (isset($product['original_price']) && !empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                <div class="original-price"><?php echo formatPrice($product['original_price']); ?></div>
                                <div class="discount-badge">
                                    <?php 
                                    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                                    echo "-{$discount}%";
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="stock-status">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <div class="status-item in-stock">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span><?php echo $product['stock_quantity']; ?> items available</span>
                                </div>
                                <?php if ($product['stock_quantity'] <= 5): ?>
                                    <div class="status-item low-stock-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span>Low stock - Order soon!</span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="status-item out-of-stock">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <span>Currently out of stock</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Product Description -->
                    <div class="product-description">
                        <p><?php echo sanitize($product['description']); ?></p>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="add-to-cart-section">
                        <form method="POST" class="cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <!-- Size Selection -->
                            <?php 
                            $sizes = getProductSizes($product['sizes']);
                            if (!empty($sizes)): 
                            ?>
                            <div class="form-group mb-2">
                                <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 1rem;">
                                    Please select your shoe size before adding to cart.
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Select Size</label>
                                <div class="size-options">
                                    <?php foreach ($sizes as $size): ?>
                                    <div class="size-option">
                                        <input type="radio" name="size" id="size-<?php echo sanitize($size); ?>" 
                                               value="<?php echo sanitize($size); ?>" required>
                                        <label for="size-<?php echo sanitize($size); ?>" class="size-label">
                                            <?php echo sanitize($size); ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Quantity Selection -->
                            <div class="form-group mb-4">
                                <label class="form-label">Quantity</label>
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn" onclick="changeQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="qty-input" name="quantity" id="quantity" 
                                           value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                    <button type="button" class="qty-btn" onclick="changeQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-add-to-cart">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Add to Cart
                                </button>
                                
                                <button type="button" class="btn btn-outline-primary btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-heart me-2"></i>
                                    Wishlist
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="out-of-stock-section">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This product is currently out of stock. Please check back later.
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                            <i class="fas fa-heart me-2"></i>
                            Add to Wishlist
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Product Features -->
                    <div class="product-features">
                        <h5 class="features-title">Product Features</h5>
                        <div class="features-grid">
                            <div class="feature-item">
                                <i class="fas fa-star feature-icon"></i>
                                <span>Premium Quality</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shoe-prints feature-icon"></i>
                                <span>Comfortable Fit</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shield-alt feature-icon"></i>
                                <span>Durable</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-undo feature-icon"></i>
                                <span>30-Day Returns</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Share Product -->
                    <div class="share-section">
                        <h6 class="share-title">Share this product</h6>
                        <div class="share-buttons">
                            <a href="#" class="share-btn facebook" onclick="shareProduct('facebook')">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="share-btn twitter" onclick="shareProduct('twitter')">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="share-btn whatsapp" onclick="shareProduct('whatsapp')">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="share-btn link" onclick="copyProductLink()">
                                <i class="fas fa-link"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products Section -->
<section class="related-products-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Related Products</h2>
            <p class="section-subtitle">You might also like these products</p>
        </div>
        
        <div class="row g-4">
            <?php
            // Get related products (same category, excluding current product)
            $related_products = getProducts($product['category_id'], 4);
            $related_products = array_filter($related_products, function($p) use ($product_id) {
                return $p['id'] != $product_id;
            });
            $related_products = array_slice($related_products, 0, 4);
            ?>
            
            <?php foreach ($related_products as $related): ?>
            <div class="col-lg-3 col-md-6">
                <div class="related-product-card">
                    <div class="product-image-container">
                        <img src="<?php echo getBasePath(); ?>assets/images/<?php echo $related['image'] ?: 'default-shoe.jpg'; ?>" 
                             class="product-image" alt="<?php echo sanitize($related['name']); ?>">
                        <div class="product-overlay">
                            <a href="details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h5 class="product-name"><?php echo sanitize($related['name']); ?></h5>
                        <p class="product-category"><?php echo sanitize($related['category_name']); ?></p>
                        <div class="product-price"><?php echo formatPrice($related['price']); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
/* Modern Product Details Styles */
.product-details-section {
    background: var(--gradient-light);
    min-height: 100vh;
}

/* Breadcrumb Styles */
.modern-breadcrumb {
    background: var(--gradient-card);
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
}

.breadcrumb-link {
    color: var(--gray-600);
    text-decoration: none;
    transition: var(--transition);
}

.breadcrumb-link:hover {
    color: var(--primary-color);
}

/* Product Gallery */
.product-gallery {
    background: var(--gradient-card);
    border-radius: var(--border-radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow-lg);
}

.main-image-container {
    position: relative;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    background: white;
    box-shadow: var(--shadow);
}

.main-product-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    transition: var(--transition);
}

.stock-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
    z-index: 2;
}

.stock-badge.out-of-stock {
    background: var(--gradient-danger);
}

.stock-badge.low-stock {
    background: var(--gradient-warning);
}

.category-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--gradient-primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    font-weight: 500;
    z-index: 2;
}

.thumbnail-gallery {
    margin-top: 1.5rem;
}

.thumbnail-image {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    border: 2px solid transparent;
}

.thumbnail-image:hover,
.thumbnail-image.active {
    border-color: var(--primary-color);
    transform: scale(1.05);
}

/* Product Info Card */
.product-info-card {
    background: var(--gradient-card);
    border-radius: var(--border-radius-xl);
    padding: 2.5rem;
    box-shadow: var(--shadow-lg);
    height: fit-content;
}

.product-header {
    margin-bottom: 2rem;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.price-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.current-price {
    font-size: 2rem;
    font-weight: 700;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.original-price {
    font-size: 1.25rem;
    color: var(--gray-500);
    text-decoration: line-through;
}

.discount-badge {
    background: var(--gradient-success);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    font-weight: 600;
}

.stock-status {
    margin-bottom: 2rem;
}

.status-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.status-item.in-stock {
    color: var(--success-color);
}

.status-item.low-stock-warning {
    color: var(--warning-color);
}

.status-item.out-of-stock {
    color: var(--danger-color);
}

.product-description {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--gradient-light);
    border-radius: var(--border-radius-lg);
    border-left: 4px solid var(--primary-color);
}

.product-description p {
    margin: 0;
    line-height: 1.6;
    color: var(--gray-700);
}

/* Add to Cart Section */
.add-to-cart-section {
    margin-bottom: 2rem;
}

.cart-form {
    background: var(--gradient-light);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.75rem;
    display: block;
}

/* Size Options */
.size-options {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.size-option {
    position: relative;
}

.size-option input[type="radio"] {
    display: none;
}

.size-label {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
    min-width: 60px;
    text-align: center;
}

.size-option input[type="radio"]:checked + .size-label {
    background: var(--gradient-primary);
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Quantity Selector */
.quantity-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 150px;
}

.qty-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--gradient-light);
    color: var(--gray-700);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
}

.qty-btn:hover {
    background: var(--gradient-primary);
    color: white;
    transform: scale(1.1);
}

.qty-input {
    width: 70px;
    height: 40px;
    text-align: center;
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    font-weight: 600;
    background: white;
}

.qty-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-add-to-cart {
    flex: 1;
    min-width: 200px;
    height: 50px;
    font-size: 1.1rem;
    font-weight: 600;
}

.btn-wishlist {
    height: 50px;
    font-weight: 600;
}

/* Product Features */
.product-features {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--gradient-light);
    border-radius: var(--border-radius-lg);
}

.features-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--gray-900);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.feature-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1rem;
    background: white;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.feature-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.feature-icon {
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.feature-item span {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
}

/* Share Section */
.share-section {
    text-align: center;
}

.share-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--gray-900);
}

.share-buttons {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
}

.share-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--transition);
    font-size: 1.1rem;
}

.share-btn.facebook {
    background: var(--gradient-primary);
    color: white;
}

.share-btn.twitter {
    background: var(--gradient-info);
    color: white;
}

.share-btn.whatsapp {
    background: var(--gradient-success);
    color: white;
}

.share-btn.link {
    background: var(--gradient-secondary);
    color: white;
}

.share-btn:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    color: white;
}

/* Related Products */
.related-products-section {
    background: var(--gradient-light);
}

.section-header {
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--gray-600);
}

.related-product-card {
    background: var(--gradient-card);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    height: 100%;
}

.related-product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.product-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.related-product-card:hover .product-overlay {
    opacity: 1;
}

.related-product-card:hover .product-image {
    transform: scale(1.1);
}

.product-info {
    padding: 1.5rem;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--gray-900);
}

.product-category {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin-bottom: 0.75rem;
}

.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Out of Stock Section */
.out-of-stock-section {
    text-align: center;
    padding: 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .product-title {
        font-size: 2rem;
    }
    
    .current-price {
        font-size: 1.75rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-add-to-cart {
        min-width: auto;
    }
    
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .size-options {
        justify-content: center;
    }
}
</style>

<script>
function changeMainImage(src, element) {
    document.getElementById('main-product-image').src = src;
    
    // Update active state
    document.querySelectorAll('.thumbnail-image').forEach(img => {
        img.classList.remove('active');
    });
    element.classList.add('active');
}

function changeQuantity(delta) {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.max);
    const newValue = currentValue + delta;
    
    if (newValue >= 1 && newValue <= maxValue) {
        input.value = newValue;
    }
}

function shareProduct(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo addslashes($product['name']); ?>');
    
    let shareUrl = '';
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${title}%20${url}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

function copyProductLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        alert('Product link copied to clipboard!');
    });
}

function addToWishlist(productId) {
    // Implement wishlist functionality
    alert('Product added to wishlist!');
}
</script>

<?php require_once '../includes/footer.php'; ?> 