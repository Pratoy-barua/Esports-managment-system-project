<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Check Admin Access
requireAdmin();
$conn = getConnection();

// 1. Total created notifications (Drafts + Sent)
$totalNotifications = $conn->query("
    SELECT COUNT(*) total FROM admin_notifications
")->fetch_assoc()['total'] ?? 0;

// 2. Total Sent notifications
$sentNotifications = $conn->query("
    SELECT COUNT(*) total FROM admin_notifications WHERE status = 'sent'
")->fetch_assoc()['total'] ?? 0;

// 3. Notifications sent today
$todayNotifications = $conn->query("
    SELECT COUNT(*) total
    FROM admin_notifications
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['total'] ?? 0;

// 4. Total User Recipients (Total rows in user_notifications table)
$totalRecipients = $conn->query("
    SELECT COUNT(*) total FROM notifications
")->fetch_assoc()['total'] ?? 0;

// 5. Unread notifications count
$unreadCount = $conn->query("
    SELECT COUNT(*) total FROM notifications WHERE is_read = 0
")->fetch_assoc()['total'] ?? 0;

/* ===============================
   FETCH NOTIFICATIONS LIST
================================ */
// ✅ JOIN users u ON an.created_by = u.user_id (Correct logic)
// an.* includes 'id' which is the Notification ID
$notifications = $conn->query("
    SELECT an.*, u.full_name
    FROM admin_notifications an
    JOIN users u ON an.created_by = u.user_id
    ORDER BY an.created_at DESC
");

// Admin info for header
$admin_name = $_SESSION['full_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Notifications Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Dashboard Consistent Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* SIDEBAR */
        .admin-sidebar {
            width: 260px; background: #1e293b; padding: 20px;
            position: fixed; height: 100vh; overflow-y: auto;
        }
        
        .admin-logo {
            text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px;
        }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        
        .admin-nav a {
            display: flex; align-items: center; padding: 12px 16px;
            color: #cbd5e1; text-decoration: none; border-radius: 8px;
            margin-bottom: 5px; transition: all 0.3s;
        }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
        .admin-nav a i { margin-right: 12px; width: 20px; }
        
        /* CONTENT AREA */
        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        
        .admin-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
        }
        .admin-header h1 { font-size: 28px; color: #f1f5f9; }

        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #818cf8; }

        /* STATS CARDS */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;
        }
        .stat-card { background: #1e293b; padding: 25px; border-radius: 12px; border-left: 4px solid #818cf8; }
        .stat-card span { font-size: 14px; color: #94a3b8; margin-bottom: 10px; display: block; }
        .stat-card h2 { font-size: 28px; font-weight: bold; color: #f1f5f9; }

        /* TABLE CARD STYLING */
        .table-card { background: #1e293b; padding: 25px; border-radius: 12px; border: 1px solid #334155; }
        .table-card h3 { margin-bottom: 20px; color: #f1f5f9; font-size: 20px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #334155; color: #94a3b8; padding: 12px; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #334155; font-size: 14px; color: #cbd5e1; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-draft { background: #f59e0b; color: #000; }
        .badge-sent { background: #10b981; color: #fff; }

        .btn-add {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
            background: #6366f1; color: #fff; border-radius: 8px; text-decoration: none;
            font-weight: 600; font-size: 14px; transition: 0.3s;
        }
        .btn-add:hover { background: #4f46e5; }

        .actions a { color: #818cf8; text-decoration: none; margin-right: 10px; }
        .actions a:hover { text-decoration: underline; }
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
            <a href="users.php"><i class="fas fa-users"></i> User Management</a>
            <a href="tournaments.php"><i class="fas fa-trophy"></i> Tournaments</a>
            <a href="hosting.php"><i class="fas fa-calendar-check"></i> Hosting Requests</a>
            <a href="teams.php"><i class="fas fa-users-gear"></i> Teams</a>
            <a href="products.php"><i class="fas fa-box"></i> Products & Orders</a>
            <a href="subscriptions.php"><i class="fas fa-crown"></i> Subscriptions</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            <a href="notifications.php" class="active"><i class="fas fa-bell"></i> Notifications</a>
            <a href="logs.php"><i class="fas fa-history"></i> Activity Logs</a>
            <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <div>
                <h1>Admin Notifications</h1>
                <p style="color:#64748b;">Create, send & monitor system notifications</p>
            </div>
            <div class="admin-user">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div style="font-size: 13px; color: #64748b;">Administrator</div>
                </div>
                <img src="../assets/images/default-avatar.png" alt="Admin">
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <a href="create_notification.php" class="btn-add">
                <i class="fas fa-plus"></i> Create Notification
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #3b82f6;">
                <span>Total Notifications</span><h2><?= $totalNotifications ?></h2>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <span>Sent</span><h2><?= $sentNotifications ?></h2>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <span>Sent Today</span><h2><?= $todayNotifications ?></h2>
            </div>
            <div class="stat-card" style="border-left-color: #06b6d4;">
                <span>Total Recipients</span><h2><?= $totalRecipients ?></h2>
            </div>
            <div class="stat-card" style="border-left-color: #ef4444;">
                <span>Unread (Users)</span><h2><?= $unreadCount ?></h2>
            </div>
        </div>

        <div class="table-card">
            <h3><i class="fas fa-history" style="margin-right: 10px; color: #818cf8;"></i> Notification History</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if($notifications->num_rows): while($n = $notifications->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $n['id'] ?></td>
                            
                            <td><strong><?= htmlspecialchars($n['title']) ?></strong></td>
                            <td><?= ucfirst($n['type']) ?></td>
                            <td><span style="color: #94a3b8;"><?= ucfirst($n['target_type']) ?></span></td>
                            <td>
                                <span class="badge <?= $n['status']=='sent'?'badge-sent':'badge-draft' ?>">
                                    <?= ucfirst($n['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($n['full_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($n['created_at'])) ?><br>
                                <small style="color: #64748b;"><?= date('g:i A', strtotime($n['created_at'])) ?></small>
                            </td>
                            <td class="actions">
                                <a href="view_notification.php?id=<?= $n['id'] ?>"><i class="fas fa-eye"></i> View</a>
                                
                                <?php if($n['status']=='draft'): ?>
                                    <a href="send_notification.php?id=<?= $n['id'] ?>" style="color: #10b981;" onclick="return confirm('Send this notification to all targets? This cannot be undone.')">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </a>
                                <?php endif; ?>

                                <a href="delete_notification.php?id=<?= $n['id'] ?>" style="color: #ef4444; margin-left: 10px;" onclick="return confirm('Are you sure you want to delete this notification?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8" style="text-align:center; padding: 30px; color:#64748b">No notifications found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>