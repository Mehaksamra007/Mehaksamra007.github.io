<?php
require_once 'auth_functions.php';
requireLogin();
?>
<?php require_once 'config.php'; ?>

<?php
// Get category filter
$category_filter = '';
if (isset($_GET['category'])) {
    $category_name = str_replace('-', ' ', $_GET['category']);
    $sql = "SELECT category_id FROM categories WHERE category_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $category_filter = "WHERE p.category_id = " . $row['category_id'];
    }
}

// Fetch products
$sql = "SELECT p.*, c.category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        $category_filter
        ORDER BY p.product_id";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
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
    <title>Products - Fresh Fields</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS from Product.html */
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
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('images/Background.jpg');
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 2rem;
        }

        .page-title {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Filters and Search */
        .filters-container {
            background: white;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }

        .filter-btn {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        /* Products Grid */
        .products-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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

        .product-category {
            background: var(--light);
            color: var(--dark);
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 0.8rem;
            font-weight: 500;
        }

        .product-info h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .product-info p {
            color: var(--gray);
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
        }

        .product-price {
            font-size: 1.4rem;
            color: var(--primary);
            font-weight: bold;
            margin: 0.8rem 0;
        }

        .rating {
            color: #f1c40f;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 12px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-block;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }

        .btn:hover {
            background: var(--primary-dark);
        }

        .no-products {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 1200px;
            margin: 0 auto;
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
        @media (max-width: 992px) {
            .page-title {
                font-size: 2.5rem;
            }
        }

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

            .page-subtitle {
                font-size: 1rem;
            }

            .filters-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                height: 250px;
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
                <li><a href="product.php" class="active">Products</a></li>
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
        <section class="page-header">
            <div>
                <h1 class="page-title">Our Products</h1>
                <p class="page-subtitle">Discover our wide selection of fresh, organic, and premium groceries</p>
            </div>
        </section>

        <div class="filters-container">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Search products..." onkeyup="filterProducts()">
            </div>
            <div class="category-filters">
                <button class="filter-btn <?= !isset($_GET['category']) ? 'active' : '' ?>" onclick="filterByCategory('all')">All Products</button>
                <?php foreach ($categories as $category): 
                    $category_slug = strtolower(str_replace(' ', '-', $category['category_name']));
                    $is_active = isset($_GET['category']) && $_GET['category'] === $category_slug;
                ?>
                <button class="filter-btn <?= $is_active ? 'active' : '' ?>" onclick="filterByCategory('<?= $category_slug ?>')">
                    <?= $category['category_name'] ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="products-container" id="products-container">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>">
                    <div class="product-info">
                        <span class="product-category"><?= $product['category_name'] ?></span>
                        <div class="rating">★★★★★</div>
                        <h3><?= $product['product_name'] ?></h3>
                        <p><?= $product['description'] ?></p>
                        <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                        <button class="btn" onclick="addToCart(<?= $product['product_id'] ?>, '<?= addslashes($product['product_name']) ?>')">
                            Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <p>No products found matching your criteria.</p>
                </div>
            <?php endif; ?>
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

        // Navigate to cart
        function goToCart() {
            window.location.href = 'cart.php';
        }

        // Filter products by category
        function filterByCategory(category) {
            if (category === 'all') {
                window.location.href = 'product.php';
            } else {
                window.location.href = `product.php?category=${category}`;
            }
        }

        // Filter products by search term
        function filterProducts() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>