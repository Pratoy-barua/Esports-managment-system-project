<?php
/**
 * Admin - View Subscription Details
 * Path: /admin/view_subscription.php
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();

// =======================
// Validate ID
// =======================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: subscriptions.php?error=invalid_id");
    exit;
}

$subscription_id = (int) $_GET['id'];

// =======================
// Fetch subscription details
// =======================
$stmt = $conn->prepare("
    SELECT 
        s.subscription_id,
        s.start_date,
        s.end_date,
        s.is_active,
        s.created_at,

        u.full_name,
        u.username,
        u.email,

        sp.student_id_number,

        uni.university_name,
        dept.department_name

    FROM subscriptions s
    JOIN users u 
        ON u.user_id = s.user_id

    LEFT JOIN student_profiles sp 
        ON sp.user_id = u.user_id

    LEFT JOIN universities uni 
        ON uni.university_id = sp.university_id

    LEFT JOIN departments dept 
        ON dept.department_id = sp.department_id

    WHERE s.subscription_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $subscription_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: subscriptions.php?error=not_found");
    exit;
}

$sub = $result->fetch_assoc();
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Subscription</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    background:#0f172a;
    color:#e2e8f0;
    font-family:Segoe UI, sans-serif;
    margin:0;
}
.admin-content{
    margin-left:260px;
    padding:30px;
}
.card{
    background:#1e293b;
    padding:30px;
    border-radius:14px;
    max-width:900px;
}
h1{margin-bottom:10px}
.label{
    font-size:13px;
    color:#94a3b8;
    margin-bottom:5px;
}
.value{
    font-size:15px;
    color:#f1f5f9;
}
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:25px;
}
.badge-active{color:#10b981;font-weight:bold}
.badge-inactive{color:#ef4444;font-weight:bold}
a{color:#818cf8;text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>

<body>
<div class="admin-content">

    <a href="subscriptions.php">← Back to Subscriptions</a>

    <h1><i class="fas fa-crown"></i> Subscription Details</h1>

    <div class="card">
        <div class="grid">

            <div>
                <div class="label">Student Name</div>
                <div class="value"><?= htmlspecialchars($sub['full_name']); ?></div>
            </div>

            <div>
                <div class="label">Username</div>
                <div class="value">@<?= htmlspecialchars($sub['username']); ?></div>
            </div>

            <div>
                <div class="label">Email</div>
                <div class="value"><?= htmlspecialchars($sub['email']); ?></div>
            </div>

            <div>
                <div class="label">University</div>
                <div class="value"><?= htmlspecialchars($sub['university_name'] ?? 'N/A'); ?></div>
            </div>

            <div>
                <div class="label">Department</div>
                <div class="value"><?= htmlspecialchars($sub['department_name'] ?? 'N/A'); ?></div>
            </div>

            <div>
                <div class="label">Student ID</div>
                <div class="value">
                    <?= $sub['student_id_number']
                        ? substr($sub['student_id_number'], 0, 3) . '****'
                        : 'N/A'; ?>
                </div>
            </div>

            <div>
                <div class="label">Subscription Status</div>
                <div class="value">
                    <?= $sub['is_active']
                        ? '<span class="badge-active">Active</span>'
                        : '<span class="badge-inactive">Inactive</span>'; ?>
                </div>
            </div>

            <div>
                <div class="label">Start Date</div>
                <div class="value"><?= date('M d, Y', strtotime($sub['start_date'])); ?></div>
            </div>

            <div>
                <div class="label">Expiry Date</div>
                <div class="value"><?= date('M d, Y', strtotime($sub['end_date'])); ?></div>
            </div>

            <div>
                <div class="label">Created At</div>
                <div class="value"><?= date('M d, Y h:i A', strtotime($sub['created_at'])); ?></div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
