<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - WebSocket Test</title>
    <style>
        body {
            background: #0a0f1c;
            color: #00ff9d;
            font-family: 'Share Tech Mono', monospace;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #ff006e;
            border-bottom: 2px solid #ff006e;
            padding-bottom: 10px;
        }
        .status {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .connected { background: #00ff9d20; border: 1px solid #00ff9d; }
        .disconnected { background: #ff006e20; border: 1px solid #ff006e; }
        .logs {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 15px;
            height: 300px;
            overflow-y: auto;
            font-family: monospace;
        }
        .log-entry {
            border-bottom: 1px solid #ff006e40;
            padding: 5px;
        }
        .timestamp { color: #4cc9f0; }
        .drone-data, .threat-data {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 15px;
            margin: 10px 0;
        }
        button {
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            font-family: 'Share Tech Mono', monospace;
        }
        button:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 WebSocket Real-time Test</h1>
        
        <div id="connectionStatus" class="status disconnected">
            Status: DISCONNECTED
        </div>
        
        <div>
            <button onclick="connect()">Connect</button>
            <button onclick="disconnect()">Disconnect</button>
            <button onclick="subscribeDrones()">Subscribe Drones</button>
            <button onclick="subscribeThreats()">Subscribe Threats</button>
            <button onclick="sendPing()">Send Ping</button>
            <button onclick="clearLogs()">Clear Logs</button>
        </div>
        
        <div class="drone-data">
            <h3 style="color: #00ff9d;">🚁 Drone Updates</h3>
            <pre id="droneDisplay">No drone data yet...</pre>
        </div>
        
        <div class="threat-data">
            <h3 style="color: #ff006e;">⚠️ Threat Updates</h3>
            <pre id="threatDisplay">No threat data yet...</pre>
        </div>
        
        <h3 style="color: #4cc9f0;">📋 Event Log</h3>
        <div id="log" class="logs"></div>
    </div>

    <script src="js/websocket-client.js"></script>
    <script>
        let ws = null;
        
        function log(message, type = 'info') {
            const logDiv = document.getElementById('log');
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            
            const timestamp = new Date().toLocaleTimeString();
            let color = '#e0e0e0';
            
            if (type === 'error') color = '#ff006e';
            if (type === 'success') color = '#00ff9d';
            if (type === 'warning') color = '#ffbe0b';
            
            entry.innerHTML = `<span class="timestamp">[${timestamp}]</span> <span style="color: ${color};">${message}</span>`;
            logDiv.appendChild(entry);
            logDiv.scrollTop = logDiv.scrollHeight;
        }
        
        function connect() {
            if (ws) {
                ws.disconnect();
            }
            
            log('Connecting to WebSocket server...', 'warning');
            
            ws = new SentinelWebSocket({
                userId: 'commander',
                userRole: 'commander',
                token: 'sentinel-websocket-token'
            });
            
            ws.on('onOpen', () => {
                document.getElementById('connectionStatus').className = 'status connected';
                document.getElementById('connectionStatus').innerHTML = 'Status: CONNECTED ✅';
                log('Connected to server', 'success');
            });
            
            ws.on('onClose', () => {
                document.getElementById('connectionStatus').className = 'status disconnected';
                document.getElementById('connectionStatus').innerHTML = 'Status: DISCONNECTED ❌';
                log('Disconnected from server', 'error');
            });
            
            ws.on('onDroneUpdate', (data) => {
                document.getElementById('droneDisplay').innerHTML = 
                    JSON.stringify(data, null, 2);
                log('Drone data updated', 'success');
            });
            
            ws.on('onThreatUpdate', (data) => {
                document.getElementById('threatDisplay').innerHTML = 
                    JSON.stringify(data, null, 2);
                log('Threat data updated', 'warning');
            });
            
            ws.on('onNotification', (data) => {
                log(`🔔 ${data.title}: ${data.message}`, 'info');
            });
            
            ws.on('onStatsUpdate', (data) => {
                log(`📊 Stats: ${data.active_drones} active drones, ${data.active_threats} threats`, 'info');
            });
            
            ws.on('onMessage', (data) => {
                if (data.type === 'pong') {
                    // Ignore pong messages
                } else {
                    log(`Received: ${data.type}`, 'info');
                }
            });
        }
        
        function disconnect() {
            if (ws) {
                ws.disconnect();
                ws = null;
            }
        }
        
        function subscribeDrones() {
            if (ws) {
                ws.subscribe('drones');
                ws.requestData('drones');
                log('Subscribed to drone updates', 'success');
            }
        }
        
        function subscribeThreats() {
            if (ws) {
                ws.subscribe('threats');
                ws.requestData('threats');
                log('Subscribed to threat updates', 'success');
            }
        }
        
        function sendPing() {
            if (ws) {
                ws.ping();
                log('Ping sent', 'info');
            }
        }
        
        function clearLogs() {
            document.getElementById('log').innerHTML = '';
        }
        
        // Auto-connect on page load
        window.onload = connect;
    </script>
</body>
</html>
