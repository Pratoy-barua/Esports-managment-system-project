<?php
/**
 * View Tournament & Manage Participants
 * Path: /admin/view_tournament.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$conn = getConnection();
$tournament_id = (int)($_GET['id'] ?? 0);
if ($tournament_id <= 0) die('Invalid tournament');

/* ===============================
   HANDLE PARTICIPANT ACTIONS
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participant_id'])) {
    $pid = (int)($_POST['participant_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($pid && in_array($action, ['approve','reject','disqualify','remove'])) {
        $conn->begin_transaction();
        try {
            if ($action === 'remove') {
                $stmt = $conn->prepare("DELETE FROM participants WHERE participant_id=?");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
            } else {
                $db_status = match($action) {
                    'approve' => 'Approved',
                    'reject' => 'Rejected',
                    'disqualify' => 'Disqualified',
                    default => 'Registered'
                };
                
                $stmt = $conn->prepare("UPDATE participants SET status=? WHERE participant_id=?");
                $stmt->bind_param("si", $db_status, $pid);
                $stmt->execute();
            }

            // Log Action
            $log = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, description, ip_address) VALUES (?, ?, 'participant', ?, ?, ?)");
            $desc = strtoupper($action) . " participant ID $pid in Tournament $tournament_id";
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $log->bind_param("isiss", $_SESSION['user_id'], $action, $pid, $desc, $ip);
            $log->execute();

            $conn->commit();
            $message = "Action successful.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Action failed.";
        }
    }
}

/* ===============================
   FETCH DATA
================================ */
$tstmt = $conn->prepare("SELECT * FROM tournaments WHERE tournament_id = ?");
$tstmt->bind_param("i", $tournament_id);
$tstmt->execute();
$tournament = $tstmt->get_result()->fetch_assoc();
if (!$tournament) die('Tournament not found');

/* ✅ Fix: joined_at কলামটি কুয়েরি থেকে বাদ দেওয়া হয়েছে */
$participants = $conn->prepare("
    SELECT 
        p.participant_id, p.status,
        u.username, u.full_name, u.profession,
        tm.team_name AS team_name
    FROM participants p
    JOIN users u ON u.user_id = p.user_id 
    LEFT JOIN teams tm ON tm.team_id = p.team_id
    WHERE p.tournament_id=?
    ORDER BY p.participant_id DESC
");
$participants->bind_param("i", $tournament_id);
$participants->execute();
$list = $participants->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tournament | Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        .box { background: #1e293b; padding: 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #334155; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge.approved { background: #065f46; color: #34d399; }
        .badge.registered { background: #1e40af; color: #bfdbfe; }
        .badge.rejected { background: #7f1d1d; color: #f87171; }
        .badge.disqualified { background: #374151; color: #9ca3af; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; font-size: 14px; }
        th { background: #334155; }

        .btn { padding: 6px 12px; border-radius: 6px; font-size: 12px; border: none; cursor: pointer; color: white; transition: 0.3s; }
        .btn.red { background: #ef4444; } 
        .btn.green { background: #22c55e; }
        .btn.yellow { background: #f59e0b; color: #000; }
        
        select, textarea { background: #0f172a; color: white; border: 1px solid #334155; padding: 10px; border-radius: 8px; width: 100%; margin-top: 10px; resize: vertical; }

        .admin-sidebar { width: 260px; background: #1e293b; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; border-right: 1px solid #334155; }
        .admin-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        .admin-nav a { display: flex; align-items: center; padding: 12px 16px; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s; }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
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
            <a href="tournaments.php" class="active"><i class="fas fa-trophy"></i> Tournaments</a>
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
            <h1>Tournament Console</h1>
            <a href="tournaments.php" style="color: #818cf8; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="box">
            <h2 style="color: #818cf8; margin-bottom: 10px;"><?= htmlspecialchars($tournament['tournament_name']) ?></h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <p>Status: <b style="color: #fbbf24;"><?= strtoupper($tournament['status']) ?></b></p>
                <p>Game: <b><?= htmlspecialchars($tournament['game_category']) ?></b></p>
            </div>
        </div>

        <div class="box">
            <h3>Update Lifecycle</h3>
            <form method="POST" action="update_tournament_status.php" style="max-width: 400px; margin-top: 15px;">
                <input type="hidden" name="tournament_id" value="<?= $tournament['tournament_id'] ?>">
                <select name="new_status" required>
                    <option value="">-- Choose Next Phase --</option>
                    <?php if($tournament['status'] === 'Upcoming'): ?>
                        <option value="Ongoing">Start Tournament</option>
                    <?php endif; ?>
                    <?php if($tournament['status'] === 'Ongoing'): ?>
                        <option value="Completed">Finish Tournament</option>
                    <?php endif; ?>
                    <option value="Cancelled">Cancel Tournament</option>
                </select>
                <button type="submit" class="btn green" style="margin-top:10px; width:100%">Update Status</button>
            </form>
        </div>

        <div class="box">
            <h3>Manage Participants</h3>
            <table>
                <thead>
                    <tr>
                        <th>Player/Team</th>
                        <th>Profession</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $list->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <b><?= htmlspecialchars($p['full_name'] ?: $p['username']) ?></b><br>
                            <small style="color:#94a3b8"><?= $p['team_name'] ?: 'Solo' ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['profession']) ?></td>
                        <td><span class="badge <?= strtolower($p['status']) ?>"><?= $p['status'] ?></span></td>
                        <td>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="participant_id" value="<?= $p['participant_id'] ?>">
                                
                                <?php if($p['status'] === 'Registered'): ?>
                                    <button class="btn green" name="action" value="approve">Approve</button>
                                    <button class="btn red" name="action" value="reject">Reject</button>
                                <?php endif; ?>

                                <?php if($p['status'] !== 'Disqualified'): ?>
                                    <button class="btn yellow" name="action" value="disqualify">Disqualify</button>
                                <?php endif; ?>
                                
                                <button class="btn red" name="action" value="remove" onclick="return confirm('Remove permanently?')"><i class="fas fa-trash"></i></button>
                            </form>
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
<?php $conn->close(); ?>