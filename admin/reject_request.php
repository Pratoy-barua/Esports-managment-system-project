<?php
/**
 * Admin - Reject Hosting Request
 * Path: /admin/reject_request.php
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

/* =========================
   Security: Admin Only
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

$conn = getConnection();

/* =========================
   Validate Input
========================= */
$request_id = (int)($_POST['request_id'] ?? 0);
$reason     = trim($_POST['reason'] ?? '');

if ($request_id <= 0 || $reason === '') {
    die('Invalid input');
}

$conn->begin_transaction();

try {

    /* ===============================
       Lock & Fetch Hosting Request
    ================================ */
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
        throw new Exception('Request not found or already processed');
    }

    /* ===============================
       Update Hosting Request Status
    ================================ */
    $up = $conn->prepare("
        UPDATE hosting_requests
        SET status = 'Rejected',
            rejection_reason = ?,
            reviewed_by = ?,
            reviewed_at = NOW()
        WHERE request_id = ?
    ");
    $up->bind_param(
        "sii",
        $reason,
        $_SESSION['user_id'],
        $request_id
    );
    $up->execute();

    /* ===============================
       Notify Host
    ================================ */
    $title   = "Hosting Request Rejected";
    $message = "Your hosting request '{$req['event_name']}' has been rejected.\n\nReason:\n$reason";

    $notif = $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, notification_type, created_at)
        VALUES (?, ?, ?, 'hosting', NOW())
    ");
    $notif->bind_param(
        "iss",
        $req['user_id'],
        $title,
        $message
    );
    $notif->execute();

    /* ===============================
       Admin Log
    ================================ */
    $desc = "Rejected hosting request ID $request_id";
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $log = $conn->prepare("
        INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address, created_at)
        VALUES (?, 'REJECT_HOSTING', 'hosting_request', ?, ?, ?, NOW())
    ");
    $log->bind_param(
        "iiss",
        $_SESSION['user_id'],
        $request_id,
        $desc,
        $ip
    );
    $log->execute();

    /* ===============================
       Commit
    ================================ */
    $conn->commit();

    header("Location: hosting.php?rejected=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Rejection failed. Please try again.");
}
