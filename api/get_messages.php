<?php
require_once '../config/database.php';
require_once '../auth/session.php';

requireLogin();
$conn = getConnection();

$conversation_id = intval($_GET['conversation_id']);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$sql = "
    SELECT sender_id, sender_role, message_text, sent_at
    FROM chat_messages
    WHERE conversation_id = ?
    ORDER BY sent_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();

$result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
