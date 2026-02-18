<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id']; // User ID for checking participation

if (!isset($_GET['id'])) {
    header("Location: tournaments.php");
    exit();
}

$tournament_id = (int)$_GET['id'];

// Fetch tournament details
$sql = "SELECT * FROM tournaments WHERE tournament_id = $tournament_id";
$tournament = getSingleRow($conn, $sql);

if (!$tournament) {
    header("Location: tournaments.php?error=Tournament not found");
    exit();
}

// Check if user already joined
$checkSql = "SELECT * FROM participants WHERE tournament_id = $tournament_id AND user_id = $user_id";
$is_joined = getSingleRow($conn, $checkSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($tournament['tournament_name']) ?> | Tournament Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="dashboard-body">
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>Tournament Details</h1>
        </div>

        <div class="dashboard-content">

            <div style="background:#1e293b;padding:25px;border-radius:12px;border:1px solid #334155;max-width:800px;">
                
                <h2 style="margin-bottom:10px;">
                    <?= htmlspecialchars($tournament['tournament_name']) ?>
                </h2>

                <p style="color:#818cf8;margin-bottom:15px;">
                    <?= htmlspecialchars($tournament['game_category']) ?>
                </p>

                <div style="margin-bottom:15px;">
                    <strong>Status:</strong>
                    <span style="color:#10b981;">
                        <?= strtoupper($tournament['status']) ?>
                    </span>
                </div>

                <div style="margin-bottom:15px;">
                    <strong>Registration Fee:</strong>
                    ৳<?= number_format($tournament['registration_fee'], 0) ?>
                </div>

                <div style="margin-bottom:15px;">
                    <strong>Prize Pool:</strong>
                    ৳<?= number_format($tournament['prize_pool'], 0) ?>
                </div>

                <div style="margin-bottom:15px;">
                    <strong>Start Date:</strong>
                    <?= htmlspecialchars($tournament['start_date']) ?>
                </div>

                <div style="margin-bottom:15px;">
                    <strong>End Date:</strong>
                    <?= htmlspecialchars($tournament['end_date']) ?>
                </div>

                <div style="margin-bottom:20px;">
                    <strong>Rules:</strong>
                    <p style="margin-top:8px;color:#cbd5e1;line-height:1.6;">
                        <?= nl2br(htmlspecialchars($tournament['rules'])) ?>
                    </p>
                </div>

                <?php if ($is_joined): ?>
                    <button class="btn btn-success" disabled style="margin-bottom:15px; width:100%;">Already Joined</button>
                <?php elseif (
                    strtolower($tournament['status']) === 'upcoming' &&
                    $tournament['is_suspended'] == 0 &&
                    $tournament['join_locked'] == 0
                ): ?>
                    <button 
                        class="btn btn-primary"
                        onclick="openJoinModal(
                            <?= $tournament['tournament_id'] ?>,
                            '<?= htmlspecialchars($tournament['tournament_name']) ?>',
                            <?= $tournament['registration_fee'] ?>
                        )"
                        style="margin-bottom:15px; width: 100%;"
                    >
                        Join Tournament
                    </button>
                <?php endif; ?>

                <a href="tournaments.php" class="btn btn-secondary" style="display:block; text-align:center;">
                    ← Back to Tournaments
                </a>
            </div>

        </div>
    </main>
</div>

<?php include 'includes/join_modal.php'; ?>

</body>
</html>