<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();
$conn = getConnection();

/* ===============================
   VALIDATE ID
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: notifications.php");
    exit;
}

$notification_id = (int) $_GET['id'];

/* ===============================
   FETCH NOTIFICATION
================================ */
$stmt = $conn->prepare("
    SELECT an.*, u.full_name
    FROM admin_notifications an
    JOIN users u ON an.created_by = u.user_id
    WHERE an.id = ?
");
$stmt->bind_param("i", $notification_id);
$stmt->execute();
$notification = $stmt->get_result()->fetch_assoc();

if (!$notification) {
    header("Location: notifications.php");
    exit;
}

/* ===============================
   DELIVERY STATS
================================ */
$totalRecipients = $conn->query("
    SELECT COUNT(*) total 
    FROM notifications 
    WHERE admin_notification_id = {$notification_id}
")->fetch_assoc()['total'];

$readCount = $conn->query("
    SELECT COUNT(*) total 
    FROM notifications 
    WHERE admin_notification_id = {$notification_id} AND is_read = 1
")->fetch_assoc()['total'];

$unreadCount = $totalRecipients - $readCount;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>View Notification</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{margin:0;font-family:Segoe UI;background:#0f172a;color:#e2e8f0}
.admin-layout{display:flex}
.admin-sidebar{width:260px;background:#1e293b;padding:20px;position:fixed;height:100vh}
.admin-content{margin-left:260px;padding:30px;flex:1}

h1{margin-bottom:5px}
.subtitle{color:#64748b;margin-bottom:25px}

.card{background:#1e293b;padding:25px;border-radius:14px;border:1px solid #334155;max-width:900px}
.field{margin-bottom:18px}
.label{font-size:13px;color:#94a3b8;margin-bottom:4px}
.value{font-size:15px;color:#f1f5f9}

.badge{padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600}
.badge-draft{background:#f59e0b;color:#000}
.badge-sent{background:#10b981;color:#fff}

.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-top:20px}
.stat{background:#0f172a;padding:15px;border-radius:10px;border:1px solid #334155;text-align:center}
.stat span{font-size:12px;color:#94a3b8}
.stat h2{margin-top:5px}

.back{display:inline-block;margin-bottom:20px;color:#818cf8;text-decoration:none}
.back:hover{text-decoration:underline}
</style>
</head>
<body>

<div class="admin-layout">

<!-- SIDEBAR -->
<aside class="admin-sidebar">
    <div style="text-align:center;padding-bottom:20px;border-bottom:1px solid #334155">
        <h2 style="color:#818cf8"><i class="fas fa-gamepad"></i> ESportsHub</h2>
        <p style="font-size:12px;color:#64748b">Admin Panel</p>
    </div>
    <nav style="margin-top:20px">
        <a href="dashboard.php" class="btn" style="display:block;color:#cbd5e1">Dashboard</a>
        <a href="notifications.php" class="btn" style="display:block;background:#334155;color:#818cf8;margin-top:5px">Notifications</a>
        <a href="logs.php" class="btn" style="display:block;color:#cbd5e1;margin-top:5px">Activity Logs</a>
        <a href="logout.php" class="btn" style="display:block;color:#cbd5e1;margin-top:5px">Logout</a>
    </nav>
</aside>

<!-- CONTENT -->
<main class="admin-content">

<a href="notifications.php" class="back">← Back to Notifications</a>

<h1><?= htmlspecialchars($notification['title']) ?></h1>
<div class="subtitle">Notification Details (Read-only)</div>

<div class="card">

<div class="field">
    <div class="label">Status</div>
    <span class="badge <?= $notification['status']=='sent'?'badge-sent':'badge-draft' ?>">
        <?= ucfirst($notification['status']) ?>
    </span>
</div>

<div class="field">
    <div class="label">Type</div>
    <div class="value"><?= $notification['type'] ?></div>
</div>

<div class="field">
    <div class="label">Target Audience</div>
    <div class="value"><?= $notification['target_type'] ?> <?= $notification['target_value'] ? '('.$notification['target_value'].')' : '' ?></div>
</div>

<div class="field">
    <div class="label">Short Message</div>
    <div class="value"><?= htmlspecialchars($notification['short_message']) ?></div>
</div>

<div class="field">
    <div class="label">Full Message</div>
    <div class="value"><?= nl2br(htmlspecialchars($notification['full_message'])) ?></div>
</div>

<div class="field">
    <div class="label">Created By</div>
    <div class="value"><?= htmlspecialchars($notification['full_name']) ?></div>
</div>

<div class="field">
    <div class="label">Created At</div>
    <div class="value"><?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?></div>
</div>

<!-- DELIVERY STATS -->
<div class="stats">
    <div class="stat"><span>Total Recipients</span><h2><?= $totalRecipients ?></h2></div>
    <div class="stat"><span>Read</span><h2><?= $readCount ?></h2></div>
    <div class="stat"><span>Unread</span><h2><?= $unreadCount ?></h2></div>
</div>

</div>
</main>
</div>

</body>
</html>
