<?php
/**
 * Admin Tournament Management Dashboard
 * Path: /admin/tournaments.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// ডাটাবেস কানেকশন
$conn = getConnection();

// অ্যাডমিন চেক
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

/* ===============================
    TOURNAMENT STATISTICS
================================ */
$stats = [
    'total' => 0,
    'upcoming' => 0,
    'running' => 0,
    'completed' => 0,
    'cancelled' => 0
];

$statQuery = $conn->query("
    SELECT status, COUNT(*) AS total 
    FROM tournaments 
    GROUP BY status
");

if ($statQuery) {
    while ($row = $statQuery->fetch_assoc()) {
        $status_key = strtolower($row['status']);
        if (array_key_exists($status_key, $stats)) {
            $stats[$status_key] = (int)$row['total'];
        }
        $stats['total'] += (int)$row['total'];
    }
}

/* ===============================
    TOURNAMENT LIST (FIXED QUERY)
================================ */
// ✅ SQL Query Fix: Using tournament_id and Correct Column for Count
$tournaments = $conn->query("
    SELECT 
        t.*,
        COUNT(p.user_id) AS total_participants
    FROM tournaments t
    LEFT JOIN participants p ON p.tournament_id = t.tournament_id
    GROUP BY t.tournament_id
    ORDER BY t.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Tournament Management</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; }
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar Styling */
        .admin-sidebar { width: 260px; background: #1e293b; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; border-right: 1px solid #334155; }
        .admin-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        .admin-nav a { display: flex; align-items: center; padding: 12px 16px; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s; }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
        .admin-nav a i { margin-right: 12px; width: 20px; }

        /* Content Area */
        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Dashboard Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #1e293b; padding: 20px; border-radius: 10px; border-left: 4px solid #818cf8; text-align: center; }
        .stat-card strong { display: block; font-size: 24px; margin-top: 5px; color: #f1f5f9; }

        /* Table Wrapper */
        .table-wrapper { background: #1e293b; padding: 20px; border-radius: 12px; overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .admin-table th, .admin-table td { padding: 12px; border-bottom: 1px solid #334155; text-align: left; }
        .admin-table th { background: #334155; color: #f1f5f9; }
        
        .status { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .status.upcoming { background: #3b82f6; color: #fff; }
        .status.running { background: #10b981; color: #fff; }
        .status.completed { background: #64748b; color: #fff; }
        .status.cancelled { background: #ef4444; color: #fff; }

        .btn-primary { background: #818cf8; color: #fff; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; background: #334155; color: #e2e8f0; margin-right: 5px; display: inline-block; }
        .btn-sm.warning { background: #f59e0b; color: #fff; }
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
            <a href="tournaments.php" class="active">
                <i class="fas fa-trophy"></i> Tournaments
            </a>
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
            <h1>Tournament Management</h1>
            <div style="text-align:right">
                <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></strong><br>
                <small style="color:#94a3b8">Administrator</small>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">Total<strong><?= $stats['total'] ?></strong></div>
            <div class="stat-card">Upcoming<strong><?= $stats['upcoming'] ?></strong></div>
            <div class="stat-card">Running<strong><?= $stats['running'] ?></strong></div>
            <div class="stat-card">Completed<strong><?= $stats['completed'] ?></strong></div>
            <div class="stat-card">Cancelled<strong><?= $stats['cancelled'] ?></strong></div>
        </div>

        <div class="table-wrapper">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <h2>All Tournaments</h2>
                <a href="create_tournament.php" class="btn-primary"><i class="fas fa-plus"></i> Create Tournament</a>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Game Category</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Participants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($tournaments && $tournaments->num_rows > 0): ?>
                    <?php while($t = $tournaments->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t['tournament_name']) ?></strong></td>
                        <td><?= htmlspecialchars($t['game_category']) ?></td>
                        <td><?= ucfirst($t['tournament_type']) ?></td>
                        <td>
                            <span class="status <?= strtolower($t['status']) ?>">
                                <?= ucfirst(str_replace('_',' ', $t['status'])) ?>
                            </span>
                        </td>
                        <td><i class="fas fa-users"></i> <?= (int)$t['total_participants'] ?></td>
                        <td>
                            <a href="view_tournament.php?id=<?= $t['tournament_id'] ?>" class="btn-sm"><i class="fas fa-eye"></i> View</a>
                            <a href="edit_tournament.php?id=<?= $t['tournament_id'] ?>" class="btn-sm warning"><i class="fas fa-edit"></i> Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">No tournaments found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>