<?php
/**
 * Get Platform Statistics
 */
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getConnection();

// Get statistics
$sql = "SELECT * FROM stats_overview LIMIT 1";
$result = getSingleRow($conn, $sql);

if ($result) {
    echo json_encode([
        'total_users' => (int)$result['total_users'],
        'active_tournaments' => (int)$result['total_tournaments'],
        'registered_teams' => 0, // Calculate from teams table
        'running_events' => (int)$result['running_events']
    ]);
} else {
    echo json_encode([
        'total_users' => 0,
        'active_tournaments' => 0,
        'registered_teams' => 0,
        'running_events' => 0
    ]);
}

closeConnection($conn);
?>
