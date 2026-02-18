<?php


require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();
$conn = getConnection();


$message     = isset($_POST['message']) ? trim($_POST['message']) : '';
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$sender_id   = $_SESSION['user_id'];
$sender_role = $_SESSION['role'];


if (!in_array($sender_role, ['admin','user'])) {
    echo json_encode(['success'=>false,'error'=>'Invalid role']);
    exit;
}

if ($message === '' || $receiver_id === 0) {
    echo json_encode(['success'=>false,'error'=>'Message or receiver missing']);
    exit;
}


if ($sender_role === 'admin') {
    $admin_id = $sender_id;
    $user_id  = $receiver_id;
} else {
    $admin_id = $receiver_id;
    $user_id  = $sender_id;
}


$stmt = $conn->prepare(
    "SELECT conversation_id FROM chat_conversations WHERE user_id=? AND admin_id=?"
);
$stmt->bind_param("ii", $user_id, $admin_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $ins = $conn->prepare(
        "INSERT INTO chat_conversations (user_id, admin_id) VALUES (?,?)"
    );
    $ins->bind_param("ii", $user_id, $admin_id);
    $ins->execute();
    $conversation_id = $ins->insert_id;
} else {
    $conversation_id = $res->fetch_assoc()['conversation_id'];
}


$insMsg = $conn->prepare(
    "INSERT INTO chat_messages (conversation_id, sender_id, sender_role, message_text)
     VALUES (?,?,?,?)"
);
$insMsg->bind_param("iiss", $conversation_id, $sender_id, $sender_role, $message);

if (!$insMsg->execute()) {
    echo json_encode(['success'=>false,'error'=>'Message insert failed']);
    exit;
}


$up = $conn->prepare(
    "UPDATE chat_conversations SET last_message_at=NOW() WHERE conversation_id=?"
);
$up->bind_param("i", $conversation_id);
$up->execute();


if ($sender_role === 'user') {
    $text = "New message from user: ".$_SESSION['username'];
    $link = "/admin/messages.php";
    $target = $admin_id;
} else {
    $text = "New message from admin";
    $link = "/user/messages.php";
    $target = $user_id;
}

$notif = $conn->prepare(
    "INSERT INTO notifications (user_id, type, message, link)
     VALUES (?, 'message', ?, ?)"
);
$notif->bind_param("iss", $target, $text, $link);
$notif->execute();


if ($sender_role === 'admin') {
    $log = $conn->prepare(
        "INSERT INTO admin_logs (admin_id, action, reference_id)
         VALUES (?, 'Reply sent to user', ?)"
    );
    $log->bind_param("ii", $admin_id, $conversation_id);
    $log->execute();
}


echo json_encode(['success'=>true]);

closeConnection($conn);
?>
