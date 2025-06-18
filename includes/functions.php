<?php
session_start();
require_once 'db.php';

// Get base path for assets and links
function getBasePath() {
    $script_name = $_SERVER['SCRIPT_NAME'];
    $path_parts = explode('/', trim($script_name, '/'));
    
    // Remove the filename and 'includes' directory
    array_pop($path_parts); // Remove filename
    if (end($path_parts) === 'includes') {
        array_pop($path_parts); // Remove 'includes'
    }
    
    // If we're in a subdirectory, add the appropriate number of '../'
    $depth = count($path_parts) - 1; // -1 because we want to go up to the project root
    return $depth > 0 ? str_repeat('../', $depth) : '';
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Get user by ID
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get all categories
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

// Get products with optional category filter
function getProducts($category_id = null, $limit = null) {
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id";
    
    if ($category_id) {
        $sql .= " WHERE p.category_id = ?";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    
    if ($category_id) {
        $stmt->execute([$category_id]);
    } else {
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

// Get product by ID
function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get cart items
function getCartItems() {
    if (!isset($_SESSION['cart'])) {
        return [];
    }
    
    global $pdo;
    $cart_items = [];
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $product = getProductById($product_id);
        if ($product) {
            // Handle both old format (quantity only) and new format (quantity + size)
            if (is_array($item)) {
                $product['quantity'] = $item['quantity'];
                $product['size'] = $item['size'] ?? '';
            } else {
                $product['quantity'] = $item;
                $product['size'] = '';
            }
            $cart_items[] = $product;
        }
    }
    
    return $cart_items;
}

// Calculate cart total
function getCartTotal() {
    $cart_items = getCartItems();
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

// Add to cart
function addToCart($product_id, $quantity = 1, $size = '') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cart_key = $product_id . ($size ? "_$size" : "");
    
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'quantity' => $quantity,
            'size' => $size
        ];
    }
}

// Remove from cart
function removeFromCart($product_id, $size = '') {
    $cart_key = $product_id . ($size ? "_$size" : "");
    if (isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
    }
}

// Update cart quantity
function updateCartQuantity($product_id, $quantity, $size = '') {
    $cart_key = $product_id . ($size ? "_$size" : "");
    if ($quantity <= 0) {
        removeFromCart($product_id, $size);
    } else {
        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
        }
    }
}

// Clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Create order
function createOrder($user_id, $total_amount) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->execute([$user_id, $total_amount]);
    return $pdo->lastInsertId();
}

// Add order items
function addOrderItems($order_id, $cart_items) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($cart_items as $item) {
        $size = $item['size'] ?? '';
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price'], $size]);
    }
}

// Get user orders
function getUserOrders($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Get order items
function getOrderItems($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// Log admin action
function logAdminAction($admin_id, $action) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, timestamp) VALUES (?, ?, NOW())");
    $stmt->execute([$admin_id, $action]);
}

// Ensure assets directory exists
function ensureAssetsDirectory() {
    $assets_dir = dirname(dirname(__FILE__)) . '/assets/images/';
    if (!file_exists($assets_dir)) {
        mkdir($assets_dir, 0777, true);
    }
    return $assets_dir;
}

// Upload image
function uploadImage($file, $target_dir = null) {
    // Use absolute path to assets/images directory
    if ($target_dir === null) {
        $target_dir = ensureAssetsDirectory();
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return false;
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $target_path = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $new_filename;
    }
    
    return false;
}

// Format price
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Display message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        
        return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                    $message
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

// Helper function to get available sizes from product
function getProductSizes($sizes_string) {
    if (empty($sizes_string)) {
        return [];
    }
    return array_map('trim', explode(',', $sizes_string));
}
?> 