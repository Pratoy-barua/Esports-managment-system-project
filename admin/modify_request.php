<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$conn = getConnection();


$request_id = (int)($_GET['id'] ?? $_POST['request_id'] ?? 0);
if ($request_id <= 0) {
    die('Invalid request ID');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $remarks = trim($_POST['remarks'] ?? '');

    if ($remarks === '') {
        die('Remarks are required');
    }

    $conn->begin_transaction();

    try {
        /* Lock request */
        $stmt = $conn->prepare("
            SELECT user_id, event_name
            FROM hosting_requests
            WHERE request_id = ?
              AND status = 'Pending'
            FOR UPDATE
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $req = $stmt->get_result()->fetch_assoc();

        if (!$req) {
            throw new Exception('Request not found or not editable');
        }

        /* Update request with admin remarks */
        $up = $conn->prepare("
            UPDATE hosting_requests
            SET admin_notes = ?,
                reviewed_by = ?,
                reviewed_at = NOW()
            WHERE request_id = ?
        ");
        $up->bind_param(
            "sii",
            $remarks,
            $_SESSION['user_id'],
            $request_id
        );
        $up->execute();

        /* Notify host */
        $title = "Hosting Request Needs Modification";
        $msg = "Your hosting request '{$req['event_name']}' requires modification.\n\nAdmin remarks:\n$remarks";

        $notif = $conn->prepare("
            INSERT INTO notifications
            (user_id, title, message, notification_type, created_at)
            VALUES (?, ?, ?, 'hosting', NOW())
        ");
        $notif->bind_param("iss", $req['user_id'], $title, $msg);
        $notif->execute();

        /* Admin log */
        $desc = "Requested modification for hosting request ID $request_id";
        $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $log = $conn->prepare("
            INSERT INTO admin_logs
            (admin_id, action, target_type, target_id, description, ip_address, created_at)
            VALUES (?, 'MODIFY_HOSTING', 'hosting_request', ?, ?, ?, NOW())
        ");
        $log->bind_param(
            "iiss",
            $_SESSION['user_id'],
            $request_id,
            $desc,
            $ip
        );
        $log->execute();

        $conn->commit();

        header("Location: hosting.php?modified=1");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Modification request failed");
    }
}


$stmt = $conn->prepare("
    SELECT hr.event_name, hr.game_category, hr.event_type,
           u.full_name, u.username
    FROM hosting_requests hr
    JOIN users u ON u.user_id = hr.user_id
    WHERE hr.request_id = ?
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die('Hosting request not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Modification | Admin</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        body {
            background:#0f172a;
            color:#e2e8f0;
            font-family:'Segoe UI',sans-serif;
        }
        .box {
            background:#1e293b;
            border:1px solid #334155;
            border-radius:12px;
            padding:20px;
            margin-bottom:25px;
        }
        textarea {
            width:100%;
            min-height:140px;
            background:#0f172a;
            color:white;
            border:1px solid #334155;
            border-radius:8px;
            padding:12px;
            resize:vertical;
        }
        .btn {
            padding:10px 20px;
            border-radius:6px;
            border:none;
            font-weight:600;
            cursor:pointer;
        }
        .btn-warning { background:#f59e0b; color:black; }
        .btn-secondary { background:#334155; color:white; text-decoration:none; }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="admin-content">

        <a href="view_request.php?id=<?= $request_id ?>" class="btn btn-secondary">← Back</a>

        <h1 style="margin-top:20px;">Request Modification</h1>

        <div class="box">
            <p><strong>Host:</strong> <?= htmlspecialchars($data['full_name']) ?> (@<?= htmlspecialchars($data['username']) ?>)</p>
            <p><strong>Event:</strong> <?= htmlspecialchars($data['event_name']) ?></p>
            <p><strong>Game:</strong> <?= htmlspecialchars($data['game_category']) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($data['event_type']) ?></p>
        </div>

        <form method="POST">
            <input type="hidden" name="request_id" value="<?= $request_id ?>">

            <div class="box">
                <h3>Admin Remarks (Required)</h3>
                <textarea name="remarks" required placeholder="Explain what needs to be changed by the host..."></textarea>
            </div>

            <button type="submit" class="btn btn-warning">
                Send Modification Request
            </button>
        </form>

    </main>
</div>

</body>
</html>
<?php $conn->close(); ?>
