<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL - Team Chat System
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$role = $_SESSION['role'] ?? 'viewer';
$username = $_SESSION['username'] ?? 'operator';
$full_name = $_SESSION['full_name'] ?? 'Operator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - TEAM CHAT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .header {
            background: #151f2c;
            border: 2px solid #00ff9d;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
        }
        .back-btn {
            padding: 8px 15px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 4px;
        }
        .chat-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
            flex: 1;
            min-height: 0;
        }
        .sidebar {
            background: #151f2c;
            border: 1px solid #ff006e;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .channels-header {
            padding: 15px;
            background: #ff006e20;
            border-bottom: 1px solid #ff006e;
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
        }
        .channels-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        .channel-item {
            padding: 12px 15px;
            margin: 5px 0;
            background: #0a0f1c;
            border: 1px solid #00ff9d40;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }
        .channel-item:hover {
            border-color: #00ff9d;
            background: #00ff9d10;
        }
        .channel-item.active {
            border-color: #00ff9d;
            background: #00ff9d20;
            border-left: 4px solid #00ff9d;
        }
        .channel-name {
            color: #00ff9d;
            font-weight: bold;
        }
        .channel-desc {
            color: #a0aec0;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .chat-area {
            background: #151f2c;
            border: 1px solid #00ff9d;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            padding: 15px 20px;
            background: #00ff9d20;
            border-bottom: 1px solid #00ff9d;
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .online-users {
            color: #a0aec0;
            font-size: 0.9rem;
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message {
            display: flex;
            gap: 15px;
            animation: fadeIn 0.3s ease;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ff006e20;
            border: 2px solid #ff006e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #ff006e;
            flex-shrink: 0;
        }
        .message-content {
            flex: 1;
        }
        .message-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        .message-author {
            color: #00ff9d;
            font-weight: bold;
        }
        .message-role {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            background: #ff006e20;
            color: #ff006e;
            border: 1px solid #ff006e;
        }
        .message-time {
            color: #4a5568;
            font-size: 0.7rem;
        }
        .message-text {
            color: #e0e0e0;
            line-height: 1.5;
            word-wrap: break-word;
        }
        .message.commander .message-avatar {
            border-color: #ff006e;
            background: #ff006e30;
        }
        .message.operator .message-avatar {
            border-color: #ffbe0b;
            background: #ffbe0b30;
        }
        .message.analyst .message-avatar {
            border-color: #4cc9f0;
            background: #4cc9f030;
        }
        .input-area {
            padding: 20px;
            background: #0a0f1c;
            border-top: 1px solid #00ff9d;
            display: flex;
            gap: 10px;
        }
        .message-input {
            flex: 1;
            padding: 12px 15px;
            background: #151f2c;
            border: 1px solid #ff006e;
            color: #00ff9d;
            border-radius: 4px;
            font-size: 1rem;
        }
        .message-input:focus {
            outline: none;
            border-color: #00ff9d;
        }
        .send-btn {
            padding: 12px 30px;
            background: #ff006e;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
        }
        .send-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .typing-indicator {
            color: #a0aec0;
            font-size: 0.8rem;
            padding: 5px 20px;
            font-style: italic;
        }
        .float-ai {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ff006e, #00ff9d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-comments"></i> TEAM CHAT</h1>
        <div>
            <span style="color: #00ff9d; margin-right: 15px;">
                <i class="fas fa-user"></i> <?= htmlspecialchars($full_name) ?> (<?= strtoupper($role) ?>)
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="chat-container">
        <div class="sidebar">
            <div class="channels-header">
                <i class="fas fa-hashtag"></i> CHANNELS
            </div>
            <div class="channels-list" id="channelsList">
                <div class="channel-item active" data-channel="general">
                    <div class="channel-name"># general</div>
                    <div class="channel-desc">General command discussion</div>
                </div>
                <div class="channel-item" data-channel="operations">
                    <div class="channel-name"># operations</div>
                    <div class="channel-desc">Tactical operations</div>
                </div>
                <div class="channel-item" data-channel="intel">
                    <div class="channel-name"># intel</div>
                    <div class="channel-desc">Intelligence updates</div>
                </div>
                <div class="channel-item" data-channel="tech">
                    <div class="channel-name"># tech</div>
                    <div class="channel-desc">Technical support</div>
                </div>
            </div>
        </div>

        <div class="chat-area">
            <div class="chat-header">
                <span id="currentChannel"># general</span>
                <span class="online-users" id="onlineCount"><i class="fas fa-circle" style="color: #00ff9d;"></i> 4 online</span>
            </div>

            <div class="messages-container" id="messages"></div>

            <div class="typing-indicator" id="typingIndicator"></div>

            <div class="input-area">
                <input type="text" class="message-input" id="messageInput" placeholder="Type your message..." autocomplete="off">
                <button class="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i> SEND</button>
            </div>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        let currentChannel = 'general';
        let lastMessageId = 0;
        let typingTimeout;
        
        // Load messages
        function loadMessages() {
            fetch(`/sentinel/api/chat.php?action=messages&channel=${currentChannel}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.messages);
                    }
                });
        }
        
        // Display messages
        function displayMessages(messages) {
            const container = document.getElementById('messages');
            container.innerHTML = '';
            
            messages.forEach(msg => {
                const messageEl = document.createElement('div');
                messageEl.className = `message ${msg.user_role}`;
                messageEl.innerHTML = `
                    <div class="message-avatar">${msg.username.charAt(0).toUpperCase()}</div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-author">${msg.username}</span>
                            <span class="message-role">${msg.user_role.toUpperCase()}</span>
                            <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>
                        </div>
                        <div class="message-text">${escapeHtml(msg.message)}</div>
                    </div>
                `;
                container.appendChild(messageEl);
                lastMessageId = Math.max(lastMessageId, msg.id);
            });
            
            container.scrollTop = container.scrollHeight;
        }
        
        // Send message
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            fetch('/sentinel/api/chat.php?action=send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    channel: currentChannel
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    loadMessages();
                }
            });
        }
        
        // Channel switching
        document.querySelectorAll('.channel-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.channel-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                currentChannel = this.dataset.channel;
                document.getElementById('currentChannel').textContent = '# ' + currentChannel;
                loadMessages();
            });
        });
        
        // Enter key to send
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        // Typing indicator
        document.getElementById('messageInput').addEventListener('input', function() {
            if (window.wsManager && window.wsManager.ws) {
                window.wsManager.send('typing', {
                    channel: currentChannel,
                    username: '<?= $username ?>'
                });
            }
            
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                if (window.wsManager && window.wsManager.ws) {
                    window.wsManager.send('stop_typing', {
                        channel: currentChannel
                    });
                }
            }, 2000);
        });
        
        // WebSocket integration
        if (window.wsManager) {
            window.wsManager.on('new_message', (data) => {
                if (data.data.channel === currentChannel) {
                    displayMessages([data.data]);
                } else {
                    // Show notification for other channels
                    window.wsManager.showNotification(
                        `💬 New message in #${data.data.channel}`,
                        `${data.data.username}: ${data.data.message.substring(0, 50)}...`,
                        'info'
                    );
                }
            });
            
            window.wsManager.on('user_typing', (data) => {
                if (data.channel === currentChannel) {
                    document.getElementById('typingIndicator').textContent = 
                        `${data.username} is typing...`;
                }
            });
            
            window.wsManager.on('user_stop_typing', (data) => {
                if (data.channel === currentChannel) {
                    document.getElementById('typingIndicator').textContent = '';
                }
            });
        }
        
        // Escape HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Load messages on start
        loadMessages();
        setInterval(loadMessages, 5000); // Refresh every 5 seconds
    </script>
</body>
</html>
