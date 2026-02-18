<?php
/**
 * Admin - User Detail
 * View single user full profile
 */

session_start();
require_once '../config/database.php';

$conn = getConnection();

// Admin verify
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Validate user id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$user_id = (int) $_GET['id'];

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'user'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: users.php');
    exit;
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Details - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin:0; background:#0f172a; color:#e2e8f0; font-family:'Segoe UI',sans-serif; }
        .admin-layout { display:flex; min-height:100vh; }

        /* Sidebar — SAME AS DASHBOARD */
        .admin-sidebar {
            width:260px;
            background:#1e293b;
            padding:20px;
            position:fixed;
            height:100vh;
            overflow-y:auto;
        }
        .admin-logo {
            text-align:center;
            padding:20px 0;
            border-bottom:1px solid #334155;
            margin-bottom:20px;
        }
        .admin-logo h2 { color:#818cf8; font-size:22px; }
        .admin-nav a {
            display:flex;
            align-items:center;
            padding:12px 16px;
            color:#cbd5e1;
            text-decoration:none;
            border-radius:8px;
            margin-bottom:5px;
        }
        .admin-nav a:hover,
        .admin-nav a.active {
            background:#334155;
            color:#818cf8;
        }
        .admin-nav a i { margin-right:12px; width:20px; }

        /* Content */
        .admin-content {
            margin-left:260px;
            flex:1;
            padding:30px;
        }
        .card {
            background:#1e293b;
            border-radius:12px;
            padding:25px;
            max-width:700px;
        }
        .row { display:flex; margin-bottom:15px; }
        .label { width:180px; color:#94a3b8; }
        .value { font-weight:600; }
        .back-btn {
            display:inline-block;
            margin-top:20px;
            background:#3b82f6;
            padding:10px 16px;
            border-radius:8px;
            color:white;
            text-decoration:none;
        }
    </style>
</head>
<body>

<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2><i class="fas fa-gamepad"></i> ESportsHub</h2>
            <p style="font-size:12px;color:#64748b;">Admin Panel</p>
        </div>

        <nav class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="users.php" class="active"><i class="fas fa-users"></i> User Management</a>
            <a href="tournaments.php"><i class="fas fa-trophy"></i> Tournaments</a>
            <a href="hosting.php"><i class="fas fa-calendar-check"></i> Hosting Requests</a>
            <a href="teams.php"><i class="fas fa-users-gear"></i> Teams</a>
            <a href="products.php"><i class="fas fa-box"></i> Products & Orders</a>
            <a href="subscriptions.php"><i class="fas fa-crown"></i> Subscriptions</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            <a href="logs.php"><i class="fas fa-history"></i> Activity Logs</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <h1>User Details</h1>

        <div class="card">
            <div class="row"><div class="label">Full Name</div><div class="value"><?php echo htmlspecialchars($user['full_name']); ?></div></div>
            <div class="row"><div class="label">Username</div><div class="value"><?php echo htmlspecialchars($user['username']); ?></div></div>
            <div class="row"><div class="label">Email</div><div class="value"><?php echo htmlspecialchars($user['email']); ?></div></div>
            <div class="row"><div class="label">Role</div><div class="value"><?php echo htmlspecialchars($user['role']); ?></div></div>
            <div class="row"><div class="label">Status</div><div class="value"><?php echo $user['is_active'] ? 'Active' : 'Suspended'; ?></div></div>
            <div class="row"><div class="label">Joined</div><div class="value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div></div>

            <a href="users.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </main>
</div>

</body>
</html>

<?php $conn->close(); ?>
