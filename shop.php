<?php 
$page_title = 'Shop';
require_once 'includes/header.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$price_min = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$price_max = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

// Get products with filters
$products = getProducts($category_filter);
$categories = getCategories();

// Apply search filter
if ($search_query) {
    $products = array_filter($products, function($product) use ($search_query) {
        return stripos($product['name'], $search_query) !== false || 
               stripos($product['description'], $search_query) !== false;
    });
}

// Apply price filter
if ($price_min !== null || $price_max !== null) {
    $products = array_filter($products, function($product) use ($price_min, $price_max) {
        $price = $product['price'];
        if ($price_min !== null && $price < $price_min) return false;
        if ($price_max !== null && $price > $price_max) return false;
        return true;
    });
}

// Apply sorting
switch ($sort_by) {
    case 'price_low':
        usort($products, function($a, $b) { return $a['price'] <=> $b['price']; });
        break;
    case 'price_high':
        usort($products, function($a, $b) { return $b['price'] <=> $a['price']; });
        break;
    case 'name':
        usort($products, function($a, $b) { return strcmp($a['name'], $b['name']); });
        break;
    default: // newest
        // Products are already sorted by newest in getProducts function
        break;
}

$total_products = count($products);
?>

<!-- Shop Header -->
<section class="py-5" style="background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-3">Shop Our Collection</h1>
                <p class="text-muted mb-0">Discover the perfect footwear for every occasion</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <p class="text-muted mb-0"><?php echo $total_products; ?> products found</p>
            </div>
        </div>
    </div>
</section>

<!-- Filters and Products -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter-form">
                            <!-- Search -->
                            <div class="mb-4">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>" 
                                       placeholder="Search products...">
                            </div>
                            
                            <!-- Categories -->
                            <div class="mb-4">
                                <label class="form-label">Categories</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category" 
                                           value="" id="cat-all" <?php echo !$category_filter ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cat-all">
                                        All Categories
                                    </label>
                                </div>
                                <?php foreach ($categories as $category): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category" 
                                           value="<?php echo $category['id']; ?>" 
                                           id="cat-<?php echo $category['id']; ?>"
                                           <?php echo $category_filter == $category['id'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cat-<?php echo $category['id']; ?>">
                                        <?php echo sanitize($category['name']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Price Range -->
                            <div class="mb-4">
                                <label class="form-label">Price Range</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="min_price" 
                                               placeholder="Min" value="<?php echo $price_min; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="max_price" 
                                               placeholder="Max" value="<?php echo $price_max; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sort By -->
                            <div class="mb-4">
                                <label class="form-label">Sort By</label>
                                <select class="form-select" name="sort">
                                    <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                                </select>
                            </div>
                            
                            <!-- Apply Filters -->
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-search me-2"></i>Apply Filters
                            </button>
                            
                            <!-- Clear Filters -->
                            <a href="shop.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Clear All
                            </a>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h3>No products found</h3>
                    <p class="text-muted">Try adjusting your filters or search terms</p>
                    <a href="shop.php" class="btn btn-primary">
                        <i class="fas fa-undo me-2"></i>Clear Filters
                    </a>
                </div>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card product-card animate-fade-in-up">
                            <div class="position-relative">
                                <img src="assets/images/<?php echo $product['image'] ?: 'default-shoe.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo sanitize($product['name']); ?>">
                                <span class="category-badge"><?php echo sanitize($product['category_name']); ?></span>
                                
                                <!-- Quick Actions -->
                                <div class="product-actions">
                                    <button class="btn btn-sm btn-light wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                
                                <!-- Stock Badge -->
                                <?php if ($product['stock_quantity'] <= 0): ?>
                                <div class="stock-badge out-of-stock">Out of Stock</div>
                                <?php elseif ($product['stock_quantity'] <= 5): ?>
                                <div class="stock-badge low-stock">Low Stock</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo sanitize($product['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo substr(sanitize($product['description']), 0, 80); ?>...</p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <small class="text-muted text-decoration-line-through">
                                        <?php echo formatPrice($product['original_price']); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="products/details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                    
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <form method="POST" action="user/cart-actions.php" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="action" value="add">
                                        
                                        <?php 
                                        $sizes = getProductSizes($product['sizes']);
                                        if (!empty($sizes)): 
                                        ?>
                                        <div class="mb-2">
                                            <select name="size" class="form-select form-select-sm" required>
                                                <option value="">Select Size</option>
                                                <?php foreach ($sizes as $size): ?>
                                                <option value="<?php echo sanitize($size); ?>"><?php echo sanitize($size); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <button type="submit" name="add_to_cart" class="btn btn-outline-primary w-100 add-to-cart-btn" 
                                                data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="fas fa-times me-2"></i>Out of Stock
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="quick-view-image" src="" alt="" class="img-fluid rounded">
                    </div>
                    <div class="col-md-6">
                        <h4 id="quick-view-title"></h4>
                        <p class="text-muted" id="quick-view-category"></p>
                        <p id="quick-view-description"></p>
                        <h5 class="text-primary mb-3" id="quick-view-price"></h5>
                        
                        <form method="POST" action="user/cart-actions.php">
                            <input type="hidden" name="product_id" id="quick-view-product-id">
                            <div class="mb-3">
                                <label for="quick-view-quantity" class="form-label">Quantity</label>
                                <div class="quantity-control">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeQuickViewQuantity(-1)">-</button>
                                    <input type="number" class="form-control" id="quick-view-quantity" name="quantity" value="1" min="1" max="10">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeQuickViewQuantity(1)">+</button>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <a href="" class="btn btn-outline-primary" id="quick-view-details-link">
                                    <i class="fas fa-eye me-2"></i>View Full Details
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-actions {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-actions {
    opacity: 1;
}

.product-actions .btn {
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    backdrop-filter: blur(10px);
}

.stock-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.stock-badge.out-of-stock {
    background: var(--danger-color);
}

.stock-badge.low-stock {
    background: var(--warning-color);
}

#filter-form .form-check {
    margin-bottom: 0.5rem;
}

#filter-form .form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
</style>

<script>
// Quick view functionality
document.querySelectorAll('.quick-view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        // This would typically fetch product data via AJAX
        // For now, we'll simulate it
        showQuickView(productId);
    });
});

function showQuickView(productId) {
    // Simulate loading product data
    const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    modal.show();
    
    // Update modal content (in real app, this would come from AJAX)
    document.getElementById('quick-view-product-id').value = productId;
    document.getElementById('quick-view-details-link').href = `products/details.php?id=${productId}`;
}

function changeQuickViewQuantity(delta) {
    const input = document.getElementById('quick-view-quantity');
    const currentValue = parseInt(input.value);
    const newValue = currentValue + delta;
    
    if (newValue >= 1 && newValue <= 10) {
        input.value = newValue;
    }
}

// Auto-submit filters on change
document.querySelectorAll('#filter-form input, #filter-form select').forEach(element => {
    element.addEventListener('change', function() {
        // Only auto-submit for certain fields
        if (this.name === 'sort' || this.type === 'radio') {
            document.getElementById('filter-form').submit();
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 