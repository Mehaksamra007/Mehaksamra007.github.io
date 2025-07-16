<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
    // Add product to cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    
    // Return updated cart count
    echo json_encode(['cart_count' => count($_SESSION['cart'])]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
?>