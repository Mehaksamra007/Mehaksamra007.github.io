<?php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null;
}

function requireLogin($redirect = 'signin.php') {
    if (!isLoggedIn()) {
        $current_url = $_SERVER['REQUEST_URI'];
        header("Location: $redirect?redirect=" . urlencode($current_url));
        exit;
    }
}

function loginUser($user_id, $email, $name) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    session_regenerate_id(true);
}

function logoutUser() {
    $_SESSION = array();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}
?>