<?php
require_once 'auth_functions.php';
requireLogin();
?>
<?php require_once 'config.php'; ?>

<?php
// Fetch featured products
$featured_products = [];
$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 6";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}

// Fetch special offers
$special_offers = [];
$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['old_price'] = number_format($row['price'] * 1.3, 2);
        $special_offers[] = $row;
    }
}

// Get all categories
$categories = [];
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Fields - Premium Grocery Delivery</title>
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
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('images/Background.jpg');
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 2rem;
        }

        .hero-content {
            max-width: 800px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin: 0 0.5rem;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Categories Section */
        .categories {
            padding: 5rem 2rem;
            background: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: var(--dark);
            font-weight: 700;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .category-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .category-card h3 {
            padding: 1.5rem;
            text-align: center;
            color: var(--dark);
            font-size: 1.3rem;
            font-weight: 600;
        }

        /* Featured Products */
        .featured-products {
            padding: 5rem 2rem;
            background: var(--light);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-info h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .product-info p {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .product-price {
            font-size: 1.3rem;
            color: var(--primary);
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .old-price {
            text-decoration: line-through;
            color: var(--gray);
            font-size: 1rem;
            margin-right: 0.5rem;
        }

        .add-to-cart {
            width: 100%;
            margin-top: 1rem;
            padding: 10px;
            font-weight: 600;
        }

        .rating {
            color: #f1c40f;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        /* Special Offers */
        .special-offers {
            padding: 5rem 2rem;
            background: white;
        }

        .offer-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .offer-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .offer-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .offer-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
            z-index: 1;
        }

        /* App Download Banner */
        .app-banner {
            background: var(--dark);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .app-banner h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .app-banner p {
            max-width: 600px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }

        .app-stores {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .app-store-btn {
            display: flex;
            align-items: center;
            background: black;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .app-store-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .app-store-btn i {
            font-size: 1.5rem;
            margin-right: 0.8rem;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 4rem 2rem 2rem;
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
        @media (max-width: 992px) {
            .hero-content h1 {
                font-size: 2.8rem;
            }
        }

        @media (max-width: 768px) {
            nav {
                padding: 0 1rem;
            }

            .nav-links {
                display: none;
            }

            .hero-content h1 {
                font-size: 2.2rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .app-stores {
                flex-direction: column;
                align-items: center;
            }

            .app-store-btn {
                width: 200px;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .hero {
                height: 400px;
            }

            .hero-content h1 {
                font-size: 1.8rem;
            }

            .btn {
                display: block;
                width: 80%;
                margin: 0.5rem auto;
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
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Groceries delivered to your door in minutes</h1>
                <p>Your premium destination for fresh, organic, and locally sourced groceries</p>
                <a href="product.php" class="btn">Shop Now</a>
                <a href="#offers" class="btn btn-outline">Today's Deals</a>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories">
            <div class="container">
                <h2 class="section-title">Shop by Category</h2>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): 
                        $category_slug = strtolower(str_replace(' ', '-', $category['category_name']));
                    ?>
                    <div class="category-card" onclick="goToProducts('<?= $category_slug ?>')">
                        <img src="images/categories/<?= $category_slug ?>.png" alt="<?= $category['category_name'] ?>">
                        <h3><?= $category['category_name'] ?></h3>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="featured-products">
            <div class="container">
                <h2 class="section-title">Popular Items</h2>
                <div class="products-grid">
                    <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <img src="<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>">
                        <div class="product-info">
                            <div class="rating">★★★★★</div>
                            <h3><?= $product['product_name'] ?></h3>
                            <p><?= $product['description'] ?></p>
                            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                            <button class="btn add-to-cart" onclick="addToCart(<?= $product['product_id'] ?>, '<?= addslashes($product['product_name']) ?>')">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Special Offers -->
        <section class="special-offers" id="offers">
            <div class="container">
                <h2 class="section-title">Today's Special Offers</h2>
                <div class="products-grid">
                    <?php foreach ($special_offers as $product): ?>
                    <div class="offer-card">
                        <span class="offer-badge">30% OFF</span>
                        <img src="<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>">
                        <div class="product-info">
                            <div class="rating">★★★★★</div>
                            <h3><?= $product['product_name'] ?></h3>
                            <p><?= $product['description'] ?></p>
                            <div class="product-price">
                                <span class="old-price">$<?= $product['old_price'] ?></span>
                                $<?= number_format($product['price'], 2) ?>
                            </div>
                            <button class="btn add-to-cart" onclick="addToCart(<?= $product['product_id'] ?>, '<?= addslashes($product['product_name']) ?>')">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- App Download Banner -->
        <section class="app-banner">
            <h2>Get the Fresh Fields app</h2>
            <p>Order groceries even faster and get exclusive app-only deals</p>
            <div class="app-stores">
                <a href="#" class="app-store-btn">
                    <i class="fab fa-apple"></i>
                    <div>
                        <div style="font-size: 0.7rem;">Download on the</div>
                        <div>App Store</div>
                    </div>
                </a>
                <a href="#" class="app-store-btn">
                    <i class="fab fa-google-play"></i>
                    <div>
                        <div style="font-size: 0.7rem;">Get it on</div>
                        <div>Google Play</div>
                    </div>
                </a>
            </div>
        </section>
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
                    <?php foreach ($categories as $category): 
                        $category_slug = strtolower(str_replace(' ', '-', $category['category_name']));
                    ?>
                    <a href="product.php?category=<?= $category_slug ?>"><?= $category['category_name'] ?></a>
                    <?php endforeach; ?>
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
        // Add product to cart
        function addToCart(productId, productName) {
            // Send AJAX request to update cart
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_to_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    document.getElementById('cart-count').textContent = response.cart_count;
                    alert(`${productName} added to cart!`);
                }
            };
            xhr.send(`product_id=${productId}`);
        }

        // Navigation functions
        function goToCart() {
            window.location.href = 'cart.php';
        }

        function goToProducts(category) {
            window.location.href = `product.php?category=${category}`;
        }
    </script>
</body>
</html>