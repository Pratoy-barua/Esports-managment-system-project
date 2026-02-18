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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name             = trim($_POST['name'] ?? '');
    $game             = trim($_POST['game'] ?? '');
    $type             = $_POST['tournament_type'] ?? '';
    $prize_pool       = (float)($_POST['prize_pool'] ?? 0);
    $max_participants = (int)($_POST['max_participants'] ?? 0);
    $max_slots        = (int)($_POST['max_slots'] ?? 0);
    $registration_fee = (float)($_POST['registration_fee'] ?? 0);
    $rules            = trim($_POST['rules'] ?? '');
    
    $start_date       = !empty($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;
    $end_date         = !empty($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null;

    // 🛡️ GUARD 2: Fallback (যদি কেউ 0 বা খালি রাখে, তবে Max Participants এর সমান হবে)
    if ($max_slots <= 0) {
        $max_slots = $max_participants;
    }

    // Basic Validation
    if ($name === '') $errors[] = 'Tournament name is required';
    if ($game === '') $errors[] = 'Game category is required';
    if (!$start_date || !$end_date) $errors[] = 'Tournament dates are required';

    // 🛡️ GUARD 1: Validation (Slots যেন Participants এর চেয়ে বেশি না হয়)
    if ($max_slots > $max_participants) {
        $errors[] = "Max Slots ($max_slots) cannot be greater than Max Participants ($max_participants)";
    }
    
    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                INSERT INTO tournaments 
                (tournament_name, game_category, tournament_type, organizer_id, 
                 prize_pool, max_participants, max_slots, registration_fee, rules, 
                 start_date, end_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Upcoming')
            ");

            $stmt->bind_param(
                "sssidiidsss",
                $name, $game, $type, $_SESSION['user_id'], 
                $prize_pool, $max_participants, $max_slots, $registration_fee, $rules, 
                $start_date, $end_date
            );

            $stmt->execute();
            $tournament_id = $stmt->insert_id;

            // Admin Log
            $log = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, description, ip_address) VALUES (?, 'CREATE_TOURNAMENT', 'tournament', ?, ?, ?)");
            $desc = "Created tournament: $name";
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $log->bind_param("iiss", $_SESSION['user_id'], $tournament_id, $desc, $ip);
            $log->execute();

            $conn->commit();
            $success = true;

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Tournament | ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: #1e293b; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; border-right: 1px solid #334155; }
        .admin-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        .admin-nav a { display: flex; align-items: center; padding: 12px 16px; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
        .admin-nav a i { margin-right: 12px; width: 20px; }
        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        .form-box { max-width: 900px; background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: #fff; box-sizing: border-box; }
        .btn-primary { background: #818cf8; color: #fff; padding: 12px 25px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; margin-top: 20px; }
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
        <div class="form-box">
            <h2>Create New Tournament</h2>
            <hr style="border: 0; border-top: 1px solid #334155; margin-bottom: 25px;">

            <?php if (!empty($errors)): ?>
                <div class="alert error"><?php foreach($errors as $e) echo "<div>$e</div>"; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert success">Tournament created successfully! <a href="tournaments.php" style="color:#fff; text-decoration:underline;">View List</a></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div>
                        <label>Tournament Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div>
                        <label>Game Category</label>
                        <input type="text" name="game" placeholder="Valorant, PUBG, etc." required>
                    </div>
                    <div>
                        <label>Tournament Type</label>
                        <select name="tournament_type">
                            <option value="Public">Public</option>
                            <option value="University">University</option>
                            <option value="Invitational">Invitational</option>
                        </select>
                    </div>
                    <div>
                        <label>Participation Rule (UI Only)</label>
                        <select name="participation_rule">
                            <option value="open">Open for All</option>
                            <option value="university">University Students Only</option>
                        </select>
                    </div>
                    <div>
                        <label>Format (UI Only)</label>
                        <select name="format">
                            <option value="knockout">Single Elimination (Knockout)</option>
                            <option value="league">League / Round Robin</option>
                            <option value="group">Group Stage + Knockout</option>
                        </select>
                    </div>
                    <div>
                        <label>Prize Pool (৳)</label>
                        <input type="number" step="0.01" name="prize_pool">
                    </div>
                    
                    <div>
                        <label>Max Participants</label>
                        <input type="number" name="max_participants" value="16">
                    </div>
                    <div>
                        <label>Max Slots (Join Limit)</label>
                        <input type="number" name="max_slots" value="16" min="1" required>
                    </div>

                    <div>
                        <label>Registration Fee (৳)</label>
                        <input type="number" step="0.01" name="registration_fee" value="0.00">
                    </div>
                    <div>
                        <label>Reg Start (UI Only)</label>
                        <input type="date" name="reg_start">
                    </div>
                    <div>
                        <label>Reg End (UI Only)</label>
                        <input type="date" name="reg_end">
                    </div>
                    <div>
                        <label>Start Date</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div>
                        <label>End Date</label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <label>Rules & Regulations</label>
                    <textarea name="rules" rows="5"></textarea>
                </div>

                <button type="submit" class="btn-primary">Create Tournament</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>