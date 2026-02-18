<?php
/**
 * Update Tournament Status
 * Path: /admin/update_tournament_status.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

$tournament_id = (int)($_POST['tournament_id'] ?? 0);
$new_status    = trim($_POST['new_status'] ?? '');
$reason        = trim($_POST['reason'] ?? '');

if ($tournament_id <= 0 || $new_status === '') {
    die('Invalid data');
}

/* ===============================
   FETCH CURRENT TOURNAMENT
================================ */
$stmt = $conn->prepare("
    SELECT tournament_name, status 
    FROM tournaments 
    WHERE tournament_id = ?
");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();

if (!$tournament) {
    die('Tournament not found');
}

$current_status  = $tournament['status'];
$tournament_name = $tournament['tournament_name'];

/* ===============================
   VALID STATUS FLOW (ADMIN RULE)
================================ */
$valid_flow = [
    'Upcoming'  => ['Ongoing', 'Cancelled'],
    'Ongoing'   => ['Completed', 'Cancelled'],
    'Completed' => [],
    'Cancelled' => []
];

if (!isset($valid_flow[$current_status]) ||
    !in_array($new_status, $valid_flow[$current_status])) {
    die('Invalid status transition');
}

if ($new_status === 'Cancelled' && $reason === '') {
    die('Cancellation reason required');
}

/* ===============================
   TRANSACTION
================================ */
$conn->begin_transaction();

try {
    // Update status
    $up = $conn->prepare("
        UPDATE tournaments 
        SET status = ? 
        WHERE tournament_id = ?
    ");
    $up->bind_param("si", $new_status, $tournament_id);
    $up->execute();

    /* ===============================
       NOTIFY PARTICIPANTS
    ================================ */
    $msg = "Tournament \"$tournament_name\" status updated to $new_status";
    if ($new_status === 'Cancelled') {
        $msg .= ". Reason: $reason";
    }

    $users = $conn->prepare("
        SELECT DISTINCT user_id 
        FROM participants 
        WHERE tournament_id = ?
    ");
    $users->bind_param("i", $tournament_id);
    $users->execute();
    $res = $users->get_result();

    $notif = $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, notification_type)
        VALUES (?, 'Tournament Update', ?, 'Tournament')
    ");

    while ($u = $res->fetch_assoc()) {
        $notif->bind_param("is", $u['user_id'], $msg);
        $notif->execute();
    }

    /* ===============================
       ADMIN LOG
    ================================ */
    $log = $conn->prepare("
        INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address)
        VALUES (?, 'UPDATE_TOURNAMENT_STATUS', 'tournament', ?, ?, ?)
    ");
    $desc = "Status changed: $current_status → $new_status";
    if ($reason) $desc .= " | Reason: $reason";
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $log->bind_param(
        "iiss",
        $_SESSION['user_id'],
        $tournament_id,
        $desc,
        $ip
    );
    $log->execute();

    $conn->commit();

    header("Location: view_tournament.php?id=$tournament_id&success=status_updated");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die('Status update failed');
}
