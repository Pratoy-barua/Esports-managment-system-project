<?php

session_start();
require_once '../config/database.php';
require_once '../config/session.php'; 

// Admin check
requireAdmin();
$conn = getConnection();

$admin_id = $_SESSION['user_id'];


$sql = "
    SELECT 
        c.conversation_id,
        u.user_id,
        u.full_name,
        u.username,
        c.last_message_at,
        (
            SELECT message_text 
            FROM chat_messages m 
            WHERE m.conversation_id = c.conversation_id 
            ORDER BY m.sent_at DESC 
            LIMIT 1
        ) AS last_message,
        (
            SELECT COUNT(*) 
            FROM chat_messages m 
            WHERE m.conversation_id = c.conversation_id 
              AND m.sender_role = 'user'
              AND m.is_read = 0
        ) AS unread_count
    FROM chat_conversations c
    JOIN users u ON u.user_id = c.user_id
    WHERE c.admin_id = ?
    ORDER BY c.last_message_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$admin_name = $_SESSION['full_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            overflow: hidden; 
        }
        
        .admin-layout { display: flex; height: 100vh; }
        
        /* HUBOHU DASHBOARD SIDEBAR */
        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo { 
            text-align: center; 
            padding: 20px 0; 
            border-bottom: 1px solid #334155; 
            margin-bottom: 20px; 
        }
        
        .admin-logo h2 { color: #818cf8; font-size: 22px; }
        
        .admin-nav a { 
            display: flex; 
            align-items: center; 
            padding: 12px 16px; 
            color: #cbd5e1; 
            text-decoration: none; 
            border-radius: 8px; 
            margin-bottom: 5px; 
            transition: all 0.3s; 
        }
        
        .admin-nav a:hover, .admin-nav a.active { 
            background: #334155; 
            color: #818cf8; 
        }
        
        .admin-nav a i { margin-right: 12px; width: 20px; }
        
        /* CONTENT AREA */
        .admin-content { 
            margin-left: 260px; 
            flex: 1; 
            padding: 30px; 
            display: flex; 
            flex-direction: column; 
            height: 100vh;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 { font-size: 28px; color: #f1f5f9; }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #818cf8;
        }

        /* SEARCH BOX */
        .search-container { margin-bottom: 20px; }
        .search-input {
            padding: 12px 15px; 
            width: 350px; 
            background: #1e293b;
            color: #fff; 
            border: 1px solid #334155; 
            border-radius: 8px; 
            outline: none;
            transition: 0.3s;
        }
        .search-input:focus { border-color: #818cf8; }

        /* SPLIT LAYOUT */
        .messenger-wrapper {
            display: flex;
            gap: 20px;
            flex: 1;
            height: calc(100vh - 200px);
        }

        /* LEFT LIST */
        .conversation-list {
            width: 35%;
            background: #1e293b;
            border-radius: 12px;
            overflow-y: auto;
            border: 1px solid #334155;
        }

        .conversation {
            padding: 15px;
            border-bottom: 1px solid #334155;
            cursor: pointer;
            transition: 0.2s;
            position: relative;
        }
        .conversation:hover, .conversation.active { background: #334155; }
        .conversation strong { color: #f1f5f9; font-size: 15px; }
        .conversation small { color: #94a3b8; display: block; margin-top: 4px; }

        .unread-badge {
            position: absolute; right: 15px; top: 15px;
            background: #ef4444; color: white; padding: 2px 7px;
            border-radius: 10px; font-size: 11px; font-weight: bold;
        }

        /* RIGHT CHAT WINDOW */
        .chat-window {
            width: 65%;
            background: #1e293b;
            border-radius: 12px;
            border: 1px solid #334155;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        iframe { width: 100%; height: 100%; border: none; }

        .no-chat-selected {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 100%; color: #64748b;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2><i class="fas fa-gamepad"></i> ESportsHub</h2>
            <p style="font-size: 12px; color: #64748b;">Admin Panel</p>
        </div>
        <nav class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-users"></i> User Management</a>
            <a href="tournaments.php"><i class="fas fa-trophy"></i> Tournaments</a>
            <a href="hosting.php"><i class="fas fa-calendar-check"></i> Hosting Requests</a>
            <a href="teams.php"><i class="fas fa-users-gear"></i> Teams</a>
            <a href="products.php"><i class="fas fa-box"></i> Products & Orders</a>
            <a href="subscriptions.php"><i class="fas fa-crown"></i> Subscriptions</a>
            <a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a>
            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            <a href="logs.php"><i class="fas fa-history"></i> Activity Logs</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Inbox</h1>
            <div class="admin-user">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div style="font-size: 13px; color: #64748b;">Administrator</div>
                </div>
                <img src="../assets/images/default-avatar.png" alt="Admin">
            </div>
        </div>

        <div class="search-container">
            <form method="GET" action="start_chat.php">
                <input type="text" name="q" class="search-input" placeholder="Search user by name / username...">
            </form>
        </div>

        <div class="messenger-wrapper">
            <div class="conversation-list">
                <?php if(empty($conversations)): ?>
                    <p style="padding:20px; color:#64748b; text-align:center;">No messages yet.</p>
                <?php endif; ?>

                <?php foreach($conversations as $conv): ?>
                    <div class="conversation" onclick="openChat(this, <?= $conv['conversation_id'] ?>)">
                        <strong><?= htmlspecialchars($conv['full_name']) ?></strong>
                        <small><?= htmlspecialchars(substr($conv['last_message'] ?? 'Start a conversation...', 0, 45)) ?>...</small>
                        <?php if($conv['unread_count'] > 0): ?>
                            <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-window" id="chatContainer">
                <div class="no-chat-selected" id="placeholder">
                    <i class="fas fa-comments" style="font-size: 50px; margin-bottom: 15px;"></i>
                    <p>Select a user to start chatting</p>
                </div>
                <iframe id="chatFrame" src="" style="display:none"></iframe>
            </div>
        </div>
    </main>
</div>

<script>
function openChat(element, conversationId) {
    if (!element) return;
    document.querySelectorAll('.conversation').forEach(el => el.classList.remove('active'));
    element.classList.add('active');

    document.getElementById('placeholder').style.display = 'none';
    const frame = document.getElementById('chatFrame');
    frame.style.display = 'block';
    
    // ✅ Updated iframe source with embed=1 parameter
    frame.src = 'conversation.php?conversation_id=' + conversationId + '&embed=1';
}

// Auto open chat if ID is in URL
<?php if(isset($_GET['open'])): ?>
window.addEventListener('load', () => {
    const targetId = <?= intval($_GET['open']) ?>;
    const targetElement = document.querySelector(`.conversation[onclick*="${targetId}"]`);
    if (targetElement) {
        openChat(targetElement, targetId);
    }
});
<?php endif; ?>
</script>

</body>
</html>