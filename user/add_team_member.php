<?php
/**
 * User Side - Add Team Member
 * Path: /user/add_team_member.php
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireLogin(); // ✅ Step 1: User login check

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$team_id = (int)($_GET['team_id'] ?? 0);

/* ===============================
    ✅ Step 2: Permission Check
   =============================== */
$stmt = $conn->prepare("SELECT team_name, captain_id FROM teams WHERE team_id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

// যদি টিম না পাওয়া যায় বা লগইন করা ইউজার ক্যাপটেন না হয়
if (!$team || $team['captain_id'] != $user_id) {
    header("Location: teams.php?error=Unauthorized access");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, trim($_POST['username']));

    /* ===============================
        ✅ Step 3: Find Username
       =============================== */
    $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $target_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$target_user) {
        $error = "User '@$username' not found in ESportsHub.";
    } else {
        $target_user_id = $target_user['user_id'];

        /* ===============================
            ✅ Step 4: Duplicate Check
           =============================== */
        $stmt = $conn->prepare("SELECT 1 FROM team_members WHERE team_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $team_id, $target_user_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows;
        $stmt->close();

        if ($exists) {
            $error = "User '@$username' is already a member of this team.";
        } else {
            /* ===============================
                ✅ Step 5: Insert Member
               =============================== */
            $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'Member')");
            $stmt->bind_param("ii", $team_id, $target_user_id);
            
            if ($stmt->execute()) {
                // ইউজারকে জানানোর জন্য নোটিফিকেশন পাঠানো (Optional but professional)
                $notif_msg = "You have been added to team '{$team['team_name']}' by its captain.";
                $notif = $conn->prepare("INSERT INTO notifications (user_id, title, message, notification_type) VALUES (?, 'New Team!', ?, 'Team')");
                $notif->bind_param("is", $target_user_id, $notif_msg);
                $notif->execute();
                
                header("Location: team_details.php?id=$team_id&success=Member added successfully");
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Member | <?= htmlspecialchars($team['team_name']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-card {
            background: #1e293b;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #334155;
            max-width: 500px;
            margin-top: 20px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #94a3b8; }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #0f172a;
            border: 1px solid #334155;
            color: #fff;
            border-radius: 8px;
            outline: none;
        }
        .form-group input:focus { border-color: #818cf8; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="top-bar">
                <h1>Add Member to "<?= htmlspecialchars($team['team_name']) ?>"</h1>
                <a href="team_details.php?id=<?= $team_id ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Details
                </a>
            </div>

            <div class="dashboard-content">
                <div class="form-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Player Username</label>
                            <input type="text" name="username" id="username" 
                                   placeholder="Enter exact username (e.g. pro_gamer123)" required>
                            <small style="color: #64748b; display: block; margin-top: 8px;">
                                <i class="fas fa-info-circle"></i> The user must be registered on ESportsHub.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-user-plus"></i> Add to Team
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>