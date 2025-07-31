[file name]: order-details.php
[file content begin]
<?php
require_once 'auth_functions.php';
requireLogin();
require_once 'config.php';

// Check if order ID is provided and belongs to the current user
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch order details
$order = [];
$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$order = $result->fetch_assoc();

// Fetch order items
$order_items = [];
$sql = "SELECT oi.*, p.product_name, p.image_url 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $order_items[] = $row;
    }
}

// Fetch all user orders for the sidebar
$user_orders = [];
$sql = "SELECT order_id, order_number, created_at, total FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $user_orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Fresh Fields</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .nav-links a.active {
            color: var(--primary);
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
            padding: 2rem 0;
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

        .order-container {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .order-container {
                grid-template-columns: 1fr;
            }
        }

        /* Orders Sidebar */
        .orders-sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .sidebar-title {
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #eee;
        }

        .order-list {
            list-style: none;
        }

        .order-list-item {
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: var(--transition);
        }

        .order-list-item:hover {
            color: var(--primary);
        }

        .order-list-item.active {
            color: var(--primary);
            font-weight: 600;
        }

        .order-number-sidebar {
            font-weight: 500;
            margin-bottom: 0.3rem;
        }

        .order-date-sidebar {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .order-total-sidebar {
            font-weight: 600;
            margin-top: 0.3rem;
        }

        /* Order Details */
        .order-details-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .order-number {
            font-size: 1.5rem;
            color: var(--dark);
            font-weight: 600;
        }

        .order-date {
            color: var(--gray);
        }

        .order-status {
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .details-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }

        .details-group {
            margin-bottom: 1.5rem;
        }

        .details-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .details-value {
            color: var(--gray);
        }

        /* Order Items */
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .order-items-table th {
            text-align: left;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            color: var(--dark);
            font-weight: 600;
        }

        .order-items-table td {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }

        .item-info {
            display: flex;
            align-items: center;
        }

        .item-name {
            font-weight: 500;
        }

        .item-price {
            font-weight: 600;
        }

        /* Order Summary */
        .order-summary {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
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
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        /* Buttons */
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

        .btn-secondary {
            background: var(--secondary);
        }

        .btn-secondary:hover {
            background: #2980b9;
        }

        .btn-dark {
            background: var(--dark);
        }

        .btn-dark:hover {
            background: #1a252f;
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

            .order-header {
                flex-direction: column;
                gap: 1rem;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.8rem;
            }

            .order-items-table th,
            .order-items-table td {
                padding: 0.75rem 0;
            }

            .item-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .item-image {
                margin-bottom: 0.5rem;
            }
        }

        /* User menu styles */
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
    </style>
</head>
<body>
    <header>
        <div class="container">
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
                    <li><a href="career.php" class="active">Careers</a></li>
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
        </div>
    </header>

    <main>
        <div class="container">
            <h1 class="page-title">Your Order Details</h1>
            
            <div class="order-container">
                <div class="orders-sidebar">
                    <h3 class="sidebar-title">Your Orders</h3>
                    <ul class="order-list">
                        <?php foreach ($user_orders as $user_order): ?>
                        <li class="order-list-item <?= $user_order['order_id'] == $order_id ? 'active' : '' ?>" 
                            onclick="window.location.href='order-details.php?order_id=<?= $user_order['order_id'] ?>'">
                            <div class="order-number-sidebar">#<?= $user_order['order_number'] ?></div>
                            <div class="order-date-sidebar"><?= date('M j, Y', strtotime($user_order['created_at'])) ?></div>
                            <div class="order-total-sidebar">$<?= number_format($user_order['total'], 2) ?></div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="order-details-container">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #<?= $order['order_number'] ?></div>
                            <div class="order-date">Placed on <?= date('F j, Y', strtotime($order['created_at'])) ?></div>
                        </div>
                        <div class="order-status"><?= $order['order_status'] ?></div>
                    </div>
                    
                    <div class="details-section">
                        <h3 class="section-title">Delivery Information</h3>
                        <div class="details-grid">
                            <div>
                                <div class="details-group">
                                    <div class="details-label">Delivery Address</div>
                                    <div class="details-value">
                                        <?= $order['delivery_address'] ?><br>
                                        <?= $order['delivery_city'] ?>, <?= $order['delivery_zip_code'] ?>
                                    </div>
                                </div>
                                <div class="details-group">
                                    <div class="details-label">Contact</div>
                                    <div class="details-value">
                                        <?= $order['delivery_phone'] ?><br>
                                        <?= $order['email'] ?>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="details-group">
                                    <div class="details-label">Payment Method</div>
                                    <div class="details-value"><?= $order['payment_method'] ?></div>
                                </div>
                                <div class="details-group">
                                    <div class="details-label">Delivery Instructions</div>
                                    <div class="details-value"><?= $order['delivery_instructions'] ?: 'None' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="details-section">
                        <h3 class="section-title">Order Items</h3>
                        <table class="order-items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="item-info">
                                            <img src="<?= $item['image_url'] ?>" alt="<?= $item['product_name'] ?>" class="item-image">
                                            <span class="item-name"><?= $item['product_name'] ?></span>
                                        </div>
                                    </td>
                                    <td class="item-price">$<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td class="item-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-row">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value">$<?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Delivery Fee</span>
                            <span class="summary-value">$<?= number_format($order['delivery_fee'], 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Tax</span>
                            <span class="summary-value">$<?= number_format($order['tax_amount'], 2) ?></span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total</span>
                            <span>$<?= number_format($order['total'], 2) ?></span>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <a href="product.php" class="btn">Continue Shopping</a>
                        <a href="index.php" class="btn btn-dark">Back to Home</a>
                    </div>
                </div>
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
                    <a href="career.php">Careers</a>
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