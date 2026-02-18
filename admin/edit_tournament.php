<?php


session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$conn = getConnection();
$errors = [];
$success = false;

$tournament_id = (int)($_GET['id'] ?? 0);
if ($tournament_id <= 0) die('Invalid tournament');


$stmt = $conn->prepare("SELECT * FROM tournaments WHERE tournament_id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
if (!$tournament) die('Tournament not found');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name             = trim($_POST['name'] ?? '');
    $game             = trim($_POST['game'] ?? '');
    $type             = $_POST['tournament_type'] ?? '';
    $prize_pool       = (float)($_POST['prize_pool'] ?? 0);
    $max_participants = (int)($_POST['max_participants'] ?? 0);
    $registration_fee = (float)($_POST['registration_fee'] ?? 0);
    $rules            = trim($_POST['rules'] ?? '');

    $start_date = !empty($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;
    $end_date   = !empty($_POST['end_date'])   ? date('Y-m-d', strtotime($_POST['end_date']))   : null;

    /* Validation */
    if ($name === '') $errors[] = 'Tournament name required';
    if ($game === '') $errors[] = 'Game category required';
    if (!$start_date || !$end_date) $errors[] = 'Start & End date required';

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $update = $conn->prepare("
                UPDATE tournaments SET
                    tournament_name=?,
                    game_category=?,
                    tournament_type=?,
                    prize_pool=?,
                    max_participants=?,
                    registration_fee=?,
                    rules=?,
                    start_date=?,
                    end_date=?
                WHERE tournament_id=?
            ");

            $update->bind_param(
                "sssididssi",
                $name, $game, $type, $prize_pool, $max_participants,
                $registration_fee, $rules, $start_date, $end_date, $tournament_id
            );

            $update->execute();

            /* Admin Log */
            $log = $conn->prepare("
                INSERT INTO admin_logs
                (admin_id, action, target_type, target_id, description, ip_address)
                VALUES (?, 'EDIT_TOURNAMENT', 'tournament', ?, ?, ?)
            ");
            $desc = "Edited tournament: $name";
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $log->bind_param("iiss", $_SESSION['user_id'], $tournament_id, $desc, $ip);
            $log->execute();

            $conn->commit();
            $success = true;

            // Refresh data
            $stmt->execute();
            $tournament = $stmt->get_result()->fetch_assoc();

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Database update failed';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tournament | ESportsHub Admin</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #e2e8f0; }
        
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar Styling (Dashboard Same) */
        .admin-sidebar { width: 260px; background: #1e293b; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; border-right: 1px solid #334155; }
        .admin-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        .admin-nav a { display: flex; align-items: center; padding: 12px 16px; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s; }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
        .admin-nav a i { margin-right: 12px; width: 20px; }

        /* Content Styling */
        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .form-box { max-width: 900px; background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label { display: block; margin-bottom: 8px; color: #94a3b8; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: #fff; }
        .btn-primary { background: #f59e0b; color: #000; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; }
        .success { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; }
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
            <h1>Tournament Management</h1>
            <div style="text-align:right">
                <strong style="display:block"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></strong>
                <small style="color:#64748b">Administrator</small>
            </div>
        </div>

        <div class="form-box">
            <h2 style="margin-bottom:15px;"><i class="fas fa-edit"></i> Edit Tournament</h2>
            <hr style="border:0; border-top:1px solid #334155; margin-bottom:25px">

            <?php if(!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach($errors as $e) echo "<div><i class='fas fa-times-circle'></i> $e</div>"; ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i> Tournament updated successfully.
                    <a href="tournaments.php" style="color:#fff; text-decoration:underline; margin-left:10px;">Back to list</a>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div>
                        <label>Tournament Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($tournament['tournament_name']) ?>" required>
                    </div>
                    <div>
                        <label>Game Category</label>
                        <input type="text" name="game" value="<?= htmlspecialchars($tournament['game_category']) ?>" required>
                    </div>
                    <div>
                        <label>Tournament Type</label>
                        <select name="tournament_type">
                            <?php foreach(['Public','University','Invitational'] as $t): ?>
                                <option value="<?= $t ?>" <?= $tournament['tournament_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Max Participants</label>
                        <input type="number" name="max_participants" value="<?= $tournament['max_participants'] ?>">
                    </div>
                    <div>
                        <label>Prize Pool (৳)</label>
                        <input type="number" step="0.01" name="prize_pool" value="<?= $tournament['prize_pool'] ?>">
                    </div>
                    <div>
                        <label>Registration Fee (৳)</label>
                        <input type="number" step="0.01" name="registration_fee" value="<?= $tournament['registration_fee'] ?>">
                    </div>
                    <div>
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= $tournament['start_date'] ?>" required>
                    </div>
                    <div>
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?= $tournament['end_date'] ?>" required>
                    </div>
                </div>

                <div style="margin-top:20px">
                    <label>Rules & Regulations</label>
                    <textarea name="rules" rows="4"><?= htmlspecialchars($tournament['rules']) ?></textarea>
                </div>

                <div style="margin-top:25px">
                    <button class="btn-primary" type="submit">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="tournaments.php" style="margin-left:15px; color:#94a3b8; text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>
<?php $conn->close(); ?>