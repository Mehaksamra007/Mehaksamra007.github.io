<?php
require_once 'config.php';
require_once 'auth_functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : null;
    $subject = $conn->real_escape_string(trim($_POST['subject']));
    $message = $conn->real_escape_string(trim($_POST['message']));
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['contact_error'] = "Please fill in all required fields";
        header("Location: contact.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Please enter a valid email address";
        header("Location: contact.php");
        exit;
    }

    // Insert into database
    $sql = "INSERT INTO contact_messages (user_id, name, email, phone, subject, message) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $user_id, $name, $email, $phone, $subject, $message);
    
    if ($stmt->execute()) {
        $_SESSION['contact_success'] = "Thank you for your message! We'll get back to you soon.";
    } else {
        $_SESSION['contact_error'] = "There was an error submitting your message. Please try again.";
    }

    $stmt->close();
    header("Location: contact.php");
    exit;
} else {
    // If someone tries to access this page directly
    header("Location: contact.php");
    exit;
}