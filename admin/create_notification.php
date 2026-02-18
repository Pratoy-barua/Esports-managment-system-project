<?php

session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// session.php theke requireAdmin function check kora hochche
requireAdmin();

$conn = getConnection();
$success = '';
$error = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $short_message = trim($_POST['short_message']);
    $full_message = trim($_POST['full_message']);
    $type = $_POST['type'];
    $target_type = $_POST['target_type'];
    $target_value = !empty($_POST['target_value']) ? trim($_POST['target_value']) : null;
    $redirect_url = !empty($_POST['redirect_url']) ? trim($_POST['redirect_url']) : null;
    $admin_id = $_SESSION['user_id'];

    if ($title === '' || $short_message === '' || $full_message === '') {
        $error = "All required fields must be filled.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO admin_notifications
            (title, short_message, full_message, type, target_type, target_value, redirect_url, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssssi",
            $title,
            $short_message,
            $full_message,
            $type,
            $target_type,
            $target_value,
            $redirect_url,
            $admin_id
        );

        if ($stmt->execute()) {

            // 🔐 Log admin action
            $log = $conn->prepare("
                INSERT INTO admin_logs
                (admin_id, action, target_type, target_id, description, ip_address)
                VALUES (?, 'CREATE', 'Notification', ?, ?, ?)
            ");

            $desc = "Created notification: {$title}";
            $ip = $_SERVER['REMOTE_ADDR'];
            $notif_id = $stmt->insert_id;

            $log->bind_param("iiss", $admin_id, $notif_id, $desc, $ip);
            $log->execute();

            $success = "Notification created successfully. Ready to send.";
        } else {
            $error = "Failed to create notification.";
        }
    }
}

// Admin info for header
$admin_name = $_SESSION['full_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Create Notification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        
        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }
        
        .admin-logo h2 {
            color: #818cf8;
            font-size: 22px;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #334155;
            color: #818cf8;
        }
        
        .admin-nav a i {
            margin-right: 12px;
            width: 20px;
        }
        
        /* CONTENT AREA */
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            font-size: 28px;
            color: #f1f5f9;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #818cf8;
        }

        /* FORM CARD STYLING */
        .form-card {
            background: #1e293b;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #334155;
            max-width: 900px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #94a3b8;
            font-size: 14px;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            background: #0f172a;
            color: #e2e8f0;
            border: 1px solid #334155;
            border-radius: 6px;
            font-size: 15px;
            transition: 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #818cf8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(129, 140, 248, 0.2);
        }

        .btn-submit {
            margin-top: 25px;
            padding: 12px 25px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success { background: rgba(6, 78, 59, 0.8); color: #a7f3d0; border: 1px solid #059669; }
        .alert-error { background: rgba(127, 29, 29, 0.8); color: #fecaca; border: 1px solid #dc2626; }
    </style>
</head>

<body>

<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2><i class="fas fa-gamepad"></i> ESportsHub</h2>
            <p style="font-size: 12px; color: #64748b;">Admin Panel</p>
        </div>
        
        <nav class="admin-nav">
            <a href="dashboard.php">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="users.php">
                <i class="fas fa-users"></i> User Management
            </a>
            <a href="tournaments.php">
                <i class="fas fa-trophy"></i> Tournaments
            </a>
            <a href="hosting.php">
                <i class="fas fa-calendar-check"></i> Hosting Requests
            </a>
            <a href="teams.php">
                <i class="fas fa-users-gear"></i> Teams
            </a>
            <a href="products.php">
                <i class="fas fa-box"></i> Products & Orders
            </a>
            <a href="subscriptions.php">
                <i class="fas fa-crown"></i> Subscriptions
            </a>
            <a href="messages.php">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="notifications.php" class="active">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="logs.php">
                <i class="fas fa-history"></i> Activity Logs
            </a>
            <a href="../auth/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <div>
                <h1>Create Notification</h1>
                <p style="color:#64748b;">Compose a new system alert for users</p>
            </div>
            <div class="admin-user">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div style="font-size: 13px; color: #64748b;">Administrator</div>
                </div>
                <img src="../assets/images/default-avatar.png" alt="Admin">
            </div>
        </div>

        <div class="form-card">
            <?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

            <form method="POST">
                <label>Notification Title *</label>
                <input type="text" name="title" placeholder="Enter headline" required>

                <label>Short Message *</label>
                <input type="text" name="short_message" placeholder="Brief summary for preview" required>

                <label>Full Message *</label>
                <textarea name="full_message" rows="5" placeholder="Detailed notification content" required></textarea>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label>Notification Type *</label>
                        <select name="type" required>
                            <option value="System">System</option>
                            <option value="Tournament">Tournament</option>
                            <option value="Hosting">Hosting</option>
                            <option value="Subscription">Subscription</option>
                            <option value="Product">Product</option>
                            <option value="Order">Order</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                    <div>
                        <label>Target Audience *</label>
                        <select name="target_type" required>
                            <option value="ALL">All Users</option>
                            <option value="STUDENTS">Students Only</option>
                            <option value="SUBSCRIBED_STUDENTS">Subscribed Students</option>
                            <option value="UNIVERSITY">Specific University</option>
                            <option value="PROFESSION">Specific Profession</option>
                            <option value="INDIVIDUAL">Individual User</option>
                        </select>
                    </div>
                </div>

                <label>Target Value (optional)</label>
                <input type="text" name="target_value" placeholder="University name / username / profession">

                <label>Redirect URL (optional)</label>
                <input type="text" name="redirect_url" placeholder="e.g. /tournaments/view.php?id=5">

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Save Notification
                </button>
                <a href="notifications.php" style="margin-left: 15px; color: #94a3b8; text-decoration: none; font-size: 14px;">Cancel</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>