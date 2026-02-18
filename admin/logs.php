<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();

$stmt = $conn->prepare("
    SELECT 
        al.log_id,
        al.action,
        al.target_type,
        al.target_id,
        al.description,
        al.ip_address,
        al.created_at,
        u.full_name,
        u.username
    FROM admin_logs al
    JOIN users u ON u.user_id = al.admin_id
    ORDER BY al.created_at DESC
    LIMIT 200
");
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Admin name for header
$admin_name = $_SESSION['full_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Activity Logs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Hubohu Dashboard Consistent Styles */
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
        
        /* SIDEBAR - Exactly like Dashboard */
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

        /* TABLE CARD STYLING */
        .table-card {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #334155;
        }

        .table-card h2 {
            margin-bottom: 20px;
            color: #f1f5f9;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: #334155;
            color: #94a3b8;
            padding: 12px;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #334155;
            font-size: 14px;
            color: #cbd5e1;
        }

        tr:hover {
            background: rgba(255,255,255,0.02);
        }

        .badge {
            padding: 4px 10px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-delete { background: #ef4444; color: #fff; }
        .badge-update { background: #f59e0b; color: #000; }
        .badge-create { background: #10b981; color: #fff; }
        .badge-login { background: #3b82f6; color: #fff; }
        .badge-default { background: #64748b; color: #fff; }
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
            <a href="notifications.php">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="logs.php" class="active">
                <i class="fas fa-history"></i> Activity Logs
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <div>
                <h1>Admin Activity Logs</h1>
                <p style="color:#64748b;">Track all administrative actions and system changes</p>
            </div>
            <div class="admin-user">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div style="font-size: 13px; color: #64748b;">Administrator</div>
                </div>
                <img src="../assets/images/default-avatar.png" alt="Admin">
            </div>
        </div>

        <div class="table-card">
            <h2><i class="fas fa-list-ul" style="margin-right: 10px; color: #818cf8;"></i> System Logs</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(count($logs)): ?>
                        <?php foreach($logs as $log): 
                            // Action color logic
                            $action_class = 'badge-default';
                            $action = strtolower($log['action']);
                            if(strpos($action, 'delete') !== false) $action_class = 'badge-delete';
                            elseif(strpos($action, 'update') !== false || strpos($action, 'edit') !== false) $action_class = 'badge-update';
                            elseif(strpos($action, 'create') !== false || strpos($action, 'add') !== false) $action_class = 'badge-create';
                            elseif(strpos($action, 'login') !== false) $action_class = 'badge-login';
                        ?>
                        <tr>
                            <td>#<?= $log['log_id']; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($log['full_name']); ?></strong><br>
                                <small style="color: #64748b;">@<?= htmlspecialchars($log['username']); ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $action_class; ?>">
                                    <?= strtoupper($log['action']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: #94a3b8; font-size: 13px;"><?= htmlspecialchars($log['target_type'] ?? 'N/A'); ?></span>
                                <?= $log['target_id'] ? '<b style="color: #818cf8;">(#'.$log['target_id'].')</b>' : ''; ?>
                            </td>
                            <td style="max-width: 300px;"><?= htmlspecialchars($log['description']); ?></td>
                            <td style="font-family: monospace; color: #94a3b8;"><?= htmlspecialchars($log['ip_address']); ?></td>
                            <td style="white-space: nowrap;"><?= date('M d, Y', strtotime($log['created_at'])); ?><br>
                                <small style="color: #64748b;"><?= date('h:i A', strtotime($log['created_at'])); ?></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:30px; color:#64748b">
                                No activity logs found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php closeConnection($conn); ?>
</body>
</html>