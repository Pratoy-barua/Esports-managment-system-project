<?php
/**
 * Admin - View Team Details (Dashboard UI Synced)
 * Path: /admin/view_team.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: teams.php');
    exit;
}

$team_id = (int) $_GET['id'];
$conn = getConnection();
$admin_name = $_SESSION['full_name'];

/* ===============================
    TEAM + LEADER INFO
================================ */
$stmt = $conn->prepare(
    "SELECT 
        t.team_id, t.team_name, t.game_category, t.description, t.team_logo,
        t.status, t.created_at,
        u.user_id AS leader_id, u.full_name, u.username, u.email,
        u.profession
     FROM teams t
     JOIN users u ON u.user_id = t.captain_id
     WHERE t.team_id = ?"
);
$stmt->bind_param("i", $team_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$team) {
    header('Location: teams.php');
    exit;
}

/* ===============================
    TEAM MEMBERS
================================ */
$stmt = $conn->prepare(
    "SELECT 
        u.user_id, u.full_name, u.username, u.profession,
        tm.role
     FROM team_members tm
     JOIN users u ON u.user_id = tm.user_id
     WHERE tm.team_id = ?
     ORDER BY tm.role DESC"
);
$stmt->bind_param("i", $team_id);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ===============================
    TOURNAMENT PARTICIPATION
================================ */
$stmt = $conn->prepare(
    "SELECT 
        tr.tournament_id, tr.tournament_name, tr.status
     FROM participants p
     JOIN tournaments tr ON tr.tournament_id = p.tournament_id
     WHERE p.team_id = ?"
);
$stmt->bind_param("i", $team_id);
$stmt->execute();
$tournaments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Team Details - ESportsHub Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Dashboard UI Sync --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar Fixed Position Match */
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
            display: flex; align-items: center; padding: 12px 16px;
            color: #cbd5e1; text-decoration: none; border-radius: 8px;
            margin-bottom: 5px; transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #334155; color: #818cf8;
        }
        
        .admin-nav a i { margin-right: 12px; width: 20px; }
        
        /* Content Area Offset Match */
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .admin-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 30px;
        }
        
        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #818cf8; }

        /* Custom Card Styles for Team View */
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        .card h3 { color: #818cf8; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 10px; font-size: 18px; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-active { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-deleted { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; }
        
        .team-logo-circle { width: 65px; height: 65px; border-radius: 50%; object-fit: cover; border: 2px solid #818cf8; background: #0f172a; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #cbd5e1; text-decoration: none; margin-bottom: 20px; font-size: 14px; transition: 0.3s; }
        .btn-back:hover { color: #818cf8; }
        
        .action-links { display: flex; gap: 15px; align-items: center; }
        .action-links a { text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; padding: 8px 16px; border-radius: 6px; }
        .btn-edit { background: #334155; color: #f59e0b; border: 1px solid #f59e0b; }
        .btn-edit:hover { background: #f59e0b; color: #000; }
        .btn-delete { background: #334155; color: #ef4444; border: 1px solid #ef4444; }
        .btn-delete:hover { background: #ef4444; color: #fff; }
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
            <a href="teams.php" class="active"><i class="fas fa-users-gear"></i> Teams</a>
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
            <a href="teams.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Teams List</a>
            <div class="admin-user">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div style="font-size: 13px; color: #64748b;">Administrator</div>
                </div>
                <img src="../assets/images/default-avatar.png" alt="Admin">
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
            <img src="../<?= htmlspecialchars($team['team_logo'] ?: 'assets/images/default-team.png') ?>" class="team-logo-circle" alt="Logo">
            <h1 style="font-size: 28px;"><?= htmlspecialchars($team['team_name']) ?> <small style="font-size: 14px; color: #64748b;">(ID: #<?= $team['team_id'] ?>)</small></h1>
        </div>

        <div class="grid">
            <div class="card">
                <h3><i class="fas fa-info-circle"></i> Team Information</h3>
                <p style="margin-bottom: 10px;"><strong>Status:</strong>
                    <span class="badge <?= $team['status']==='active'?'status-active':'status-deleted' ?>">
                        <?= ucfirst($team['status']) ?>
                    </span>
                </p>
                <p style="margin-bottom: 10px;"><strong>Game:</strong> <?= htmlspecialchars($team['game_category']) ?></p>
                <p style="margin-bottom: 10px;"><strong>Created:</strong> <?= date('M d, Y', strtotime($team['created_at'])) ?></p>
                <p><strong>Description:</strong></p>
                <p style="color: #94a3b8; font-size: 14px; line-height: 1.6; background: #0f172a; padding: 12px; border-radius: 8px; margin-top: 5px;">
                    <?= nl2br(htmlspecialchars($team['description'] ?: 'No description provided.')) ?>
                </p>
            </div>

            <div class="card">
                <h3><i class="fas fa-crown"></i> Team Leader (Captain)</h3>
                <p style="margin-bottom: 10px;"><strong>Name:</strong> <?= htmlspecialchars($team['full_name']) ?></p>
                <p style="margin-bottom: 10px;"><strong>Username:</strong> <span style="color: #818cf8;">@<?= htmlspecialchars($team['username']) ?></span></p>
                <p style="margin-bottom: 10px;"><strong>Email:</strong> <?= htmlspecialchars($team['email']) ?></p>
                <p><strong>Profession:</strong> <?= htmlspecialchars($team['profession']) ?></p>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-user-group"></i> Team Members</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Profession</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['full_name']) ?></td>
                        <td><span style="color: #94a3b8;">@<?= htmlspecialchars($m['username']) ?></span></td>
                        <td>
                            <span style="color: <?= $m['role'] === 'Captain' ? '#f59e0b' : '#cbd5e1' ?>; font-weight: 600;">
                                <?= htmlspecialchars($m['role']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($m['profession']) ?></td>
                        <td>
                            <?php if ($m['role'] !== 'Captain'): ?>
                                <a href="remove_member.php?team_id=<?= $team_id ?>&user_id=<?= $m['user_id'] ?>"
                                   onclick="return confirm('Remove this member?')"
                                   style="color:#ef4444; font-size: 13px; font-weight: 600; text-decoration: none;">
                                   <i class="fas fa-user-minus"></i> Remove
                                </a>
                            <?php else: ?>
                                <small style="color: #64748b; font-style: italic;">Leader</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3><i class="fas fa-trophy"></i> Tournament Participation</h3>
            <?php if (!$tournaments): ?>
                <p style="color: #94a3b8;">This team hasn't participated in any tournaments yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tournament Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tournaments as $t): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t['tournament_name']) ?></strong></td>
                            <td><?= htmlspecialchars($t['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card action-links" style="border-left: 4px solid #818cf8;">
            <a href="edit_team.php?id=<?= $team_id ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit Team Info</a>
            
            <?php if ($team['status'] === 'active'): ?>
                <a href="delete_team.php?id=<?= $team_id ?>"
                   class="btn-delete"
                   onclick="return confirm('Are you sure you want to disband this team?')">
                   <i class="fas fa-trash-alt"></i> Disband Team
                </a>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>