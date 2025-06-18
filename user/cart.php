<?php 
$page_title = 'Shopping Cart';
require_once '../includes/header.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $cart_key => $quantity) {
            // Parse cart key to get product_id and size
            $parts = explode('_', $cart_key);
            $product_id = (int)$parts[0];
            $size = isset($parts[1]) ? $parts[1] : '';
            updateCartQuantity($product_id, $quantity, $size);
        }
        redirect('cart.php', 'Cart updated successfully!', 'success');
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = (int)$_POST['product_id'];
        $size = sanitize($_POST['size'] ?? '');
        removeFromCart($product_id, $size);
        redirect('cart.php', 'Item removed from cart!', 'success');
    }
    
    if (isset($_POST['clear_cart'])) {
        clearCart();
        redirect('cart.php', 'Cart cleared!', 'success');
    }
    
    if (isset($_POST['checkout'])) {
        if (!isLoggedIn()) {
            redirect('../auth/login.php', 'Please login to checkout.', 'warning');
        }
        
        $cart_items = getCartItems();
        if (empty($cart_items)) {
            redirect('cart.php', 'Your cart is empty.', 'warning');
        }
        
        // Create order
        $total_amount = getCartTotal();
        $order_id = createOrder($_SESSION['user_id'], $total_amount);
        
        if ($order_id) {
            // Add order items
            addOrderItems($order_id, $cart_items);
            
            // Clear cart
            clearCart();
            
            redirect('orders.php', 'Order placed successfully! Order #' . $order_id, 'success');
        } else {
            redirect('cart.php', 'Error creating order. Please try again.', 'error');
        }
    }
}

$cart_items = getCartItems();
$cart_total = getCartTotal();
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                    <span class="badge bg-primary ms-2"><?php echo count($cart_items); ?> items</span>
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($cart_items)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h4>Your cart is empty</h4>
                    <p class="text-muted mb-4">Looks like you haven't added any products to your cart yet.</p>
                    <a href="../shop.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                    </a>
                </div>
                <?php else: ?>
                <form method="POST">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Size</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo getBasePath(); ?>assets/images/<?php echo $item['image'] ?: 'default-shoe.jpg'; ?>" 
                                                 alt="<?php echo sanitize($item['name']); ?>" 
                                                 class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            <div>
                                                <h6 class="mb-1"><?php echo sanitize($item['name']); ?></h6>
                                                <small class="text-muted"><?php echo sanitize($item['category_name']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td>
                                        <?php if (!empty($item['size'])): ?>
                                            <span class="badge bg-secondary"><?php echo sanitize($item['size']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="quantity-control">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="changeQuantity('<?php echo $item['id'] . ($item['size'] ? '_' . $item['size'] : ''); ?>', -1)">-</button>
                                            <input type="number" class="form-control" name="quantity[<?php echo $item['id'] . ($item['size'] ? '_' . $item['size'] : ''); ?>]" 
                                                   value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                                   style="width: 60px; text-align: center;">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="changeQuantity('<?php echo $item['id'] . ($item['size'] ? '_' . $item['size'] : ''); ?>', 1)">+</button>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                                            <button type="submit" name="remove_item" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to remove this item?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" name="update_cart" class="btn btn-outline-primary">
                            <i class="fas fa-sync me-2"></i>Update Cart
                        </button>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="clear_cart" class="btn btn-outline-danger" 
                                    onclick="return confirm('Are you sure you want to clear your cart?')">
                                <i class="fas fa-trash me-2"></i>Clear Cart
                            </button>
                        </form>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Cart Summary -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($cart_items)): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal (<?php echo count($cart_items); ?> items):</span>
                    <span><?php echo formatPrice($cart_total); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span class="text-success">FREE</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-primary fs-5"><?php echo formatPrice($cart_total); ?></strong>
                </div>
                
                <form method="POST">
                    <button type="submit" name="checkout" class="btn btn-success btn-lg w-100 mb-3">
                        <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                    </button>
                </form>
                
                <div class="text-center">
                    <a href="<?php echo getBasePath(); ?>shop.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                    </a>
                </div>
                
                <!-- Shipping Info -->
                <div class="mt-4">
                    <h6><i class="fas fa-shipping-fast me-2"></i>Shipping Information</h6>
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-check text-success me-2"></i>Free shipping on orders over ₹1000</li>
                        <li><i class="fas fa-check text-success me-2"></i>Estimated delivery: 3-5 business days</li>
                        <li><i class="fas fa-check text-success me-2"></i>30-day return policy</li>
                    </ul>
                </div>
                
                <!-- Payment Methods -->
                <div class="mt-3">
                    <h6><i class="fas fa-credit-card me-2"></i>Accepted Payment Methods</h6>
                    <div class="d-flex gap-2">
                        <i class="fab fa-cc-visa fa-2x text-primary"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-primary"></i>
                        <i class="fab fa-cc-amex fa-2x text-primary"></i>
                        <i class="fab fa-cc-paypal fa-2x text-primary"></i>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-3">
                    <p class="text-muted mb-0">No items in cart</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recently Viewed (if implemented) -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Recently Viewed</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">No recently viewed products</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add to Cart Modal -->
<div class="modal fade" id="quickAddModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="modal-product-image" src="" alt="" class="img-fluid rounded">
                    </div>
                    <div class="col-md-8">
                        <h6 id="modal-product-name"></h6>
                        <p class="text-muted" id="modal-product-description"></p>
                        <p class="price" id="modal-product-price"></p>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <div class="quantity-control">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="quantity-minus">-</button>
                                <input type="number" class="form-control" id="quantity" value="1" min="1" max="10">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="quantity-plus">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-add-to-cart">
                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Quantity controls in cart
function changeQuantity(cart_key, delta) {
    const input = document.querySelector(`input[name="quantity[${cart_key}]"]`);
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.max);
    const newValue = currentValue + delta;
    
    if (newValue >= 1 && newValue <= maxValue) {
        input.value = newValue;
    }
}

// Auto-update cart when quantity changes
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('input[name^="quantity["]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Optionally auto-submit the form to update cart
            // this.closest('form').submit();
        });
    });
    
    // Quick add to cart functionality
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    const modal = new bootstrap.Modal(document.getElementById('quickAddModal'));
    let currentProductId = null;
    
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const card = this.closest('.product-card');
            
            // Get product details from the card
            const productName = card.querySelector('.card-title').textContent;
            const productDescription = card.querySelector('.card-text').textContent;
            const productPrice = card.querySelector('.price').textContent;
            const productImage = card.querySelector('.card-img-top').src;
            
            // Update modal content
            document.getElementById('modal-product-name').textContent = productName;
            document.getElementById('modal-product-description').textContent = productDescription;
            document.getElementById('modal-product-price').textContent = productPrice;
            document.getElementById('modal-product-image').src = productImage;
            
            currentProductId = productId;
            modal.show();
        });
    });
    
    // Quantity controls in modal
    const quantityInput = document.getElementById('quantity');
    const minusBtn = document.getElementById('quantity-minus');
    const plusBtn = document.getElementById('quantity-plus');
    
    if (minusBtn) {
        minusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });
    }
    
    if (plusBtn) {
        plusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value < 10) {
                quantityInput.value = value + 1;
            }
        });
    }
    
    // Confirm add to cart
    const confirmBtn = document.getElementById('confirm-add-to-cart');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (currentProductId) {
                const quantity = parseInt(quantityInput.value);
                addToCart(currentProductId, quantity);
                modal.hide();
            }
        });
    }
});

// Cart functions (if not already defined in script.js)
function addToCart(productId, quantity) {
    fetch('cart-actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cart_count);
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    });
}

function updateCartBadge(count) {
    const badge = document.querySelector('.navbar .badge');
    if (badge) {
        badge.textContent = count;
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>

<?php require_once '../includes/footer.php'; ?>