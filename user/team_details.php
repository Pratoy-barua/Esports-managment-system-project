<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$team_id = (int)($_GET['id'] ?? 0);

/* Fetch team */
$stmt = $conn->prepare("
    SELECT team_id, team_name, game_category, description, team_logo, captain_id
    FROM teams
    WHERE team_id = ?
");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$team) die("Team not found");

/* Members */
$stmt = $conn->prepare("
    SELECT u.username, tm.role
    FROM team_members tm
    JOIN users u ON u.user_id = tm.user_id
    WHERE tm.team_id = ?
");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$isLeader = ($team['captain_id'] == $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($team['team_name']) ?> | Team</title>

    <!-- SAME CSS AS teams.php -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="dashboard-body">
<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="top-bar">
            <h1><?= htmlspecialchars($team['team_name']) ?></h1>
            <?php if ($isLeader): ?>
                <a href="add_team_member.php?team_id=<?= $team_id ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add Member
                </a>
            <?php endif; ?>
        </div>

        <div class="dashboard-content">

            <div class="section">
                <h2>Team Information</h2>

                <div class="tournament-card">
                    <p><strong>Game:</strong> <?= htmlspecialchars($team['game_category']) ?></p>
                    <?php if ($team['description']): ?>
                        <p style="margin-top:10px;color:var(--text-muted);">
                            <?= htmlspecialchars($team['description']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section">
                <h2>Team Members</h2>

                <div class="tournament-grid">
                    <?php foreach ($members as $m): ?>
                        <div class="tournament-card">
                            <strong>@<?= htmlspecialchars($m['username']) ?></strong><br>
                            <span class="tournament-badge">
                                <?= htmlspecialchars($m['role']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="teams.php" class="btn btn-secondary" style="margin-top:20px;">
                ← Back to My Teams
            </a>

        </div>
    </main>
</div>
</body>
</html>
