<?php

session_start();

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/database.php';

$error = '';
$success = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Query admin user
        $stmt = $conn->prepare("SELECT user_id, full_name, username, email, role, account_status FROM users WHERE email = ? AND password_hash = MD5(?) AND role = 'admin'");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if ($admin['account_status'] !== 'active') {
                $error = 'Your admin account has been suspended';
            } else {
                // Set session
                $_SESSION['user_id'] = $admin['user_id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['full_name'] = $admin['full_name'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['role'] = $admin['role'];
                
                // Log admin login
                $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action_type, affected_entity, action_details, ip_address) VALUES (?, 'login', 'admin', 'Admin logged in', ?)");
                $ip = $_SERVER['REMOTE_ADDR'];
                $log_stmt->bind_param("is", $admin['user_id'], $ip);
                $log_stmt->execute();
                
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid admin credentials';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .admin-login-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .admin-login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .admin-login-header i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .admin-login-header h1 {
            font-size: 28px;
            color: #2d3748;
            margin: 0 0 10px 0;
        }
        
        .admin-login-header p {
            color: #718096;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-admin-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-admin-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-to-home a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-to-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-box">
            <div class="admin-login-header">
                <i class="fas fa-shield-halved"></i>
                <h1>Admin Portal</h1>
                <p>ESportsHub Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Admin Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="adminalvi@gmail.com"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter admin password"
                        required
                    >
                </div>
                
                <button type="submit" name="login" class="btn-admin-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In as Admin
                </button>
            </form>
            
            <div class="back-to-home">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Back to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html>
