<?php
require_once '../config/database.php';
require_once '../auth/session.php';

requireLogin();
$conn = getConnection();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $sql = "
        SELECT c.conversation_id, u.full_name, u.profile_image,
               c.last_message_at,
               (SELECT COUNT(*) FROM chat_messages m
                WHERE m.conversation_id = c.conversation_id
                AND m.sender_role = 'user'
                AND m.is_read = 0) AS unread_count
        FROM chat_conversations c
        JOIN users u ON u.user_id = c.user_id
        ORDER BY c.last_message_at DESC
    ";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "
        SELECT c.conversation_id, 'Admin' AS full_name, NULL AS profile_image,
               c.last_message_at,
               (SELECT COUNT(*) FROM chat_messages m
                WHERE m.conversation_id = c.conversation_id
                AND m.sender_role = 'admin'
                AND m.is_read = 0) AS unread_count
        FROM chat_conversations c
        WHERE c.user_id = ?
        ORDER BY c.last_message_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
