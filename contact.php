<?php
require_once 'auth_functions.php';
requireLogin();
require_once 'config.php';

// Handle form submission messages
$success_message = '';
$error_message = '';

if (isset($_SESSION['contact_success'])) {
    $success_message = $_SESSION['contact_success'];
    unset($_SESSION['contact_success']);
}

if (isset($_SESSION['contact_error'])) {
    $error_message = $_SESSION['contact_error'];
    unset($_SESSION['contact_error']);
}

// Get categories for footer
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
    <title>Contact Us - Fresh Fields</title>
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

        /* Contact Section */
        .contact-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }

        @media (max-width: 992px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }

        .contact-info {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .contact-form {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-icon {
            background: var(--light);
            color: var(--primary);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .info-content h3 {
            color: var(--dark);
            margin-bottom: 0.3rem;
            font-size: 1.1rem;
        }

        .info-content p, 
        .info-content a {
            color: var(--gray);
            text-decoration: none;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .info-content a:hover {
            color: var(--primary);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .social-links a {
            color: white;
            background: var(--dark);
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
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
            font-size: 1rem;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Map */
        .map-container {
            margin-top: 2rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .map-container iframe {
            width: 100%;
            height: 300px;
            border: none;
        }

        /* Business Hours */
        .hours-container {
            margin-top: 2rem;
        }

        .hours-table {
            width: 100%;
            border-collapse: collapse;
        }

        .hours-table tr {
            border-bottom: 1px solid #eee;
        }

        .hours-table tr:last-child {
            border-bottom: none;
        }

        .hours-table th,
        .hours-table td {
            padding: 12px 0;
            text-align: left;
        }

        .hours-table th {
            color: var(--dark);
            font-weight: 500;
        }

        .hours-table td {
            color: var(--gray);
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

        .alert {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            animation: slideIn 0.3s ease-out, fadeOut 0.5s ease-in 3s forwards;
            max-width: 90%;
            width: auto;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert i {
            font-size: 1.2rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
                visibility: hidden;
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
                <li><a href="contact.php" class="active">Contact</a></li>
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
                    <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?= htmlspecialchars($success_message) ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div><?= htmlspecialchars($error_message) ?></div>
            </div>
        <?php endif; ?>
        
        <section class="page-header">
            <div>
                <h1 class="page-title">Contact Us</h1>
                <p class="page-subtitle">We'd love to hear from you! Get in touch with our team.</p>
            </div>
        </section>

        <div class="contact-container">
            <div class="contact-info">
                <h2 class="section-title">Contact Information</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Our Location</h3>
                        <p>20 Columbia St W, Waterloo, Ontario, N2L 3K3</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h3>Phone Numbers</h3>
                        <a href="tel:4378711007">(437) 871-1007</a>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>Email Address</h3>
                        <a href="mailto:info@freshfields.com">info@freshfields.com</a>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h3>Customer Support Hours</h3>
                        <p>Monday - Friday: 8am - 8pm</p>
                        <p>Saturday - Sunday: 9am - 6pm</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
                
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2895.658058040101!2d-80.5297189242653!3d43.47862697110546!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x882bf1565f1841c9%3A0x9a8c4bca8c9a8a8!2s20%20Columbia%20St%20W%2C%20Waterloo%2C%20ON%20N2L%203K3%2C%20Canada!5e0!3m2!1sen!2sus!4v1718762345678!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
            
            <div class="contact-form">
                <h2 class="section-title">Send Us a Message</h2>
                <form id="contactForm" method="POST" action="submit_contact.php">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" class="form-control" required>
                            <option value="" disabled selected>Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="order">Order Question</option>
                            <option value="delivery">Delivery Issue</option>
                            <option value="product">Product Question</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" class="form-control" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-block">Send Message</button>
                </form>
                
                <div class="hours-container">
                    <h3 class="section-title">Store Hours</h3>
                    <table class="hours-table">
                        <tr>
                            <th>Monday - Friday</th>
                            <td>7:00 AM - 10:00 PM</td>
                        </tr>
                        <tr>
                            <th>Saturday</th>
                            <td>8:00 AM - 9:00 PM</td>
                        </tr>
                        <tr>
                            <th>Sunday</th>
                            <td>8:00 AM - 8:00 PM</td>
                        </tr>
                    </table>
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
        // Navigate to cart
        function goToCart() {
            window.location.href = 'cart.php';
        }

        // Handle contact form submission (client-side validation)
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value.trim();

            if (!name || !email || !subject || !message) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }

            return true;
        });
    </script>
</body>
</html>