<?php
require_once '../config/database.php';
require_once '../auth/session.php';

requireLogin();
$conn = getConnection();

$conversation_id = intval($_POST['conversation_id']);
$role = $_SESSION['role'];

$sender_role = ($role === 'admin') ? 'user' : 'admin';

$sql = "
    UPDATE chat_messages
    SET is_read = 1
    WHERE conversation_id = ?
      AND sender_role = ?
      AND is_read = 0
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $conversation_id, $sender_role);
$stmt->execute();

echo json_encode(['success' => true]);
