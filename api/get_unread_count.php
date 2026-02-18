<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();
$conn = getConnection();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $sql = "
        SELECT COUNT(*) total
        FROM chat_messages m
        JOIN chat_conversations c ON c.conversation_id = m.conversation_id
        WHERE c.admin_id = ? AND m.sender_role = 'user' AND m.is_read = 0
    ";
} else {
    $sql = "
        SELECT COUNT(*) total
        FROM chat_messages m
        JOIN chat_conversations c ON c.conversation_id = m.conversation_id
        WHERE c.user_id = ? AND m.sender_role = 'admin' AND m.is_read = 0
    ";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];

echo json_encode(['unread' => (int)$total]);
