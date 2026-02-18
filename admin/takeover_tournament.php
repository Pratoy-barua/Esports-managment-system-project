<?php
/**
 * Admin Tournament Takeover
 * Path: /admin/takeover_tournament.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized');
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

$tournament_id = (int)($_POST['tournament_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($tournament_id <= 0 || $reason === '') {
    die('Invalid data');
}

$conn->begin_transaction();

try {

    // Fetch tournament
    $t = $conn->prepare("SELECT name, host_type, host_id FROM tournaments WHERE id=?");
    $t->bind_param("i", $tournament_id);
    $t->execute();
    $tournament = $t->get_result()->fetch_assoc();

    if (!$tournament) {
        throw new Exception('Tournament not found');
    }

    if ($tournament['host_type'] !== 'user') {
        throw new Exception('Tournament is already admin-controlled');
    }

    // Revoke hosting request
    $revoke = $conn->prepare("
        UPDATE hosting_requests 
        SET status='revoked' 
        WHERE tournament_id=?
    ");
    $revoke->bind_param("i", $tournament_id);
    $revoke->execute();

    // Take over tournament
    $update = $conn->prepare("
        UPDATE tournaments 
        SET host_type='admin', host_id=NULL 
        WHERE id=?
    ");
    $update->bind_param("i", $tournament_id);
    $update->execute();

    // Notify original host
    $notify = $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, notification_type, link_url)
        VALUES (?, 'Hosting Revoked', ?, 'tournament', ?)
    ");
    $msg = "Your hosting rights for tournament '{$tournament['name']}' have been revoked. Reason: $reason";
    $link = "tournament.php?id=$tournament_id";
    $notify->bind_param("iss", $tournament['host_id'], $msg, $link);
    $notify->execute();

    // Admin log
    $log = $conn->prepare("
        INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address)
        VALUES (?, 'TAKEOVER_TOURNAMENT', 'tournament', ?, ?, ?)
    ");
    $desc = "Admin took over hosted tournament. Reason: $reason";
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $log->bind_param("iiss", $_SESSION['user_id'], $tournament_id, $desc, $ip);
    $log->execute();

    $conn->commit();

    header("Location: view_tournament.php?id=$tournament_id&takeover=success");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die('Takeover failed');
}
