

<?php
require_once 'auth_functions.php';
requireLogin();
require_once 'config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify user exists in database before proceeding
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: signin.php?redirect=checkout.php');
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // User doesn't exist - log them out and redirect
    logoutUser();
    header('Location: signin.php?redirect=checkout.php');
    exit;
}
$stmt->close();

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get cart items with product details
$products = [];
$product_ids = implode(',', array_keys($_SESSION['cart']));
$sql = "SELECT product_id, product_name, price, image_url FROM products WHERE product_id IN ($product_ids)";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[$row['product_id']] = $row;
    }
}

// Get all categories for footer
$categories = [];
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Calculate totals
$subtotal = 0;
$tax_rate = 0.13; // 13% tax
$delivery_fee = 5.99;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $subtotal += $products[$product_id]['price'] * $quantity;
    }
}

$tax_amount = round($subtotal * $tax_rate, 2);
$total = $subtotal + $tax_amount + $delivery_fee;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $full_name = trim($_POST['full_name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $zip_code = trim($_POST['zip_code']);
    $payment_method = $_POST['payment_method'];
    $instructions = trim($_POST['instructions']);
    
    // Initialize credit card fields
    $card_name = $card_number = $card_expiry = $card_cvv = '';
    
    if ($payment_method === 'Credit/Debit Card') {
        $card_name = trim($_POST['card_name']);
        $card_number = str_replace(' ', '', trim($_POST['card_number']));
        $card_expiry = trim($_POST['card_expiry']);
        $card_cvv = trim($_POST['card_cvv']);
        
        // Validate credit card details
        if (empty($card_name) || empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
            $error = "Please fill in all credit card details";
        } elseif (!preg_match('/^[0-9]{16}$/', $card_number)) {
            $error = "Please enter a valid 16-digit card number";
        } elseif (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
            $error = "Please enter a valid expiry date (MM/YY)";
        } elseif (!preg_match('/^[0-9]{3,4}$/', $card_cvv)) {
            $error = "Please enter a valid CVV (3 or 4 digits)";
        }
    }
    
    // Basic validation for all fields
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($zip_code)) {
        $error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    }
    
    if (!isset($error)) {
        // Generate order number
        $order_number = 'FF' . date('YmdHis') . rand(100, 999);
        
        // Insert order into database
        $sql = "INSERT INTO orders (
            user_id, order_number, full_name, email, subtotal, delivery_fee, tax_amount, total,
            delivery_address, delivery_city, delivery_zip_code, delivery_phone,
            delivery_instructions, payment_method, card_name, card_number, card_expiry, card_cvv
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssddddssssssssss",
            $_SESSION['user_id'],
            $order_number,
            $full_name,
            $email,
            $subtotal,
            $delivery_fee,
            $tax_amount,
            $total,
            $address,
            $city,
            $zip_code,
            $phone,
            $instructions,
            $payment_method,
            $card_name,
            $card_number,
            $card_expiry,
            $card_cvv
        );
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Insert order items
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                if (isset($products[$product_id])) {
                    $price = $products[$product_id]['price'];
                    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                            VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                    $stmt->execute();
                }
            }
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to confirmation page
            header("Location: order-confirmation.php?order_number=$order_number");
            exit;
        } else {
            $error = "There was an error processing your order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Fresh Fields</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --light-gray: #e9ecef;
            --gray: #6c757d;
            --danger: #e74c3c;
            --warning: #f39c12;
            --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --border-radius: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }

        /* Header Styles */
        header {
            background: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark);
            text-decoration: none;
        }

        .logo img {
            height: 40px;
        }

        .logo span {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 1.5rem;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
}

        .user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.welcome-msg {
    color: var(--dark);
    font-weight: 500;
}

.btn-logout {
    background: none;
    border: none;
    color: var(--danger);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    font-size: inherit;
    font-family: inherit;
    padding: 0;
}

.btn-logout:hover {
    color: var(--primary);
}

        .cart-icon {
            position: relative;
            cursor: pointer;
            color: var(--dark);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .cart-icon:hover {
            color: var(--primary);
        }

        .cart-count {
            position: absolute;
            top: -0.5rem;
            right: -0.5rem;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 1.2rem;
            height: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        .page-title {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 5rem;
            height: 0.25rem;
            background: var(--primary);
            border-radius: 0.25rem;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 992px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }

        /* Checkout Form */
        .checkout-form {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Payment Methods */
        .payment-methods {
            margin-top: 1rem;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .payment-option:hover {
            border-color: var(--primary);
        }

        .payment-option input {
            margin-right: 1rem;
        }

        .payment-icon {
            margin-right: 0.75rem;
            color: var(--dark);
            font-size: 1.25rem;
        }

        /* Credit Card Form */
        .credit-card-form {
            display: none;
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--light-gray);
            border-radius: var(--border-radius);
        }

        .credit-card-form.active {
            display: block;
        }

        .card-icons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 2.5rem;
            height: 1.75rem;
            background: white;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem;
        }

        .card-icon img {
            max-width: 100%;
            max-height: 100%;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group.half {
            flex: 1;
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 1rem;
        }

        .summary-title {
            font-size: 1.25rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-label {
            color: var(--gray);
        }

        .summary-value {
            font-weight: 600;
        }

        .total-row {
            border-top: 1px solid var(--light-gray);
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .cart-items-preview {
            margin-top: 1.5rem;
        }

        .cart-item-preview {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .item-image {
            width: 50px;
            height: 50px;
            border-radius: 0.25rem;
            object-fit: cover;
        }

        .item-name {
            font-weight: 500;
        }

        .item-quantity {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
        }

        /* Checkout Button */
        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .checkout-btn:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-hover);
        }

        /* Error Message */
        .error-message {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: none;
        }

        .error-message.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .checkout-form,
            .order-summary {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.5rem;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <img src="images/logo.png" alt="Fresh Fields Logo">
                    Fresh<span>Fields</span>
                </a>
                
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="product.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="about.php">About</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                            <a href="logout.php" class="btn-logout">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="signin.php" class="btn-logout">Sign In</a>
                    <?php endif; ?>
                    
                    <div class="cart-icon" onclick="window.location.href='cart.php'">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?= array_sum($_SESSION['cart']) ?></span>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1 class="page-title">Checkout</h1>
        
        <div class="checkout-container">
            <div class="checkout-form">
                <?php if (isset($error)): ?>
                <div class="error-message active">
                    <?= $error ?>
                </div>
                <?php endif; ?>
                
                <form action="checkout.php" method="POST" id="checkout-form">
                    <div class="form-section">
                        <h2 class="section-title">Delivery Information</h2>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required 
                                value="<?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required 
                                value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required 
                                value="<?= isset($_SESSION['user_phone']) ? htmlspecialchars($_SESSION['user_phone']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Delivery Address</label>
                            <input type="text" id="address" name="address" required 
                                value="<?= isset($_SESSION['user_address']) ? htmlspecialchars($_SESSION['user_address']) : '' ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required 
                                    value="<?= isset($_SESSION['user_city']) ? htmlspecialchars($_SESSION['user_city']) : '' ?>">
                            </div>
                            
                            <div class="form-group half">
                                <label for="zip_code">Postal Code</label>
                                <input type="text" id="zip_code" name="zip_code" required 
                                    value="<?= isset($_SESSION['user_zip_code']) ? htmlspecialchars($_SESSION['user_zip_code']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="instructions">Delivery Instructions (Optional)</label>
                            <textarea id="instructions" name="instructions"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2 class="section-title">Payment Method</h2>
                        
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Credit/Debit Card">
                                <i class="fas fa-credit-card payment-icon"></i>
                                Credit/Debit Card
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="PayPal">
                                <i class="fab fa-paypal payment-icon"></i>
                                PayPal
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Cash on Delivery">
                                <i class="fas fa-money-bill-wave payment-icon"></i>
                                Cash on Delivery
                            </label>
                        </div>
                        
                        <div class="credit-card-form" id="credit-card-form">
                            <div class="card-icons">
                                <div class="card-icon">
                                    <img src="images/payment/visa.png" alt="Visa">
                                </div>
                                <div class="card-icon">
                                    <img src="images/payment/mastercard.png" alt="Mastercard">
                                </div>
                                <div class="card-icon">
                                    <img src="images/payment/apple-pay.png" alt="Apple Pay">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="card_name">Name on Card</label>
                                <input type="text" id="card_name" name="card_name" placeholder="Full name as shown on card">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half">
                                    <label for="card_expiry">Expiry Date</label>
                                    <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                </div>
                                
                                <div class="form-group half">
                                    <label for="card_cvv">CVV</label>
                                    <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="checkout-btn">Place Order</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2 class="summary-title">Order Summary</h2>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">$<?= number_format($subtotal, 2) ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Tax (13%)</span>
                    <span class="summary-value">$<?= number_format($tax_amount, 2) ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Delivery</span>
                    <span class="summary-value">$<?= number_format($delivery_fee, 2) ?></span>
                </div>
                
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>
                
                <div class="cart-items-preview">
                    <h3 class="section-title">Your Items</h3>
                    
                    <?php foreach ($_SESSION['cart'] as $product_id => $quantity): 
                        if (isset($products[$product_id])): 
                            $product = $products[$product_id];
                    ?>
                    <div class="cart-item-preview">
                        <div class="item-info">
                            <img src="<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>" class="item-image">
                            <div>
                                <div class="item-name"><?= $product['product_name'] ?></div>
                                <div class="item-quantity">Qty: <?= $quantity ?></div>
                            </div>
                        </div>
                        <div class="item-price">$<?= number_format($product['price'] * $quantity, 2) ?></div>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <footer style="background: var(--dark); color: white; padding: 3rem 0; margin-top: 3rem;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <div>
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Fresh Fields</h3>
                    <p style="margin-bottom: 1rem;">We're committed to bringing you the freshest, highest quality groceries at affordable prices.</p>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" style="color: white;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div>
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Quick Links</h3>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 0.5rem;"><a href="index.php" style="color: white; text-decoration: none;">Home</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="product.php" style="color: white; text-decoration: none;">Products</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="contact.php" style="color: white; text-decoration: none;">Contact</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="about.php" style="color: white; text-decoration: none;">About</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Categories</h3>
                    <ul style="list-style: none;">
                        <?php foreach ($categories as $category): 
                            $category_slug = strtolower(str_replace(' ', '-', $category['category_name']));
                        ?>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="product.php?category=<?= $category_slug ?>" style="color: white; text-decoration: none;"><?= $category['category_name'] ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div>
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Contact Info</h3>
                    <p style="margin-bottom: 0.5rem;"><i class="fas fa-envelope"></i> info@freshfields.com</p>
                    <p style="margin-bottom: 0.5rem;"><i class="fas fa-phone"></i> (437) 871-1007</p>
                    <p style="margin-bottom: 0.5rem;"><i class="fas fa-map-marker-alt"></i> 20 Columbia St W, Waterloo, ON</p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p>&copy; 2024 Fresh Fields. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Show/hide credit card form based on payment method
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const cardForm = document.getElementById('credit-card-form');
                if (this.value === 'Credit/Debit Card') {
                    cardForm.classList.add('active');
                } else {
                    cardForm.classList.remove('active');
                }
            });
        });
        
        // Format card number input (add spaces every 4 digits)
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });
        
        // Format expiry date input (add slash after MM)
        document.getElementById('card_expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
        
        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!paymentMethod) {
                alert('Please select a payment method');
                e.preventDefault();
                return false;
            }
            
            if (paymentMethod.value === 'Credit/Debit Card') {
                const cardName = document.getElementById('card_name').value.trim();
                const cardNumber = document.getElementById('card_number').value.replace(/\s+/g, '');
                const cardExpiry = document.getElementById('card_expiry').value;
                const cardCvv = document.getElementById('card_cvv').value.trim();
                
                if (!cardName || !cardNumber || !cardExpiry || !cardCvv) {
                    alert('Please fill in all credit card details');
                    e.preventDefault();
                    return false;
                }
                
                if (!/^\d{16}$/.test(cardNumber)) {
                    alert('Please enter a valid 16-digit card number');
                    e.preventDefault();
                    return false;
                }
                
                if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                    alert('Please enter a valid expiry date in MM/YY format');
                    e.preventDefault();
                    return false;
                }
                
                if (!/^\d{3,4}$/.test(cardCvv)) {
                    alert('Please enter a valid CVV (3 or 4 digits)');
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    </script>
</body>
</html>
[file content end]