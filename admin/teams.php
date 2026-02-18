<?php
/**
 * Admin - Team Management (Dashboard Layout Synced)
 * Path: /admin/teams.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$conn = getConnection();
$admin_name = $_SESSION['full_name'];

/* ===============================
    DASHBOARD STATS (Optimized)
================================ */

// Total teams
$stmt = $conn->prepare("SELECT COUNT(*) FROM teams");
$stmt->execute();
$stmt->bind_result($totalTeams);
$stmt->fetch();
$stmt->close();

// Active teams
$stmt = $conn->prepare("SELECT COUNT(*) FROM teams WHERE status = 'active'");
$stmt->execute();
$stmt->bind_result($activeTeams);
$stmt->fetch();
$stmt->close();

// Deleted teams
$stmt = $conn->prepare("SELECT COUNT(*) FROM teams WHERE status = 'deleted'");
$stmt->execute();
$stmt->bind_result($deletedTeams);
$stmt->fetch();
$stmt->close();

// Teams joined tournaments (Excluding Deleted Teams)
$stmt = $conn->prepare(
    "SELECT COUNT(DISTINCT p.team_id) 
     FROM participants p 
     JOIN teams t ON t.team_id = p.team_id 
     WHERE t.status = 'active'"
);
$stmt->execute();
$stmt->bind_result($tournamentTeams);
$stmt->fetch();
$stmt->close();

/* ===============================
    DYNAMIC FILTER LOGIC
================================ */
$allowedStatus = ['active', 'deleted'];
if (!empty($_GET['status']) && !in_array($_GET['status'], $allowedStatus)) {
    $_GET['status'] = ''; 
}

$where = [];
$params = [];
$types  = '';

if (!empty($_GET['search'])) {
    $where[] = "(t.team_name LIKE ? OR u.username LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search; $params[] = $search;
    $types .= 'ss';
}

if (!empty($_GET['profession'])) {
    $where[] = "u.profession = ?";
    $params[] = $_GET['profession'];
    $types .= 's';
}

if (!empty($_GET['status'])) {
    $where[] = "t.status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
}

$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* ===============================
    FETCH ALL TEAMS (Filtered)
================================ */
$sql = "
SELECT 
    t.team_id, t.team_name, t.game_category, t.created_at, t.status,
    u.full_name AS leader_name, u.username AS leader_username, u.profession,
    (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.team_id) AS total_members
FROM teams t
JOIN users u ON u.user_id = t.captain_id
$whereSQL
ORDER BY t.created_at DESC
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management - ESportsHub Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Hubahu Dashboard Styles --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar Fixed Logic */
        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #334155;
        }
        
        .admin-logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }
        
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        
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
        
        .admin-nav a i { margin-right: 12px; width: 20px; }
        
        /* Content Area Offset */
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
        
        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #818cf8; }

        /* Stats Grid Dashboard Match */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #1e293b;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #818cf8;
        }
        
        .stat-card h3 { font-size: 14px; color: #94a3b8; margin-bottom: 10px; }
        .stat-card .stat-value { font-size: 24px; font-weight: bold; color: #f1f5f9; }

        /* Filter Form */
        .filter-box {
            background: #1e293b;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-box input, .filter-box select {
            background: #0f172a;
            border: 1px solid #334155;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            outline: none;
        }

        .btn-search {
            background: #818cf8;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Table Box */
        .table-box {
            background: #1e293b;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #94a3b8; padding: 15px; border-bottom: 1px solid #334155; font-size: 13px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #334155; vertical-align: middle; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .active-badge { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .deleted-badge { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        
        .action-links a { color: #818cf8; text-decoration: none; margin-right: 15px; font-size: 14px; }
        .action-links a:hover { text-decoration: underline; }
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
                <a href="teams.php" class="active">
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
                <h1>Team Management</h1>
                <div class="admin-user">
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div style="font-size: 13px; color: #64748b;">Administrator</div>
                    </div>
                    <img src="../assets/images/default-avatar.png" alt="Admin">
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Teams</h3>
                    <div class="stat-value"><?php echo number_format($totalTeams); ?></div>
                </div>
                <div class="stat-card" style="border-left-color: #10b981;">
                    <h3>Active Teams</h3>
                    <div class="stat-value"><?php echo number_format($activeTeams); ?></div>
                </div>
                <div class="stat-card" style="border-left-color: #f59e0b;">
                    <h3>Tournament Teams</h3>
                    <div class="stat-value"><?php echo number_format($tournamentTeams); ?></div>
                </div>
                <div class="stat-card" style="border-left-color: #ef4444;">
                    <h3>Disbanded Teams</h3>
                    <div class="stat-value"><?php echo number_format($deletedTeams); ?></div>
                </div>
            </div>

            <form method="GET" class="filter-box">
                <input type="text" name="search" placeholder="Search team or leader..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <select name="profession">
                    <option value="">All Professions</option>
                    <option value="Student" <?= ($_GET['profession'] ?? '')==='Student'?'selected':'' ?>>Student</option>
                    <option value="Job Holder" <?= ($_GET['profession'] ?? '')==='Job Holder'?'selected':'' ?>>Job Holder</option>
                </select>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($_GET['status'] ?? '')==='active'?'selected':'' ?>>Active</option>
                    <option value="deleted" <?= ($_GET['status'] ?? '')==='deleted'?'selected':'' ?>>Deleted</option>
                </select>
                <button type="submit" class="btn-search"><i class="fas fa-filter"></i> Apply Filter</button>
                <a href="teams.php" style="color: #64748b; font-size: 13px; text-decoration: none;">Clear</a>
            </form>

            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>Team Details</th>
                            <th>Captain</th>
                            <th>Profession</th>
                            <th>Members</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$teams): ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px; color: #64748b;">No results found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($teams as $t): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($t['team_name']) ?></strong><br>
                                <small style="color: #64748b;">#<?= $t['team_id'] ?> | <?= htmlspecialchars($t['game_category']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['leader_name']) ?><br>
                                <small style="color: #818cf8;">@<?= htmlspecialchars($t['leader_username']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($t['profession']) ?></td>
                            <td><i class="fas fa-users" style="font-size: 12px; color: #64748b;"></i> <?= $t['total_members'] ?></td>
                            <td>
                                <span class="status-badge <?= $t['status']==='active'?'active-badge':'deleted-badge' ?>">
                                    <?= ucfirst($t['status']) ?>
                                </span>
                            </td>
                            <td class="action-links">
                                <a href="view_team.php?id=<?= $t['team_id'] ?>"><i class="fas fa-eye"></i> View</a>
                                <a href="edit_team.php?id=<?= $t['team_id'] ?>"><i class="fas fa-edit"></i> Edit</a>
                                <?php if ($t['status']==='active'): ?>
                                    <a href="delete_team.php?id=<?= $t['team_id'] ?>" style="color: #ef4444;" onclick="return confirm('Disband this team?')"><i class="fas fa-ban"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
<?php 
// Close DB connection
$conn->close(); 
?>