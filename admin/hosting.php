<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$conn = getConnection();
$admin_name = $_SESSION['full_name'];


$status = $_GET['status'] ?? '';
$whereSql = '';
$params = [];
$types = '';

if (!empty($status)) {
    $whereSql = "WHERE hr.status = ?";
    $params[] = $status;
    $types .= 's';
}


$sql = "
    SELECT 
        hr.request_id,
        hr.event_name,
        hr.game_category,
        hr.event_type,
        hr.prize_pool,
        hr.start_date,
        hr.end_date,
        hr.status,
        hr.requested_at,
        u.full_name,
        u.username,
        uni.university_name AS university_name
    FROM hosting_requests hr
    INNER JOIN users u ON u.user_id = hr.user_id
    LEFT JOIN student_profiles sp ON sp.user_id = u.user_id
    LEFT JOIN universities uni ON uni.university_id = sp.university_id
    $whereSql
    ORDER BY hr.requested_at DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hosting Requests - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #e2e8f0; }
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* Dashboard Sidebar Match */
        .admin-sidebar { width: 260px; background: #1e293b; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        .admin-nav a { display: flex; align-items: center; padding: 12px 16px; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s; }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
        .admin-nav a i { margin-right: 12px; width: 20px; }

        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #818cf8; }

        /* Table Box Styling */
        .table-container { background: #1e293b; padding: 25px; border-radius: 12px; }
        .filter-bar { margin-bottom: 25px; display: flex; gap: 10px; }
        .btn-filter { padding: 8px 16px; border-radius: 8px; text-decoration: none; background: #334155; color: #cbd5e1; border: 1px solid #475569; transition: 0.3s; }
        .btn-filter.active { background: #818cf8; color: #fff; border-color: #818cf8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; font-size: 14px; text-transform: uppercase; }
        
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge.pending { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        .badge.approved { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .badge.rejected { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

        .btn-view { background: #334155; color: #fff; padding: 7px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; transition: 0.3s; }
        .btn-view:hover { background: #475569; color: #818cf8; }
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
                <a href="hosting.php" class="active"><i class="fas fa-calendar-check"></i> Hosting Requests</a>
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
                <h1>Tournament Hosting Requests</h1>
                <div class="admin-user">
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div style="font-size: 13px; color: #64748b;">Administrator</div>
                    </div>
                    <img src="../assets/images/default-avatar.png" alt="Admin">
                </div>
            </div>

            <div class="filter-bar">
                <a href="hosting.php" class="btn-filter <?= !$status ? 'active' : '' ?>">All</a>
                <a href="?status=Pending" class="btn-filter <?= $status === 'Pending' ? 'active' : '' ?>">Pending</a>
                <a href="?status=Approved" class="btn-filter <?= $status === 'Approved' ? 'active' : '' ?>">Approved</a>
                <a href="?status=Rejected" class="btn-filter <?= $status === 'Rejected' ? 'active' : '' ?>">Rejected</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Host Information</th>
                            <th>Event & Game</th>
                            <th>Prize Pool</th>
                            <th>Timeline</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($requests->num_rows === 0): ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:40px; color: #64748b;">
                                    No hosting requests found.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php while ($r = $requests->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['full_name']) ?></strong><br>
                                    <small style="color: #818cf8;">@<?= htmlspecialchars($r['username']) ?></small><br>
                                    <small style="color: #94a3b8;"><?= htmlspecialchars($r['university_name'] ?? 'General User') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['event_name']) ?><br>
                                    <small style="color: #94a3b8;"><?= htmlspecialchars($r['game_category']) ?> (<?= htmlspecialchars($r['event_type']) ?>)</small>
                                </td>
                                <td><strong style="color: #10b981;">৳<?= number_format($r['prize_pool']) ?></strong></td>
                                <td>
                                    <div style="font-size: 13px;">
                                        <span style="color: #94a3b8;">Start:</span> <?= date('d M, Y', strtotime($r['start_date'])) ?><br>
                                        <span style="color: #94a3b8;">End:</span> <?= date('d M, Y', strtotime($r['end_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= strtolower($r['status']) ?>">
                                        <?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_request.php?id=<?= (int)$r['request_id'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>