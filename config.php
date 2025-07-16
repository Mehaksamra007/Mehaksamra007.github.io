<?php
// Error reporting (enable in development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'freshfields');

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Timezone setting
date_default_timezone_set('America/Toronto');

// Session configuration
session_start([
    'cookie_lifetime' => 86400, // 1 day
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize user session if not exists
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = null;
    $_SESSION['user_email'] = null;
    $_SESSION['user_name'] = null;
}

// Regenerate session ID for security
if (!isset($_SESSION['auth_init'])) {
    session_regenerate_id(true);
    $_SESSION['auth_init'] = true;
}
?>