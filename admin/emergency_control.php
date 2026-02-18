<?php


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

$tournament_id     = (int)($_POST['tournament_id'] ?? 0);
$emergency_action  = $_POST['emergency_action'] ?? '';
$reason            = trim($_POST['reason'] ?? '');

if ($tournament_id <= 0 || $emergency_action === '') {
    die('Invalid data');
}


$stmt = $conn->prepare("
    SELECT tournament_name, is_suspended, join_locked 
    FROM tournaments 
    WHERE tournament_id = ?
");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();

if (!$tournament) {
    die('Tournament not found');
}


$updates = match($emergency_action) {
    'suspend'     => ['is_suspended' => 1],
    'resume'      => ['is_suspended' => 0],
    'lock_join'   => ['join_locked' => 1],
    'unlock_join' => ['join_locked' => 0],
    default       => null
};

if (!$updates) {
    die('Invalid action');
}

if (in_array($emergency_action, ['suspend']) && $reason === '') {
    die('Reason required');
}


$conn->begin_transaction();

try {
    foreach ($updates as $column => $value) {
        $up = $conn->prepare("
            UPDATE tournaments 
            SET $column = ? 
            WHERE tournament_id = ?
        ");
        $up->bind_param("ii", $value, $tournament_id);
        $up->execute();
    }

   
    $log = $conn->prepare("
        INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address)
        VALUES (?, 'EMERGENCY_ACTION', 'tournament', ?, ?, ?)
    ");

    $desc = strtoupper($emergency_action) . " applied";
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

    header("Location: view_tournament.php?id=$tournament_id&success=emergency_updated");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die('Emergency action failed');
}
