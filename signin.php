<?php
require_once 'config.php';
require_once 'auth_functions.php';

// Initialize variables
$error = '';
$success = '';
$redirect = $_GET['redirect'] ?? 'index.php';
$active_tab = isset($_GET['register']) ? 'register' : 'login';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (isset($_POST['register'])) {
        // Registration process
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone'] ?? '');
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $error = "Please fill in all required fields.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "Email already exists. Please use a different email.";
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $phone);
                
                if ($stmt->execute()) {
                    $success = "Account created successfully! Please sign in.";
                    $active_tab = 'login'; // Switch to login tab after registration
                    // Pre-fill the email in the login form
                    $_POST['login_email'] = $email;
                } else {
                    $error = "Error creating account. Please try again.";
                }
            }
        }
    } else {
        // Login process
        $stmt = $conn->prepare("SELECT user_id, full_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                loginUser($user['user_id'], $email, $user['full_name']);
                header("Location: $redirect");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Fresh Fields</title>
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

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('images/Background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            width: 100%;
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

        /* Main Content */
        main {
            margin-top: 80px;
            padding: 2rem 0;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-title {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .auth-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
        }

        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .auth-tab {
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }

        .auth-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .auth-form {
            display: none;
        }

        .auth-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
        }

        .auth-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
        }

        .auth-btn:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-hover);
        }

        .error-message {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .success-message {
            color: var(--primary);
            background: rgba(46, 204, 113, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 1rem 0;
            text-align: center;
            width: 100%;
        }

        .footer-bottom {
            font-size: 0.9rem;
            color: #bdc3c7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .auth-container {
                padding: 1.5rem;
                width: 90%;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.8rem;
            }
            
            .auth-tab {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
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
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h1 class="page-title">Welcome to Fresh Fields</h1>
            
            <div class="auth-container">
                <?php if ($error): ?>
                <div class="error-message"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success-message"><?= $success ?></div>
                <?php endif; ?>
                
                <div class="auth-tabs">
                    <div class="auth-tab <?= $active_tab === 'login' ? 'active' : '' ?>" onclick="showTab('login')">Sign In</div>
                    <div class="auth-tab <?= $active_tab === 'register' ? 'active' : '' ?>" onclick="showTab('register')">Register</div>
                </div>
                
                <form id="login-form" class="auth-form <?= $active_tab === 'login' ? 'active' : '' ?>" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= isset($_POST['login_email']) ? htmlspecialchars($_POST['login_email']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="auth-btn">Sign In</button>
                </form>
                
                <form id="register-form" class="auth-form <?= $active_tab === 'register' ? 'active' : '' ?>" method="POST">
                    <input type="hidden" name="register" value="1">
                    <div class="form-group">
                        <label for="reg-name">Full Name</label>
                        <input type="text" id="reg-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-email">Email</label>
                        <input type="email" id="reg-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-phone">Phone Number (Optional)</label>
                        <input type="tel" id="reg-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="reg-password">Password (min 8 characters)</label>
                        <input type="password" id="reg-password" name="password" required minlength="8">
                    </div>
                    <button type="submit" class="auth-btn">Create Account</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 Fresh Fields. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Tab switching
        function showTab(tabName) {
            // Update tabs
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.toggle('active', tab.textContent.toLowerCase().includes(tabName));
            });
            
            // Update forms
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.toggle('active', form.id.includes(tabName));
            });
            
            // Update URL without reload
            const url = new URL(window.location);
            if (tabName === 'register') {
                url.searchParams.set('register', '1');
            } else {
                url.searchParams.delete('register');
            }
            window.history.replaceState({}, '', url);
        }
        
        // Initialize based on URL
        if (window.location.search.includes('register')) {
            showTab('register');
        }
    </script>
</body>
</html>