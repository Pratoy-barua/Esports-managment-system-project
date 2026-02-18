<?php
/**
 * Get Universities List
 */
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getConnection();

$sql = "SELECT university_id, university_name, short_name FROM universities ORDER BY university_name ASC";
$universities = getAllRows($conn, $sql);

echo json_encode($universities);

closeConnection($conn);
?>
