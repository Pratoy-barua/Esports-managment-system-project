<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();
$conn = getConnection();

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = $user_id";
$user = getSingleRow($conn, $sql);

// Notification count for sidebar badge
$sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = $user_id AND is_read = 0";
$notification_count = getSingleRow($conn, $sql)['unread'];

$sql = "SELECT COUNT(*) as unread FROM chat_messages m 
        JOIN chat_conversations c ON m.conversation_id = c.conversation_id 
        WHERE c.user_id = $user_id AND m.sender_role = 'admin' AND m.is_read = 0";
$message_count = getSingleRow($conn, $sql)['unread'];

$stmt = $conn->prepare("SELECT user_id FROM users WHERE role='admin' LIMIT 1");
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$admin_id = $admin['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        
        $stmt = $conn->prepare("SELECT conversation_id FROM chat_conversations WHERE user_id=? AND admin_id=?");
        $stmt->bind_param("ii", $user_id, $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $ins = $conn->prepare("INSERT INTO chat_conversations (user_id, admin_id) VALUES (?,?)");
            $ins->bind_param("ii", $user_id, $admin_id);
            $ins->execute();
            $conversation_id = $ins->insert_id;
        } else {
            $conversation_id = $res->fetch_assoc()['conversation_id'];
        }
        
        $insMsg = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_id, sender_role, message_text) VALUES (?,?, 'user', ?)");
        $insMsg->bind_param("iis", $conversation_id, $user_id, $message);
        $insMsg->execute();
        
        $up = $conn->prepare("UPDATE chat_conversations SET last_message_at=NOW() WHERE conversation_id=?");
        $up->bind_param("i", $conversation_id);
        $up->execute();

        header("Location: messages.php");
        exit;
    }
}

$stmt = $conn->prepare("SELECT conversation_id FROM chat_conversations WHERE user_id=? AND admin_id=?");
$stmt->bind_param("ii", $user_id, $admin_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$conversation_id = $row['conversation_id'] ?? 0;

$messages = [];
if ($conversation_id) {
    $stmt = $conn->prepare("SELECT sender_role, message_text, sent_at FROM chat_messages WHERE conversation_id=? ORDER BY sent_at ASC");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $upRead = $conn->prepare("UPDATE chat_messages SET is_read=1 WHERE conversation_id=? AND sender_role='admin'");
    $upRead->bind_param("i", $conversation_id);
    $upRead->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main-content { padding: 30px; margin-left: 260px; }
        .chat-container { max-width: 900px; background: #1e293b; border-radius: 15px; overflow: hidden; border: 1px solid #334155; display: flex; flex-direction: column; height: calc(100vh - 120px); }
        .chat-header { padding: 20px; background: #2d3748; border-bottom: 1px solid #334155; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
        .msg { padding: 12px 16px; border-radius: 12px; font-size: 14px; max-width: 75%; line-height: 1.5; }
        .msg.user { align-self: flex-end; background: #6366f1; color: #fff; border-bottom-right-radius: 2px; }
        .msg.admin { align-self: flex-start; background: #334155; color: #e2e8f0; border-bottom-left-radius: 2px; }
        .time { font-size: 10px; opacity: 0.6; margin-top: 5px; text-align: right; }
        .chat-footer { padding: 20px; background: #1e293b; border-top: 1px solid #334155; }
        .chat-form { display: flex; gap: 10px; }
        .chat-form input { flex: 1; background: #0f172a; border: 1px solid #334155; padding: 12px 15px; border-radius: 8px; color: #fff; outline: none; }
        .chat-form button { background: #6366f1; color: white; border: none; padding: 0 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .badge { background: #ef4444; color: white; padding: 2px 6px; border-radius: 50%; font-size: 10px; margin-left: 5px; }
    </style>
</head>
<body class="dashboard-body">

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-gamepad"></i>
                <span>ESports<strong>Hub</strong></span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="tournaments.php" class="nav-item">
                <i class="fas fa-trophy"></i>
                <span>Tournaments</span>
            </a>
            <a href="teams.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Teams</span>
            </a>
            <a href="tickets.php" class="nav-item">
                <i class="fas fa-ticket-alt"></i>
                <span>Tickets</span>
            </a>
            <a href="products.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Products</span>
            </a>
            <?php if ($user['profession'] === 'Student'): ?>
                <a href="subscription.php" class="nav-item">
                    <i class="fas fa-star"></i>
                    <span>Subscription</span>
                </a>
                <?php if (hasActiveSubscription()): ?>
                    <a href="events.php" class="nav-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Events Hub</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="notifications.php" class="nav-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($notification_count > 0): ?>
                    <span class="badge"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="messages.php" class="nav-item active">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
                <?php if ($message_count > 0): ?>
                    <span class="badge"><?php echo $message_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="../auth/logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="chat-container">
            <div class="chat-header">
                <h2 style="font-size: 18px;"><i class="fas fa-headset" style="margin-right: 10px; color: #6366f1;"></i> Chat with Support Admin</h2>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if(empty($messages)): ?>
                    <p style="text-align: center; color: #64748b; margin-top: 20px;">No messages yet. Feel free to ask anything!</p>
                <?php endif; ?>

                <?php foreach($messages as $m): ?>
                    <div class="msg <?= $m['sender_role'] ?>">
                        <?= htmlspecialchars($m['message_text']) ?>
                        <div class="time"><?= date('M d, H:i', strtotime($m['sent_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-footer">
                <form method="POST" class="chat-form">
                    <input type="text" name="message" placeholder="Type your message here..." required autocomplete="off">
                    <button type="submit">Send <i class="fas fa-paper-plane" style="margin-left: 5px;"></i></button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
</script>

</body>
</html>
<?php closeConnection($conn); ?>