<?php
/**
 * Admin - Subscription Management
 * Path: /admin/subscriptions.php
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

// session.php theke requireAdmin function check kora hochche
requireAdmin();
$conn = getConnection();

// =======================
// Fetch subscriptions (FULLY CORRECT JOIN)
// =======================
$stmt = $conn->prepare("
    SELECT 
        s.subscription_id,
        s.start_date,
        s.end_date,
        s.is_active,
        s.created_at,
        u.full_name,
        u.username,
        sp.student_id_number,
        uni.university_name,
        dept.department_name
    FROM subscriptions s
    JOIN users u ON u.user_id = s.user_id
    LEFT JOIN student_profiles sp ON sp.user_id = u.user_id
    LEFT JOIN universities uni ON uni.university_id = sp.university_id
    LEFT JOIN departments dept ON dept.department_id = sp.department_id
    ORDER BY s.created_at DESC
");
$stmt->execute();
$subscriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Admin name for header
$admin_name = $_SESSION['full_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Student Subscriptions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Dashboard Consistent Styles */
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
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active { background: #10b981; color: #fff; }
        .badge-expired { background: #ef4444; color: #fff; }

        .btn-view {
            color: #818cf8;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-view:hover { text-decoration: underline; }
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
            <a href="subscriptions.php" class="active">
                <i class="fas fa-crown"></i> Subscriptions
            </a>
            <a href="messages.php">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="notifications.php">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="logs.php">
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
                <h1>Student Subscriptions</h1>
                <p style="color:#64748b;">Manage and monitor active student plans</p>
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
            <h2><i class="fas fa-crown" style="margin-right: 10px; color: #818cf8;"></i> Subscription List</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>University</th>
                            <th>Department</th>
                            <th>Student ID</th>
                            <th>Status</th>
                            <th>Start</th>
                            <th>Expiry</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(count($subscriptions)): ?>
                        <?php foreach($subscriptions as $sub): ?>
                        <tr>
                            <td>#<?= $sub['subscription_id']; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($sub['full_name']); ?></strong><br>
                                <small style="color: #64748b;">@<?= htmlspecialchars($sub['username']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($sub['university_name'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($sub['department_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                if (!empty($sub['student_id_number'])) {
                                    echo substr($sub['student_id_number'], 0, 3) . '****';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($sub['is_active']): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-expired">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($sub['start_date'])); ?></td>
                            <td><?= date('M d, Y', strtotime($sub['end_date'])); ?></td>
                            <td>
                                <a href="view_subscription.php?id=<?= $sub['subscription_id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center; padding:30px; color:#64748b">
                                No subscriptions found.
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