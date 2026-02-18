<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();
$conn = getConnection();

// logged-in user
$user_id = $_SESSION['user_id'] ?? 0;

/* =========================
   Mark single notification as read
========================= */
if (isset($_GET['read']) && is_numeric($_GET['read'])) {

    // ✅ FIX: correct column name
    $notification_id = (int) $_GET['read'];

    // ownership + link check
    $stmt = $conn->prepare("
        SELECT link_url
        FROM notifications
        WHERE notification_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    $notification = $stmt->get_result()->fetch_assoc();

    if ($notification) {

        // mark as read
        $upd = $conn->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE notification_id = ?
        ");
        $upd->bind_param("i", $notification_id);
        $upd->execute();

        // redirect if link exists
        if (!empty($notification['link_url'])) {
            header("Location: " . $notification['link_url']);
            exit;
        }
    }

    header("Location: notifications.php");
    exit;
}

/* =========================
   Mark all as read
========================= */
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {

    $stmt = $conn->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    header("Location: notifications.php?success=all_read");
    exit;
}

/* =========================
   Fetch notifications
========================= */
$stmt = $conn->prepare("
    SELECT notification_id, title, message, notification_type, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =========================
   Unread count
========================= */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ? AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body class="dashboard-body">

<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="top-bar">
            <div>
                <h1>Notifications</h1>
                <p><?= $unread_count ?> unread</p>
            </div>

            <?php if ($unread_count > 0): ?>
                <a href="?action=mark_all_read" class="btn btn-secondary">
                    Mark All as Read
                </a>
            <?php endif; ?>
        </div>

        <div class="notification-list">

            <?php if (empty($notifications)): ?>
                <p>No notifications yet</p>
            <?php else: ?>

                <?php foreach ($notifications as $n): ?>
                    <div class="notification-item <?= $n['is_read'] ? '' : 'unread' ?>"
                         onclick="window.location.href='notifications.php?read=<?= $n['notification_id'] ?>'">

                        <div>
                            <h4><?= htmlspecialchars($n['title']) ?></h4>
                            <p><?= htmlspecialchars($n['message']) ?></p>
                            <small><?= date('M d, Y H:i', strtotime($n['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </main>
</div>

</body>
</html>
