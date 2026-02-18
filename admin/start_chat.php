<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();
$conn = getConnection();

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    header("Location: messages.php");
    exit;
}

// find user
$stmt = $conn->prepare(
    "SELECT user_id
     FROM users
     WHERE role='user'
       AND (full_name LIKE ? OR username LIKE ?)
     LIMIT 1"
);
$like = "%$q%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: messages.php");
    exit;
}

$user_id  = $user['user_id'];
$admin_id = $_SESSION['user_id'];

// get or create conversation
$stmt = $conn->prepare(
    "SELECT conversation_id
     FROM chat_conversations
     WHERE user_id=? AND admin_id=?"
);
$stmt->bind_param("ii", $user_id, $admin_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $ins = $conn->prepare(
        "INSERT INTO chat_conversations (user_id, admin_id)
         VALUES (?,?)"
    );
    $ins->bind_param("ii", $user_id, $admin_id);
    $ins->execute();
    $conversation_id = $ins->insert_id;
} else {
    $conversation_id = $res->fetch_assoc()['conversation_id'];
}

// redirect back to split UI with conversation open
header("Location: messages.php?open=".$conversation_id);
exit;
