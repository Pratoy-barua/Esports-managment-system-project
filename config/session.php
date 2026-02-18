<?php


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is student
function isStudent() {
    return isset($_SESSION['profession']) && $_SESSION['profession'] === 'Student';
}

// Check if user has active subscription
function hasActiveSubscription() {
    return isset($_SESSION['has_subscription']) && $_SESSION['has_subscription'] === true;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /index.php?login=required");
        exit();
    }
}

// Require admin (Fixed and cleaned up)
function requireAdmin() {
    requireLogin(); // Prothome login check korbe
    if (!isAdmin()) {
        header("Location: /user/dashboard.php?error=access_denied");
        exit();
    }
}

// Require student with subscription
function requireStudentSubscription() {
    requireLogin();
    if (!isStudent() || !hasActiveSubscription()) {
        header("Location: /user/subscription.php?error=subscription_required");
        exit();
    }
}

// Set user session
function setUserSession($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['profession'] = $user['profession'];
    $_SESSION['profile_image'] = $user['profile_image'];
    
    // Database connection for last login update
    require_once '../config/database.php';
    $conn = getConnection();
    
    // Using Prepared Statements for security
    $user_id = $user['user_id'];
    $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    closeConnection($conn);
}

// Check and update subscription status
function checkSubscriptionStatus($conn, $user_id) {
    // Prepared statement use kora hoyeche SQL Injection rodhe
    $sql = "SELECT * FROM subscriptions WHERE user_id = ? AND is_active = 1 ORDER BY end_date DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $subscription = mysqli_fetch_assoc($result);
    
    if ($subscription) {
        $end_date = strtotime($subscription['end_date']);
        $today = strtotime(date('Y-m-d'));
        
        if ($today > $end_date) {
            // Subscription expired
            $subscription_id = $subscription['subscription_id'];
            $update_sql = "UPDATE subscriptions SET is_active = 0 WHERE subscription_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $subscription_id);
            mysqli_stmt_execute($update_stmt);
            
            $_SESSION['has_subscription'] = false;
            return false;
        } else {
            $_SESSION['has_subscription'] = true;
            $_SESSION['subscription_end_date'] = $subscription['end_date'];
            return true;
        }
    }
    
    $_SESSION['has_subscription'] = false;
    return false;
}

// Logout user
function logout() {
    session_unset();
    session_destroy();
    header("Location: /index.php");
    exit();
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>