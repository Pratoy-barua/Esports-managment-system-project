<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getConnection();

if (isset($_GET['id'])) {
    $tournament_id = (int)$_GET['id'];
    
    $sql = "SELECT t.*, u.full_name as organizer_name, u.username as organizer_username 
            FROM tournaments t 
            INNER JOIN users u ON t.organizer_id = u.user_id 
            WHERE t.tournament_id = $tournament_id";
    
    $tournament = getSingleRow($conn, $sql);
    
    if ($tournament) {
        echo json_encode(['success' => true, 'tournament' => $tournament]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tournament not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

closeConnection($conn);
?>
