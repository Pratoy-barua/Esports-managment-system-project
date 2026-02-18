<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

requireLogin();
$conn = getConnection();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ? AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode([
    'unread' => (int)$stmt->get_result()->fetch_assoc()['total']
]);
