<?php 
$page_title = 'Manage Products';

// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php'; 
require_once $includes_dir . '/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect(getBasePath() . 'index.php', 'Access denied. Admin privileges required.', 'error');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['edit_product'])) {
        $name = sanitize($_POST['name']);
        $category_id = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $description = sanitize($_POST['description']);
        $stock_quantity = (int)$_POST['stock_quantity'];
        $sizes = isset($_POST['sizes']) ? (is_array($_POST['sizes']) ? implode(',', array_map('sanitize', $_POST['sizes'])) : sanitize($_POST['sizes'])) : '';
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = "Product name is required";
        }
        
        if ($category_id <= 0) {
            $errors[] = "Please select a category";
        }
        
        if ($price <= 0) {
            $errors[] = "Price must be greater than 0";
        }
        
        if ($stock_quantity < 0) {
            $errors[] = "Stock quantity cannot be negative";
        }
        
        // Handle image upload
        $image_name = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = uploadImage($_FILES['image']);
            if (!$image_name) {
                $errors[] = "Invalid image file. Please upload JPG, PNG, or GIF.";
            }
        }
        
        if (empty($errors)) {
            try {
                if (isset($_POST['add_product'])) {
                    // Add new product
                    $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, description, image, stock_quantity, sizes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$name, $category_id, $price, $description, $image_name, $stock_quantity, $sizes]);
                    
                    logAdminAction($_SESSION['user_id'], "Added new product: $name");
                    redirect('manage-products.php', 'Product added successfully!', 'success');
                } else {
                    // Update existing product
                    $update_fields = "name = ?, category_id = ?, price = ?, description = ?, stock_quantity = ?, sizes = ?";
                    $params = [$name, $category_id, $price, $description, $stock_quantity, $sizes];
                    
                    if ($image_name) {
                        $update_fields .= ", image = ?";
                        $params[] = $image_name;
                    }
                    
                    $params[] = $product_id;
                    $stmt = $pdo->prepare("UPDATE products SET $update_fields WHERE id = ?");
                    $stmt->execute($params);
                    
                    logAdminAction($_SESSION['user_id'], "Updated product: $name");
                    redirect('manage-products.php', 'Product updated successfully!', 'success');
                }
            } catch (PDOException $e) {
                $errors[] = "Database error. Please try again.";
            }
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $product_id = (int)$_POST['product_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                
                logAdminAction($_SESSION['user_id'], "Deleted product: " . $product['name']);
                redirect('manage-products.php', 'Product deleted successfully!', 'success');
            }
        } catch (PDOException $e) {
            redirect('manage-products.php', 'Error deleting product.', 'error');
        }
    }
}

// Get categories
$categories = getCategories();

// Get products for listing
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-shoe-prints me-2"></i>
                <?php echo $edit_product ? 'Edit Product' : 'Manage Products'; ?>
            </h2>
            <?php if (!$edit_product): ?>
            <a href="manage-products.php?add=1" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Product
            </a>
            <?php else: ?>
            <a href="manage-products.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($_GET['add']) || $edit_product): ?>
<!-- Add/Edit Product Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
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
                
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo $edit_product ? $edit_product['name'] : (isset($_POST['name']) ? $_POST['name'] : ''); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a product name.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) || 
                                                   (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a category.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo $edit_product ? $edit_product['price'] : (isset($_POST['price']) ? $_POST['price'] : ''); ?>" 
                                       step="0.01" min="0" required>
                            </div>
                            <div class="invalid-feedback">
                                Please provide a valid price.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                   value="<?php echo $edit_product ? $edit_product['stock_quantity'] : (isset($_POST['stock_quantity']) ? $_POST['stock_quantity'] : '0'); ?>" 
                                   min="0" required>
                            <div class="invalid-feedback">
                                Please provide stock quantity.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sizes" class="form-label">Sizes <span class="text-muted" style="font-weight:normal;">(select one or more)</span></label>
                            <?php
                            $all_sizes = ['5','6','7','8','9','10','11','12'];
                            $selected_sizes = isset($edit_product) ? getProductSizes($edit_product['sizes']) : (isset($_POST['sizes']) ? (array)$_POST['sizes'] : []);
                            ?>
                            <select class="form-select" id="sizes" name="sizes[]" multiple size="5">
                                <?php foreach ($all_sizes as $size): ?>
                                    <option value="<?php echo $size; ?>" <?php echo in_array($size, $selected_sizes) ? 'selected' : ''; ?>><?php echo $size; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple sizes.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo $edit_product ? $edit_product['description'] : (isset($_POST['description']) ? $_POST['description'] : ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Upload JPG, PNG, or GIF file. Max size: 5MB.</div>
                        
                        <?php if ($edit_product && $edit_product['image']): ?>
                        <div class="mt-2">
                            <p class="mb-2">Current Image:</p>
                            <img src="<?php echo getBasePath(); ?>assets/images/<?php echo $edit_product['image']; ?>" 
                                 alt="Current product image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" name="<?php echo $edit_product ? 'edit_product' : 'add_product'; ?>" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                        </button>
                        
                        <?php if ($edit_product): ?>
                        <a href="manage-products.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Use clear, descriptive product names
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Upload high-quality product images
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Set accurate stock quantities
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Write detailed product descriptions
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Products List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Products</h5>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shoe-prints fa-3x text-muted mb-3"></i>
            <h4>No products yet</h4>
            <p class="text-muted mb-4">Start by adding your first product.</p>
            <a href="manage-products.php?add=1" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add First Product
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <img src="<?php echo getBasePath(); ?>assets/images/<?php echo $product['image'] ?: 'default-shoe.jpg'; ?>" 
                                 alt="<?php echo sanitize($product['name']); ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td>
                            <strong><?php echo sanitize($product['name']); ?></strong>
                            <br>
                            <small class="text-muted">ID: <?php echo $product['id']; ?></small>
                        </td>
                        <td><?php echo sanitize($product['category_name']); ?></td>
                        <td><?php echo formatPrice($product['price']); ?></td>
                        <td>
                            <span class="<?php echo $product['stock_quantity'] <= 5 ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $product['stock_quantity']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="badge bg-success">In Stock</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="manage-products.php?edit=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
<?php endif; ?>

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
    
    // Image preview
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // You can add image preview functionality here
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php require_once $includes_dir . '/footer.php'; ?> 