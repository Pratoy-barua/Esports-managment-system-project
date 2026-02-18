<?php
/**
 * Get Departments List
 */
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getConnection();

$sql = "SELECT department_id, department_name, department_code FROM departments ORDER BY department_name ASC";
$departments = getAllRows($conn, $sql);

echo json_encode($departments);

closeConnection($conn);
?>
