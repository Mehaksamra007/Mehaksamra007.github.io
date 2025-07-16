<?php
require_once 'auth_functions.php';
requireLogin();
?>
<?php require_once 'config.php'; ?>

<?php
// Get all products
$products = [];
$sql = "SELECT product_id, product_name, price, image_url FROM products";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[$row['product_id']] = $row;
    }
}

// Get cart items with product details
$cart_items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $product = $products[$product_id];
        $item_total = $product['price'] * $quantity;
        $subtotal += $item_total;
        
        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image' => $product['image_url'],
            'total' => $item_total
        ];
    }
}

$delivery_fee = 5.99;
$total = $subtotal + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Fresh Fields</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS from Cart.html */
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

        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 992px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
        }

        /* Cart Items */
        .cart-items {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .cart-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            color: var(--dark);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .item-info img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .item-details p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
            color: var(--dark);
        }

        .item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--light);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background: var(--primary);
            color: white;
        }

        .quantity-input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.3rem;
        }

        .remove-item {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .remove-item:hover {
            transform: scale(1.2);
        }

        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .summary-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
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

        .discount-row {
            display: flex;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .discount-input {
            flex: 1;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .discount-btn {
            background: var(--dark);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 1.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .discount-btn:hover {
            background: var(--primary);
        }

        .discount-applied {
            color: var(--primary);
            font-weight: 600;
            margin-top: 0.5rem;
            display: none;
        }

        .discount-error {
            color: var(--danger);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }

        .total-row {
            border-top: 1px solid #eee;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
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

        .empty-cart {
            text-align: center;
            padding: 4rem 0;
        }

        .empty-cart i {
            font-size: 5rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        .empty-cart h2 {
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: var(--gray);
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
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

            .cart-header {
                display: none;
            }

            .cart-item {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 1.5rem 0;
                position: relative;
            }

            .item-info {
                grid-column: 1 / -1;
            }

            .item-price {
                text-align: right;
            }

            .item-quantity {
                justify-content: flex-end;
            }

            .item-total {
                text-align: right;
                font-weight: bold;
            }

            .remove-item {
                position: absolute;
                top: 1.5rem;
                right: 0;
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
}        }
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
            <h1 class="page-title">Your Shopping Cart</h1>
            
            <div id="cart-content">
                <?php if (count($cart_items) === 0): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added anything to your cart yet. Start shopping to fill it with fresh groceries!</p>
                    <a href="product.php" class="btn">Shop Now</a>
                </div>
                <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <div class="cart-header">
                            <div>Product</div>
                            <div>Price</div>
                            <div>Quantity</div>
                            <div>Total</div>
                        </div>
                        <div id="cart-items-list">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-info">
                                    <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                                    <div class="item-details">
                                        <h3><?= $item['name'] ?></h3>
                                    </div>
                                </div>
                                <div class="item-price">$<?= number_format($item['price'], 2) ?></div>
                                <div class="item-quantity">
                                    <button class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">-</button>
                                    <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" min="1" 
                                        onchange="updateQuantity(<?= $item['id'] ?>, parseInt(this.value))">
                                    <button class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                                </div>
                                <div class="item-total">$<?= number_format($item['total'], 2) ?></div>
                                <button class="remove-item" onclick="removeItem(<?= $item['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <h3 class="summary-title">Order Summary</h3>
                        
                        <div class="summary-row">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value" id="subtotal">$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Delivery</span>
                            <span class="summary-value">$<?= number_format($delivery_fee, 2) ?></span>
                        </div>
                        
                        <div class="discount-row">
                            <input type="text" class="discount-input" id="promo-code" placeholder="Promo Code">
                            <button class="discount-btn" onclick="applyPromoCode()">Apply</button>
                        </div>
                        <div class="discount-applied" id="discount-applied">
                            <span id="discount-text"></span> applied
                        </div>
                        <div class="discount-error" id="discount-error">
                            Invalid promo code
                        </div>
                        
                        <div class="summary-row total-row">
                            <span>Total</span>
                            <span id="total">$<?= number_format($total, 2) ?></span>
                        </div>
                        
                        <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
                    </div>
                </div>
                <?php endif; ?>
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
        // Valid promo codes
        const validPromoCodes = [
            { code: "FRESH10", discount: 0.1, type: "percent", description: "10% off" },
            { code: "FIELDS20", discount: 0.2, type: "percent", description: "20% off" },
            { code: "SAVE15", discount: 15, type: "fixed", description: "$15 off" },
            { code: "FREESHIP", discount: 5.99, type: "fixed", description: "Free shipping" }
        ];

        // Update quantity
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) newQuantity = 1;
            
            // Send AJAX request to update quantity
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    location.reload(); // Refresh page to show updated cart
                }
            };
            xhr.send(`product_id=${productId}&quantity=${newQuantity}`);
        }

        // Remove item
        function removeItem(productId) {
            // Send AJAX request to remove item
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'remove_from_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    location.reload(); // Refresh page to show updated cart
                }
            };
            xhr.send(`product_id=${productId}`);
        }

        // Apply promo code
        function applyPromoCode() {
            const promoCode = document.getElementById('promo-code').value.trim();
            const discountApplied = document.getElementById('discount-applied');
            const discountError = document.getElementById('discount-error');
            
            // Reset messages
            discountApplied.style.display = 'none';
            discountError.style.display = 'none';
            
            // Check if valid promo code
            const promo = validPromoCodes.find(p => p.code === promoCode.toUpperCase());
            
            if (promo) {
                document.getElementById('discount-text').textContent = promo.description;
                discountApplied.style.display = 'block';
                
                // Calculate discount (simplified for demo)
                let discountAmount = 0;
                if (promo.type === 'percent') {
                    discountAmount = <?= $subtotal ?> * promo.discount;
                } else {
                    discountAmount = promo.discount;
                }
                
                // Update total display
                const totalElement = document.getElementById('total');
                const newTotal = <?= $total ?> - discountAmount;
                totalElement.textContent = '$' + newTotal.toFixed(2);
            } else {
                discountError.style.display = 'block';
            }
        }

        // Checkout function
        function checkout() {
            window.location.href = 'checkout.php';
        }

        // Navigate to cart
        function goToCart() {
            window.location.href = 'cart.php';
        }
    </script>
</body>
</html>