<?php
/**
 * Admin User Management - UI Synced with Dashboard
 * View, Search, Filter, Suspend, Delete Users
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// ১. ডাটাবেস কানেকশন ইনিশিয়েলাইজ করা
$conn = getConnection(); 

// ২. অ্যাডমিন অ্যাক্সেস ভেরিফাই করা
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$message = '';

// ৩. ইউজার অ্যাকশন হ্যান্ডেল করা (is_active কলাম ব্যবহার করে)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];
        
        $is_active = null;
        $log_type = '';
        
        if ($action === 'suspend') { $is_active = 0; $log_type = 'suspend_user'; }
        elseif ($action === 'activate') { $is_active = 1; $log_type = 'activate_user'; }
        elseif ($action === 'delete') { $is_active = 0; $log_type = 'delete_user'; }

        if ($is_active !== null) {
            $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE user_id = ? AND role = 'user'");
            if ($stmt) {
                $stmt->bind_param("ii", $is_active, $user_id);
                if ($stmt->execute()) {
                    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action_type, affected_entity, action_details) VALUES (?, ?, ?, ?)");
                    if ($log_stmt) {
                        $details = ($is_active == 1) ? "User activated" : "User suspended/deleted";
                        $log_stmt->bind_param("isis", $admin_id, $log_type, $user_id, $details);
                        $log_stmt->execute();
                    }
                    $message = "User status updated successfully!";
                }
            }
        }
    }
}

// ৪. ফিল্টার ও সার্চ লজিক
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT u.* FROM users u WHERE u.role = 'user'";
$params = [];
$types = '';

if ($search) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param; $params[] = $search_param;
    $types .= 'sss';
}

if ($status_filter !== '') {
    $query .= " AND u.is_active = ?";
    $params[] = intval($status_filter);
    $types .= 'i';
}

$query .= " ORDER BY u.created_at DESC";

$stmt = $conn->prepare($query);
if ($stmt) {
    if ($params) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; }
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar Styling */
        .admin-sidebar { width: 260px; background: #1e293b; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        .admin-nav a { display: flex; align-items: center; padding: 12px 16px; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s; }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
        .admin-nav a i { margin-right: 12px; width: 20px; }

        /* Content Styling */
        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #818cf8; }

        .table-container { background: #1e293b; border-radius: 12px; overflow: hidden; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
        th { background: #334155; font-size: 14px; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-active { background: #065f46; color: #34d399; }
        .status-suspended { background: #7f1d1d; color: #f87171; }
        .btn { padding: 6px 12px; border-radius: 6px; cursor: pointer; border: none; color: white; text-decoration: none; font-size: 12px; margin-right: 5px; }
        .btn-view { background: #3b82f6; }
        .btn-suspend { background: #f59e0b; }
        .btn-activate { background: #10b981; }
        .btn-delete { background: #ef4444; }
        .search-box { background: #1e293b; padding: 15px; border-radius: 10px; display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input, .search-box select { background: #0f172a; color: white; border: 1px solid #334155; padding: 8px; border-radius: 6px; }
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
            
            <div class="admin-header">
                <h1>User Management</h1>
                <div class="admin-user">
                    <div>
                        <div style="font-weight: 600;">
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </div>
                        <div style="font-size: 13px; color: #64748b;">
                            Administrator
                        </div>
                    </div>
                    <img src="../assets/images/default-avatar.png" alt="Admin">
                </div>
            </div>

            <?php if ($message): ?>
                <div style="background: #10b981; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search name, email..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="1" <?php echo ($status_filter === '1' ? 'selected' : ''); ?>>Active</option>
                    <option value="0" <?php echo ($status_filter === '0' ? 'selected' : ''); ?>>Suspended</option>
                </select>
                <button type="submit" class="btn btn-view">Search</button>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php $status_text = ($user['is_active'] == 1) ? 'active' : 'suspended'; ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge status-<?php echo $status_text; ?>">
                                        <?php echo ucfirst($status_text); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div style="display:flex;">
                                        <a href="user_detail.php?id=<?php echo $user['user_id']; ?>" class="btn btn-view">View</a>
                                        
                                        <?php if ($user['is_active'] == 1): ?>
                                            <form method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <input type="hidden" name="action" value="suspend">
                                                <button type="submit" class="btn btn-suspend" onclick="return confirm('Suspend this user?')">Suspend</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="btn btn-activate">Activate</button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" onsubmit="return confirm('Delete this user?')">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
<?php $conn->close(); ?>