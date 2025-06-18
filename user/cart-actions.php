<?php
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $size = sanitize($_POST['size'] ?? '');
        
        if ($product_id > 0 && $quantity > 0) {
            $product = getProductById($product_id);
            if ($product && $product['stock_quantity'] >= $quantity) {
                addToCart($product_id, $quantity, $size);
                $response = [
                    'success' => true,
                    'message' => 'Product added to cart',
                    'cart_count' => count(getCartItems()),
                    'cart_total' => getCartTotal()
                ];
            } else {
                $response = ['success' => false, 'message' => 'Product not available or insufficient stock'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid product or quantity'];
        }
        break;
        
    case 'update':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $size = sanitize($_POST['size'] ?? '');
        
        if ($product_id > 0) {
            updateCartQuantity($product_id, $quantity, $size);
            $response = [
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => count(getCartItems()),
                'cart_total' => getCartTotal()
            ];
        } else {
            $response = ['success' => false, 'message' => 'Invalid product'];
        }
        break;
        
    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $size = sanitize($_POST['size'] ?? '');
        
        if ($product_id > 0) {
            removeFromCart($product_id, $size);
            $response = [
                'success' => true,
                'message' => 'Product removed from cart',
                'cart_count' => count(getCartItems()),
                'cart_total' => getCartTotal()
            ];
        } else {
            $response = ['success' => false, 'message' => 'Invalid product'];
        }
        break;
        
    case 'clear':
        clearCart();
        $response = [
            'success' => true,
            'message' => 'Cart cleared',
            'cart_count' => 0,
            'cart_total' => 0
        ];
        break;
        
    case 'get':
        $cart_items = getCartItems();
        $response = [
            'success' => true,
            'cart_items' => $cart_items,
            'cart_count' => count($cart_items),
            'cart_total' => getCartTotal()
        ];
        break;
        
    default:
        $response = ['success' => false, 'message' => 'Unknown action'];
}

echo json_encode($response);
?> 