<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: hosting.php');
    exit;
}

$conn = getConnection();


$request_id = (int)($_POST['request_id'] ?? 0);
if ($request_id <= 0) {
    die('Invalid request ID');
}

$conn->begin_transaction();

try {

   
    $stmt = $conn->prepare("
        SELECT *
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

   
    $eventStmt = $conn->prepare("
        INSERT INTO events
        (host_id, request_id, event_name, game_category, event_type, prize_pool, start_date, end_date, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    $eventStmt->bind_param(
        "iisssiss",
        $req['user_id'], $request_id, $req['event_name'], $req['game_category'], 
        $req['event_type'], $req['prize_pool'], $req['start_date'], $req['end_date']
    );
    $eventStmt->execute();
    $event_id = $conn->insert_id;

    
    $tourStmt = $conn->prepare("
        INSERT INTO tournaments
        (tournament_name, game_category, tournament_type, organizer_id, prize_pool, max_participants, registration_fee, rules, start_date, end_date, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Upcoming', NOW())
    ");
    
    $registration_fee = 0.00; // Default for approved requests
    
    $tourStmt->bind_param(
        "sssididsss",
        $req['event_name'],
        $req['game_category'],
        $req['event_type'],
        $req['user_id'],
        $req['prize_pool'],
        $req['expected_participants'],
        $registration_fee,
        $req['rules'],
        $req['start_date'],
        $req['end_date']
    );
    $tourStmt->execute();
    $tournament_id = $conn->insert_id;

  
    $up = $conn->prepare("
        UPDATE hosting_requests
        SET status = 'Approved',
            reviewed_by = ?,
            reviewed_at = NOW()
        WHERE request_id = ?
    ");
    $up->bind_param("ii", $_SESSION['user_id'], $request_id);
    $up->execute();

   
    $title = "Hosting Request Approved";
    $message = "Your hosting request '{$req['event_name']}' has been approved! Your tournament is now live.";

    $notif = $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, notification_type, created_at)
        VALUES (?, ?, ?, 'Tournament', NOW())
    ");
    $notif->bind_param("iss", $req['user_id'], $title, $message);
    $notif->execute();

    
    $desc = "Approved Hosting ID $request_id -> Tournament $tournament_id";
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $log = $conn->prepare("
        INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address, created_at)
        VALUES (?, 'APPROVE_HOSTING', 'tournament', ?, ?, ?, NOW())
    ");
    $log->bind_param("iiss", $_SESSION['user_id'], $tournament_id, $desc, $ip);
    $log->execute();

    $conn->commit();
    header("Location: hosting.php?success=approved");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Approval failed: " . $e->getMessage());
}