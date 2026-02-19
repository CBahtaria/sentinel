<?php
require_once 'includes/session.php';
/**
 * UEDF SENTINEL v5.0 - AI Command Assistant
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';
$role = $_SESSION['role'] ?? 'commander';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - AI ASSISTANT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            min-height: 100vh;
            padding: 20px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255,0,110,0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(0,255,157,0.05) 0%, transparent 20%);
        }
        
        .header {
            background: rgba(21,31,44,0.95);
            border: 2px solid #ff006e;
            padding: 20px 30px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(255,0,110,0.2);
            backdrop-filter: blur(10px);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: #ff006e;
            filter: drop-shadow(0 0 10px #ff006e);
            animation: pulse 2s infinite;
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            color: #ff006e;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-badge {
            padding: 8px 20px;
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 30px;
        }
        
        .back-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 30px;
            transition: 0.3s;
        }
        
        .back-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .ai-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .ai-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .ai-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff006e, #00ff9d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        .ai-avatar i {
            font-size: 3rem;
            color: #0a0f1c;
        }
        
        .ai-title h2 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
            font-size: 2rem;
        }
        
        .ai-title p {
            color: #00ff9d;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        
        .stat-label {
            color: #a0aec0;
            font-size: 0.8rem;
        }
        
        .chat-container {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #0a0f1c;
        }
        
        .message {
            margin-bottom: 20px;
            display: flex;
            animation: slideIn 0.3s ease;
        }
        
        .message.ai {
            justify-content: flex-start;
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message-content {
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 15px;
            position: relative;
        }
        
        .message.ai .message-content {
            background: #151f2c;
            border: 1px solid #ff006e;
            color: #00ff9d;
        }
        
        .message.user .message-content {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #4a5568;
            margin-top: 5px;
            text-align: right;
        }
        
        .chat-input-container {
            display: flex;
            padding: 20px;
            background: #151f2c;
            border-top: 1px solid #ff006e;
        }
        
        .chat-input {
            flex: 1;
            padding: 15px;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            color: #00ff9d;
            border-radius: 30px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 1rem;
        }
        
        .chat-input:focus {
            outline: none;
            border-color: #00ff9d;
        }
        
        .chat-send {
            width: 50px;
            height: 50px;
            margin-left: 10px;
            background: #ff006e;
            border: none;
            border-radius: 50%;
            color: #0a0f1c;
            font-size: 1.2rem;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .chat-send:hover {
            background: #00ff9d;
            transform: scale(1.1);
        }
        
        .suggestions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        
        .suggestion-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .suggestion-card:hover {
            transform: translateY(-5px);
            border-color: #00ff9d;
            box-shadow: 0 10px 20px rgba(255,0,110,0.3);
        }
        
        .suggestion-card i {
            font-size: 2rem;
            color: #ff006e;
            margin-bottom: 10px;
        }
        
        .suggestion-card span {
            color: #00ff9d;
            font-size: 0.9rem;
        }
        
        .typing-indicator {
            display: flex;
            gap: 5px;
            padding: 10px;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background: #ff006e;
            border-radius: 50%;
            animation: typing 1s infinite;
        }
        
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .voice-btn {
            background: #00ff9d;
        }
        
        .voice-btn:hover {
            background: #ff006e;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .suggestions {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .suggestions {
                grid-template-columns: 1fr;
            }
            
            .ai-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-robot"></i>
            <h1>AI COMMAND ASSISTANT</h1>
        </div>
        <div class="user-info">
            <span class="user-badge">
                <i class="fas fa-user"></i> <?= htmlspecialchars($full_name) ?>
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="ai-container">
        <div class="ai-header">
            <div class="ai-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="ai-title">
                <h2>SENTINEL AI</h2>
                <p>Advanced Tactical Assistant • Online</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">94%</div>
                <div class="stat-label">AI CONFIDENCE</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">1,247</div>
                <div class="stat-label">COMMANDS PROCESSED</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">99.9%</div>
                <div class="stat-label">UPTIME</div>
            </div>
        </div>

        <div class="chat-container">
            <div class="chat-messages" id="chatMessages">
                <div class="message ai">
                    <div class="message-content">
                        <i class="fas fa-robot" style="margin-right: 10px;"></i>
                        Good day, Commander. How may I assist you with tactical operations today?
                        <div class="message-time"><?= date('H:i:s') ?></div>
                    </div>
                </div>
            </div>
            <div class="chat-input-container">
                <input type="text" class="chat-input" id="userInput" placeholder="Enter command or question..." autofocus>
                <button class="chat-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                <button class="chat-send voice-btn" onclick="startVoiceRecognition()" style="margin-left: 10px;" title="Voice Command">
                    <i class="fas fa-microphone"></i>
                </button>
            </div>
        </div>

        <div class="suggestions">
            <div class="suggestion-card" onclick="quickCommand('Show me active threats')">
                <i class="fas fa-exclamation-triangle"></i>
                <span>ACTIVE THREATS</span>
            </div>
            <div class="suggestion-card" onclick="quickCommand('Deploy drone to sector 7')">
                <i class="fas fa-drone"></i>
                <span>DEPLOY DRONE</span>
            </div>
            <div class="suggestion-card" onclick="quickCommand('Generate threat report')">
                <i class="fas fa-chart-line"></i>
                <span>THREAT REPORT</span>
            </div>
            <div class="suggestion-card" onclick="quickCommand('System status')">
                <i class="fas fa-heartbeat"></i>
                <span>SYSTEM STATUS</span>
            </div>
            <div class="suggestion-card" onclick="quickCommand('Schedule drone patrol')">
                <i class="fas fa-clock"></i>
                <span>SCHEDULE PATROL</span>
            </div>
            <div class="suggestion-card" onclick="quickCommand('Analyze recent data')">
                <i class="fas fa-brain"></i>
                <span>DATA ANALYSIS</span>
            </div>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const userInput = document.getElementById('userInput');

        function addMessage(text, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            
            if (!isUser) {
                const icon = document.createElement('i');
                icon.className = 'fas fa-robot';
                icon.style.marginRight = '10px';
                contentDiv.appendChild(icon);
            }
            
            contentDiv.appendChild(document.createTextNode(text));
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            timeDiv.textContent = new Date().toLocaleTimeString();
            contentDiv.appendChild(timeDiv);
            
            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message ai';
            typingDiv.id = 'typingIndicator';
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator';
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('div');
                dot.className = 'typing-dot';
                typingIndicator.appendChild(dot);
            }
            
            contentDiv.appendChild(typingIndicator);
            typingDiv.appendChild(contentDiv);
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTyping() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }

        function getAIResponse(command) {
            const lower = command.toLowerCase();
            
            if (lower.includes('threat')) {
                return "Current threat assessment: 5 active threats detected. 2 are critical in sectors 4 and 9. Recommend immediate drone deployment to sector 4.";
            }
            else if (lower.includes('drone') && lower.includes('deploy')) {
                return "Drone deployment initiated. DRONE-003 is being dispatched to sector 7. ETA: 45 seconds. Camera feed will be available shortly.";
            }
            else if (lower.includes('report')) {
                return "Generating comprehensive threat report... Analysis shows 23% increase in unauthorized access attempts over the last 24 hours. Peak activity detected between 0200-0400 hours.";
            }
            else if (lower.includes('status') || lower.includes('health')) {
                return "All systems operational. CPU: 45%, Memory: 62%, Network: Optimal. 15 drones online, 8 currently active. Last threat scan: 2 minutes ago.";
            }
            else if (lower.includes('patrol') || lower.includes('schedule')) {
                return "Patrol schedule optimized. Drones will conduct perimeter sweeps every 30 minutes. Additional coverage added to sectors 3 and 8 based on recent threat patterns.";
            }
            else if (lower.includes('analyze') || lower.includes('data')) {
                return "Analyzing recent data streams... Detected unusual patterns in network traffic originating from sector 5. Recommend increased monitoring and possible drone reconnaissance.";
            }
            else if (lower.includes('weather')) {
                return "Current weather conditions: Partly cloudy, 23°C, wind 12 km/h. Optimal flying conditions for drone operations. No weather-related restrictions.";
            }
            else {
                return "Processing command: \"" + command + "\". AI analysis complete. No immediate action required. Would you like me to execute this command or provide more details?";
            }
        }

        function sendMessage() {
            const message = userInput.value.trim();
            if (!message) return;
            
            addMessage(message, true);
            userInput.value = '';
            
            showTyping();
            
            setTimeout(() => {
                hideTyping();
                const response = getAIResponse(message);
                addMessage(response);
            }, 1500);
        }

        function quickCommand(command) {
            userInput.value = command;
            sendMessage();
        }

        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Voice recognition
        function startVoiceRecognition() {
            if (!('webkitSpeechRecognition' in window)) {
                alert('Voice recognition is not supported in your browser. Please use Chrome, Edge, or Safari.');
                return;
            }
            
            const recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';
            
            recognition.onstart = function() {
                addMessage("Listening...", false);
            };
            
            recognition.onresult = function(event) {
                const command = event.results[0][0].transcript;
                userInput.value = command;
                sendMessage();
            };
            
            recognition.onerror = function(event) {
                addMessage("Sorry, I couldn't understand. Please try again.", false);
            };
            
            recognition.start();
        }

        // Quick commands for demo
        const demoCommands = [
            "Show me active threats",
            "Deploy drone to sector 7",
            "Generate threat report",
            "System status"
        ];
        
        let demoIndex = 0;
        setInterval(() => {
            if (document.hasFocus()) {
                // Uncomment for auto-demo
                // quickCommand(demoCommands[demoIndex % demoCommands.length]);
                // demoIndex++;
            }
        }, 30000);
    </script>
</body>
</html>
