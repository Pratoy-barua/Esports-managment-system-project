let activeConversation = null;

function loadConversations() {
    fetch('/api/get_conversations.php')
        .then(res => res.json())
        .then(data => {
            console.log(data); // UI binding পরে করবে
        });
}

function loadMessages(conversationId) {
    activeConversation = conversationId;

    fetch('/api/get_messages.php?conversation_id=' + conversationId)
        .then(res => res.json())
        .then(data => {
            console.log(data); // message bubble later
            markAsRead(conversationId);
        });
}

function sendMessage(receiverId) {
    const msg = document.getElementById('messageInput').value;
    if (!msg.trim()) return;

    fetch('/api/send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `message=${encodeURIComponent(msg)}&receiver_id=${receiverId}`
    }).then(() => {
        document.getElementById('messageInput').value = '';
        loadMessages(activeConversation);
    });
}

function markAsRead(conversationId) {
    fetch('/api/mark_as_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `conversation_id=${conversationId}`
    });
}

// auto refresh
setInterval(() => {
    if (activeConversation) loadMessages(activeConversation);
    loadConversations();
}, 3000);
