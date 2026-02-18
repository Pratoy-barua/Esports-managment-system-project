<?php
/**
 * User Registration Handler
 */
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$conn = getConnection();

// Get form data
$full_name = sanitize($conn, $_POST['full_name']);
$username = sanitize($conn, $_POST['username']);
$email = sanitize($conn, $_POST['email']);
$phone = sanitize($conn, $_POST['phone']);
$gender = sanitize($conn, $_POST['gender']);
$date_of_birth = sanitize($conn, $_POST['date_of_birth']);
$profession = sanitize($conn, $_POST['profession']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validation
$errors = [];

// Check if passwords match
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Check password strength
if (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters";
}

// Ensure username starts with @
if (!str_starts_with($username, '@')) {
    $username = '@' . $username;
}

// Check if username exists
$sql = "SELECT user_id FROM users WHERE username = '$username'";
if (getSingleRow($conn, $sql)) {
    $errors[] = "Username already exists";
}

// Check if email exists
$sql = "SELECT user_id FROM users WHERE email = '$email'";
if (getSingleRow($conn, $sql)) {
    $errors[] = "Email already registered";
}

// If there are errors, redirect back
if (!empty($errors)) {
    $error_msg = implode(', ', $errors);
    header("Location: ../index.php?register=true&error=" . urlencode($error_msg));
    closeConnection($conn);
    exit();
}

// Hash password
$password_hash = md5($password); // Use password_hash() in production

// Insert user
$sql = "INSERT INTO users (full_name, username, email, phone, password_hash, gender, date_of_birth, profession, terms_agreed) 
        VALUES ('$full_name', '$username', '$email', '$phone', '$password_hash', '$gender', '$date_of_birth', '$profession', 1)";

if (executeQuery($conn, $sql)) {
    $user_id = $conn->insert_id;
    
    // Insert conditional data based on profession
    if ($profession === 'Student' && isset($_POST['university_id'])) {
        $university_id = (int)$_POST['university_id'];
        $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 'NULL';
        
        $sql = "INSERT INTO student_profiles (user_id, university_id, department_id) 
                VALUES ($user_id, $university_id, $department_id)";
        executeQuery($conn, $sql);
    }
    
    if ($profession === 'Job Holder' && isset($_POST['company_name'])) {
        $company_name = sanitize($conn, $_POST['company_name']);
        $designation = isset($_POST['designation']) ? sanitize($conn, $_POST['designation']) : '';
        
        $sql = "INSERT INTO job_holder_profiles (user_id, company_name, designation) 
                VALUES ($user_id, '$company_name', '$designation')";
        executeQuery($conn, $sql);
    }
    
    // Send welcome notification
    $notification_sql = "INSERT INTO notifications (user_id, title, message, notification_type) 
                        VALUES ($user_id, 'Welcome to ESportsHub!', 'Your account has been created successfully. Start exploring tournaments and teams!', 'System')";
    executeQuery($conn, $notification_sql);
    
    // Success - redirect to login
    header("Location: ../index.php?success=" . urlencode("Registration successful! Please sign in."));
} else {
    header("Location: ../index.php?register=true&error=" . urlencode("Registration failed. Please try again."));
}

closeConnection($conn);
exit();
?>
