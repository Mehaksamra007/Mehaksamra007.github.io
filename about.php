<?php
require_once 'config.php';
require_once 'auth_functions.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Fresh Fields</title>
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

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        /* About Sections */
        .about-section {
            max-width: 1200px;
            margin: 0 auto 3rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: center;
        }

        @media (min-width: 768px) {
            .about-section {
                grid-template-columns: 1fr 1fr;
            }
            
            .about-section.reverse {
                grid-template-columns: 1fr 1fr;
                direction: rtl;
            }
            
            .about-section.reverse > * {
                direction: ltr;
            }
        }

        .about-image {
            width: 100%;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .about-content {
            padding: 1rem;
        }

        .about-content h2 {
            color: var(--dark);
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .about-content p {
            color: var(--gray);
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        /* Team Section */
        .team-section {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            padding: 2rem 0;
        }

        .team-section h2 {
            color: var(--dark);
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .team-member {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 5px solid var(--light);
        }

        .team-member h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .team-member p {
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .social-links a {
            color: var(--primary);
            background: var(--light);
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
            color: white;
        }

        /* Mission Section */
        .mission-section {
            background: var(--primary);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            margin: 3rem 0;
        }

        .mission-section h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .mission-section p {
            max-width: 800px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.7;
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

        .footer-social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .footer-social-links a {
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

        .footer-social-links a:hover {
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
        }

        @media (max-width: 576px) {
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
                <li><a href="about.php" class="active">About</a></li>
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
                <div class="cart-icon" onclick="window.location.href='cart.php'">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?= array_sum($_SESSION['cart']) ?></span>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="page-header">
            <h1 class="page-title">Our Story</h1>
            <p class="page-subtitle">Fresh Fields was born from a simple idea: to make fresh, organic groceries accessible to everyone while supporting local farmers and producers.</p>
        </div>

        <section class="about-section">
            <img src="images/about/farm.jpg" alt="Our Farm" class="about-image">
            <div class="about-content">
                <h2>Our Beginnings</h2>
                <p>Founded in 2015 by a group of local farmers and food enthusiasts, Fresh Fields started as a small farmers' market stall in Farm City. Our commitment to quality and sustainability quickly gained us a loyal following.</p>
                <p>What began as a weekend market operation has grown into a thriving grocery delivery service serving the entire metropolitan area, while still maintaining our core values of freshness, quality, and community.</p>
            </div>
        </section>

        <section class="about-section reverse">
            <img src="images/about/delivery.jpg" alt="Our Delivery Service" class="about-image">
            <div class="about-content">
                <h2>Our Growth</h2>
                <p>In 2025, we launched our delivery service to bring farm-fresh products directly to our customers' doors. This allowed us to expand our reach while maintaining the personal touch that made us successful.</p>
                <p>Today, we partner with over 200 local farms and producers to offer the widest selection of organic and sustainable products in the region, all delivered with the same care we'd give to our own families.</p>
            </div>
        </section>

        <section class="mission-section">
            <h2>Our Mission</h2>
            <p>At Fresh Fields, we believe everyone deserves access to fresh, healthy food. We're committed to sustainable farming practices, fair wages for our workers, and building a food system that benefits both people and the planet.</p>
        </section>

        <section class="team-section">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="images/team/mehakdeep-singh.jpg" alt="John Doe">
                    <h3>Mehakdeep Singh</h3>
                    <p>Founder & CEO</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team/harsha-suman.jpg" alt="Harsha Suman">
                    <h3>Harsha Suman</h3>
                    <p>Head of Operations</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team/shubham-bharti.jpg" alt="Shubham Bharti">
                    <h3>Shubham Bharti</h3>
                    <p>Head of Procurement</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team/Mba.png" alt="Ifeatu Christian Mba">
                    <h3>Ifeatu Christian Mba</h3>
                    <p>Customer Experience</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Fresh Fields</h3>
                    <p>We're committed to bringing you the freshest, highest quality groceries at affordable prices.</p>
                    <div class="footer-social-links">
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
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Any client-side JavaScript can go here
        });
    </script>
</body>
</html>