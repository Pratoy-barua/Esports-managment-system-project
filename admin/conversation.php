<?php
/**
 * Admin - Individual Chat Conversation
 * Path: /admin/conversation.php
 */
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();
$conn = getConnection();

$admin_id = $_SESSION['user_id'];
$isIframe = isset($_GET['embed']) && $_GET['embed'] == 1;

/* ==========================================
   START CHAT WITH SPECIFIC USER
========================================== */
if (isset($_GET['start_user'])) {
    $user_id = intval($_GET['start_user']);

    $stmt = $conn->prepare(
        "SELECT conversation_id FROM chat_conversations WHERE user_id=? AND admin_id=?"
    );
    $stmt->bind_param("ii", $user_id, $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $ins = $conn->prepare(
            "INSERT INTO chat_conversations (user_id, admin_id, last_message_at)
             VALUES (?, ?, NOW())"
        );
        $ins->bind_param("ii", $user_id, $admin_id);
        $ins->execute();
        $conversation_id = $ins->insert_id;
    } else {
        $conversation_id = $res->fetch_assoc()['conversation_id'];
    }

    header("Location: conversation.php?conversation_id=".$conversation_id.($isIframe ? '&embed=1' : ''));
    exit;
}

$conversation_id = intval($_GET['conversation_id'] ?? 0);
if (!$conversation_id) die('Invalid conversation');

/* LOG */
$log = $conn->prepare(
    "INSERT INTO admin_logs (admin_id, action, target_type, target_id)
     VALUES (?, 'Opened conversation', 'Chat', ?)"
);
$log->bind_param("ii", $admin_id, $conversation_id);
$log->execute();

/* GET USER INFO */
$stmt = $conn->prepare(
    "SELECT u.user_id, u.full_name, u.username
     FROM chat_conversations c
     JOIN users u ON u.user_id = c.user_id
     WHERE c.conversation_id=? AND c.admin_id=?"
);
$stmt->bind_param("ii", $conversation_id, $admin_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die('Access denied');

/* SEND REPLY */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);

    $ins = $conn->prepare(
        "INSERT INTO chat_messages (conversation_id, sender_id, sender_role, message_text)
         VALUES (?, ?, 'admin', ?)"
    );
    $ins->bind_param("iis", $conversation_id, $admin_id, $msg);
    $ins->execute();

    $upd = $conn->prepare(
        "UPDATE chat_conversations SET last_message_at = NOW() WHERE conversation_id = ?"
    );
    $upd->bind_param("i", $conversation_id);
    $upd->execute();

    header("Location: conversation.php?conversation_id=".$conversation_id.($isIframe ? '&embed=1' : ''));
    exit;
}

/* FETCH MESSAGES */
$stmt = $conn->prepare(
    "SELECT sender_role, message_text, sent_at
     FROM chat_messages
     WHERE conversation_id=?
     ORDER BY sent_at ASC"
);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* MARK USER MESSAGES AS READ */
$read = $conn->prepare(
    "UPDATE chat_messages SET is_read=1
     WHERE conversation_id=? AND sender_role='user'"
);
$read->bind_param("i", $conversation_id);
$read->execute();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Chat</title>
<style>
body{margin:0;font-family:'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0}
.chat-header{padding:15px;background:#1e293b;border-bottom:1px solid #334155;display:flex;justify-content:space-between}
.chat-box{height:calc(100vh - 130px);padding:15px;overflow-y:auto}
.msg{padding:10px 14px;border-radius:12px;margin-bottom:12px;max-width:70%}
.admin{margin-left:auto;background:#6366f1;color:#fff}
.user{margin-right:auto;background:#1e293b;border:1px solid #334155}
.time{font-size:10px;opacity:.7;text-align:right}
.chat-footer{padding:15px;background:#1e293b;border-top:1px solid #334155}
form{display:flex;gap:10px}
input{flex:1;padding:10px;border-radius:8px;border:1px solid #334155;background:#0f172a;color:#fff}
button{background:#6366f1;color:#fff;border:none;padding:0 18px;border-radius:8px}
</style>
</head>
<body>

<div class="chat-header">
    <div>
        <strong><?=htmlspecialchars($user['full_name'])?></strong><br>
        <small>@<?=$user['username']?></small>
    </div>
    <span style="color:#10b981">● Online</span>
</div>

<div class="chat-box" id="chatBox">
    <?php foreach($messages as $m): ?>
        <div class="msg <?=$m['sender_role']?>">
            <?=htmlspecialchars($m['message_text'])?>
            <div class="time"><?=date('g:i A',strtotime($m['sent_at']))?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="chat-footer">
    <form method="POST">
        <input type="text" name="message" required placeholder="Write a message...">
        <button>Send</button>
    </form>
</div>

<script>
const chatBox=document.getElementById('chatBox');
chatBox.scrollTop=chatBox.scrollHeight;
</script>

</body>
</html>
<?php closeConnection($conn); ?>
