<?php
/**
 * Admin - View Hosting Request (Perfect Sidebar Sync)
 * Path: /admin/view_request.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$conn = getConnection();
$admin_name = $_SESSION['full_name'];

/* =========================
    Validate Request ID
========================= */
$request_id = (int)($_GET['id'] ?? 0);
if ($request_id <= 0) {
    die('Invalid request ID');
}

/* =========================
    Fetch Hosting Request
========================= */
$stmt = $conn->prepare("
    SELECT 
        hr.*,
        u.full_name,
        u.username,
        u.profession,
        uni.university_name AS university_display_name
    FROM hosting_requests hr
    INNER JOIN users u ON u.user_id = hr.user_id
    LEFT JOIN student_profiles sp ON sp.user_id = u.user_id
    LEFT JOIN universities uni ON uni.university_id = sp.university_id
    WHERE hr.request_id = ?
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    die('Hosting request not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Hosting Request - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Dashboard Theme Sync --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        
        .admin-layout { display: flex; min-height: 100vh; }
        
        /* --- Sidebar Match --- */
        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #334155;
        }
        
        .admin-logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }
        
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #334155;
            color: #818cf8;
        }
        
        .admin-nav a i { margin-right: 12px; width: 20px; }
        
        /* --- Content Area Match --- */
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #818cf8; }

        /* --- Custom Page Styling --- */
        .box {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #334155;
        }

        .box h3 {
            margin-bottom: 20px;
            color: #818cf8;
            border-bottom: 1px solid #334155;
            padding-bottom: 10px;
        }

        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .info-item label { display: block; color: #94a3b8; font-size: 13px; margin-bottom: 5px; }
        .info-item p { margin: 0; font-weight: 600; }

        .badge {
            padding: 6px 16px; border-radius: 20px; font-size: 12px;
            font-weight: bold; text-transform: uppercase;
        }
        .badge.pending { background: #78350f; color: #fbbf24; }
        .badge.approved { background: #065f46; color: #34d399; }
        .badge.rejected { background: #7f1d1d; color: #f87171; }

        .btn {
            padding: 10px 20px; border-radius: 8px; text-decoration: none;
            font-size: 14px; font-weight: 600; border: none; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .btn-success { background: #10b981; color: #fff; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-warning { background: #f59e0b; color: #000; }
        .btn-secondary { background: #334155; color: #fff; }

        textarea {
            width: 100%; background: #0f172a; color: #fff;
            border: 1px solid #334155; border-radius: 8px; padding: 12px;
            resize: vertical; min-height: 100px; margin-top: 10px;
        }
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
                <a href="hosting.php" class="active"><i class="fas fa-calendar-check"></i> Hosting Requests</a>
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
                <h1>Review Hosting Request</h1>
                <div class="admin-user">
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div style="font-size: 13px; color: #64748b;">Administrator</div>
                    </div>
                    <img src="../assets/images/default-avatar.png" alt="Admin">
                </div>
            </div>

            <a href="hosting.php" class="btn btn-secondary" style="margin-bottom: 25px;">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>

            <div class="box">
                <h3><i class="fas fa-user-tie"></i> Host Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Full Name</label>
                        <p><?= htmlspecialchars($request['full_name']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Username</label>
                        <p>@<?= htmlspecialchars($request['username']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Profession</label>
                        <p><?= htmlspecialchars($request['profession']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Organization / University</label>
                        <p><?= htmlspecialchars($request['university_display_name'] ?? 'General User') ?></p>
                    </div>
                </div>
            </div>

            <div class="box">
                <h3><i class="fas fa-gamepad"></i> Event Details</h3>
                <div class="info-grid" style="margin-bottom: 20px;">
                    <div class="info-item">
                        <label>Event Name</label>
                        <p><?= htmlspecialchars($request['event_name']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Game Category</label>
                        <p><?= htmlspecialchars($request['game_category']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Prize Pool</label>
                        <p style="color: #10b981;">৳<?= number_format($request['prize_pool']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Expected Participants</label>
                        <p><?= (int)$request['expected_participants'] ?></p>
                    </div>
                </div>
                <div class="info-item">
                    <label>Timeline</label>
                    <p><?= date('d M, Y', strtotime($request['start_date'])) ?> — <?= date('d M, Y', strtotime($request['end_date'])) ?></p>
                </div>
            </div>

            <div class="box">
                <h3><i class="fas fa-scroll"></i> Rules & Regulations</h3>
                <div style="background: #0f172a; padding: 15px; border-radius: 8px; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($request['rules'])) ?>
                </div>
            </div>

            <?php if ($request['status'] === 'Pending'): ?>
            <div class="box" style="border-top: 4px solid #818cf8;">
                <h3><i class="fas fa-gavel"></i> Decision Panel</h3>
                <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                    <form method="POST" action="approve_request.php">
                        <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this request?');">
                            <i class="fas fa-check"></i> Approve Request
                        </button>
                    </form>

                    <a href="modify_request.php?id=<?= $request['request_id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Request Modification
                    </a>
                </div>

                <form method="POST" action="reject_request.php" style="border-top: 1px solid #334155; padding-top: 20px;">
                    <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                    <label><strong>Reason for Rejection</strong></label>
                    <textarea name="reason" required placeholder="Write why this request is being rejected..."></textarea>
                    <button type="submit" class="btn btn-danger" style="margin-top: 15px;" onclick="return confirm('Confirm rejection?');">
                        <i class="fas fa-times"></i> Confirm Rejection
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="box">
                <label>Current Status:</label>
                <span class="badge <?= strtolower($request['status']) ?>"><?= $request['status'] ?></span>
            </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>