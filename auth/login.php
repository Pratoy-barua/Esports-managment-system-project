<?php

session_start();
require_once '../config/database.php';
require_once '../config/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$conn = getConnection();

// Get form data
$username = sanitize($conn, $_POST['username']);
$password = $_POST['password'];

// Hash password for comparison
$password_hash = md5($password); // Use password_verify() in production

// Check if username or email
$sql = "SELECT * FROM users WHERE (username = '$username' OR email = '$username') AND password_hash = '$password_hash'";
$user = getSingleRow($conn, $sql);

if ($user) {
    // Check if user is active
    if ($user['is_active'] != 1) {
        header("Location: ../index.php?error=" . urlencode("Your account has been suspended. Please contact admin."));
        closeConnection($conn);
        exit();
    }
    
    // Set session
    setUserSession($user);
    
    // Check subscription status for students
    if ($user['profession'] === 'Student') {
        checkSubscriptionStatus($conn, $user['user_id']);
    }
    
    // Redirect based on role
    if ($user['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../user/dashboard.php");
    }
} else {
    header("Location: ../index.php?error=" . urlencode("Invalid username/email or password"));
}

closeConnection($conn);
exit();
?>
