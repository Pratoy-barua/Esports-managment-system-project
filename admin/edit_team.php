<?php


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

$team_id  = (int) $_GET['id'];
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['full_name'];
$conn     = getConnection();


$stmt = $conn->prepare("SELECT team_id, team_name, description, team_logo FROM teams WHERE team_id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$team) {
    header('Location: teams.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name   = trim($_POST['team_name']);
    $description = trim($_POST['description']);
    $logoPath    = $team['team_logo'];

    if ($team_name === '') {
        $error = "Team name is required";
    }

    // Logo upload
    if (!isset($error) && !empty($_FILES['logo']['name'])) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Invalid format (JPG, PNG, WEBP only)";
        } else {
            $uploadDir = __DIR__ . '/../uploads/teams/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newName = 'team_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newName)) {
                $logoPath = 'uploads/teams/' . $newName;
            }
        }
    }

    if (!isset($error)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE teams SET team_name = ?, description = ?, team_logo = ? WHERE team_id = ?");
            $stmt->bind_param("sssi", $team_name, $description, $logoPath, $team_id);
            $stmt->execute();

            // Log activity
            $logStmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, description, ip_address, created_at) VALUES (?, 'EDIT_TEAM', 'team', ?, ?, ?, NOW())");
            $desc = "Updated team info for: $team_name";
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $logStmt->bind_param("iiss", $admin_id, $team_id, $desc, $ip);
            $logStmt->execute();

            $conn->commit();
            header("Location: view_team.php?id={$team_id}&success=updated");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Update failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Team - ESportsHub Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Hubahu Dashboard Theme --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #e2e8f0; }
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar Fixed Sync */
        .admin-sidebar {
            width: 260px; background: #1e293b; padding: 20px;
            position: fixed; height: 100vh; overflow-y: auto;
            border-right: 1px solid #334155;
        }
        .admin-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        
        .admin-nav a {
            display: flex; align-items: center; padding: 12px 16px;
            color: #cbd5e1; text-decoration: none; border-radius: 8px;
            margin-bottom: 5px; transition: all 0.3s;
        }
        .admin-nav a:hover, .admin-nav a.active { background: #334155; color: #818cf8; }
        .admin-nav a i { margin-right: 12px; width: 20px; }

        /* Content Offset */
        .admin-content { margin-left: 260px; flex: 1; padding: 30px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #818cf8; }

        /* Form Styling */
        .form-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 30px; max-width: 700px; }
        label { display: block; margin-top: 20px; color: #94a3b8; font-size: 14px; font-weight: 600; }
        input[type="text"], textarea, input[type="file"] { 
            width: 100%; padding: 12px; margin-top: 8px; 
            background: #0f172a; border: 1px solid #334155; 
            color: #fff; border-radius: 8px; outline: none;
        }
        .btn-save { 
            background: #818cf8; color: #fff; border: none; 
            padding: 12px 25px; border-radius: 8px; 
            margin-top: 25px; cursor: pointer; font-weight: bold; width: 100%;
        }
        .btn-save:hover { background: #6366f1; }
        .current-logo-box { margin-top: 15px; padding: 15px; background: #0f172a; border-radius: 8px; display: inline-block; }
        .btn-back { color: #cbd5e1; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 14px; }
        .btn-back:hover { color: #818cf8; }
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
                <h1>Edit Team</h1>
                <div class="admin-user">
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div style="font-size: 13px; color: #64748b;">Administrator</div>
                    </div>
                    <img src="../assets/images/default-avatar.png" alt="Admin">
                </div>
            </div>

            <a href="view_team.php?id=<?= $team_id ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Team Details
            </a>

            <?php if (isset($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ef4444;">
                    <i class="fas fa-circle-exclamation"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" enctype="multipart/form-data">
                    <label>Team Name</label>
                    <input type="text" name="team_name" value="<?= htmlspecialchars($team['team_name']) ?>" required>

                    <label>Team Description</label>
                    <textarea name="description" rows="5" placeholder="Write something about the team..."><?= htmlspecialchars($team['description']) ?></textarea>

                    <label>Team Logo (Optional)</label>
                    <input type="file" name="logo" accept="image/*">

                    <?php if (!empty($team['team_logo'])): ?>
                        <div class="current-logo-box">
                            <p style="font-size: 12px; color: #94a3b8; margin-bottom: 10px;">Current Logo:</p>
                            <img src="../<?= htmlspecialchars($team['team_logo']) ?>" style="height:80px; border-radius: 4px; display: block;">
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-check-circle"></i> Update Team Info
                    </button>
                </form>
            </div>
        </main>
    </div>

    
</body>
</html>