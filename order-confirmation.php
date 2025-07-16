<?php 
require_once 'auth_functions.php';
require_once 'config.php';
?>

<?php
// Check if order number is provided
if (!isset($_GET['order_number'])) {
    header('Location: index.php');
    exit;
}

$order_number = $_GET['order_number'];

// Fetch order details
$order = [];
$sql = "SELECT * FROM orders WHERE order_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    
    // Fetch order items
    $order_items = [];
    $sql = "SELECT oi.*, p.product_name 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order['order_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $order_items[] = $row;
        }
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Fresh Fields</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
            --danger: #e74c3c;
            --warning: #f39c12;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 6px 12px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        /* Header Styles */
        header {
            background: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--dark);
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
            gap: 2rem;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0;
            position: relative;
        }

        .nav-links a:hover,
        .nav-links a.active {
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

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-signin {
            background: none;
            border: none;
            color: var(--dark);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-signin:hover {
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
            top: -8px;
            right: -8px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Main Content */
        main {
            margin-top: 80px;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .confirmation-container {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: var(--shadow);
            text-align: center;
            margin-top: 2rem;
        }

        .confirmation-icon {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: 2rem;
        }

        .confirmation-title {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .confirmation-subtitle {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .order-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 2rem;
        }

        .order-details {
            background: var(--light);
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 600px;
            text-align: left;
        }

        .details-title {
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .details-row {
            display: flex;
            margin-bottom: 1rem;
        }

        .details-label {
            font-weight: 500;
            color: var(--dark);
            width: 150px;
        }

        .details-value {
            flex: 1;
        }

        .order-items {
            margin-top: 2rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .item-name {
            flex: 2;
        }

        .item-quantity {
            flex: 1;
            text-align: center;
        }

        .item-price {
            flex: 1;
            text-align: right;
            font-weight: 600;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-hover);
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 4rem 2rem 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .footer-section p,
        .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            line-height: 1.8;
            transition: var(--transition);
            display: block;
            margin-bottom: 0.5rem;
        }

        .footer-section a:hover {
            color: var(--primary);
            padding-left: 5px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #bdc3c7;
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            color: white;
            background: rgba(255,255,255,0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav {
                padding: 0 1rem;
            }

            .nav-links {
                display: none;
            }

            .page-title {
                font-size: 2rem;
            }

            .confirmation-container {
                padding: 2rem 1rem;
            }

            .details-row {
                flex-direction: column;
            }

            .details-label {
                width: 100%;
                margin-bottom: 0.3rem;
            }
        }

        @media (max-width: 576px) {
            main {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.8rem;
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
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                    <img src="images/logo.png" alt="Fresh Fields Logo">
                    Fresh<span>Fields</span>
                </a>
            </div>
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
        <button class="btn-signin" onclick="window.location.href='signin.php'">Sign In</button>
    <?php endif; ?>
    <div class="cart-icon" onclick="goToCart()">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="cart-count"><?= count($_SESSION['cart']) ?></span>
    </div>
</div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1 class="page-title">Order Confirmation</h1>
            
            <div class="confirmation-container">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="confirmation-title">Thank you for your order!</h2>
                <p class="confirmation-subtitle">
                    Your order has been placed successfully and will be delivered to you soon. 
                    We've sent a confirmation email with your order details.
                </p>
                
                <div class="order-number">
                    Order #<?= $order['order_number'] ?>
                </div>
                
                <div class="order-details">
                    <h3 class="details-title">Order Details</h3>
                    
                    <div class="details-row">
                        <span class="details-label">Order Date:</span>
                        <span class="details-value"><?= date('F j, Y', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Delivery Address:</span>
                        <span class="details-value">
                            <?= $order['delivery_address'] ?><br>
                            <?= $order['delivery_city'] ?>, <?= $order['delivery_zip_code'] ?>
                        </span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Contact:</span>
                        <span class="details-value">
                            <?= $order['delivery_phone'] ?><br>
                            <?= $order['email'] ?? 'N/A' ?>
                        </span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Payment Method:</span>
                        <span class="details-value"><?= $order['payment_method'] ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Delivery Instructions:</span>
                        <span class="details-value"><?= $order['delivery_instructions'] ?: 'None' ?></span>
                    </div>
                    
                    <div class="order-items">
                        <h3 class="details-title">Items Ordered</h3>
                        
                        <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <span class="item-name"><?= $item['product_name'] ?></span>
                            <span class="item-quantity"><?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></span>
                            <span class="item-price">$<?= number_format($item['quantity'] * $item['price'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="order-total">
                            <span>Total:</span>
                            <span>$<?= number_format($order['total'], 2) ?></span>
                        </div>
                    </div>
                </div>
                
                <a href="product.php" class="btn">Continue Shopping</a>
                <a href="index.php" class="btn" style="background: var(--dark); margin-left: 1rem;">Back to Home</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Fresh Fields</h3>
                    <p>We're committed to bringing you the freshest, highest quality groceries at affordable prices.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="product.php">Products</a>
                    <a href="contact.php">Contact</a>
                    <a href="about.php">About</a>
                </div>
                <div class="footer-section">
                    <h3>Categories</h3>
                    <?php 
                    $sql = "SELECT * FROM categories";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $category_slug = strtolower(str_replace(' ', '-', $row['category_name']));
                            echo '<a href="product.php?category=' . $category_slug . '">' . $row['category_name'] . '</a>';
                        }
                    }
                    ?>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-envelope"></i> info@freshfields.com</p>
                    <p><i class="fas fa-phone"></i> (437) 871-1007</p>
                    <p><i class="fas fa-map-marker-alt"></i> 20 Columbia St W, Waterloo, Ontario, N2L 3K3</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Fresh Fields. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Navigate to cart
        function goToCart() {
            window.location.href = 'cart.php';
        }
    </script>
</body>
</html>