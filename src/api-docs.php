<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - API Documentation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            padding: 20px;
        }
        .header {
            background: #151f2c;
            border: 2px solid #00ff9d;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
        }
        .api-key-box {
            background: #ff006e;
            padding: 10px 20px;
            border-radius: 4px;
            color: white;
            font-size: 0.9rem;
        }
        .api-section {
            background: #151f2c;
            border: 1px solid #ff006e;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        .api-title {
            background: #ff006e20;
            padding: 15px 20px;
            border-bottom: 1px solid #ff006e;
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
            font-size: 1.2rem;
        }
        .api-content {
            padding: 20px;
        }
        .endpoint {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            border-left: 3px solid #00ff9d;
        }
        .method {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            margin-right: 10px;
            font-size: 0.8rem;
        }
        .method.get { background: #4cc9f040; color: #4cc9f0; border: 1px solid #4cc9f0; }
        .method.post { background: #00ff9d40; color: #00ff9d; border: 1px solid #00ff9d; }
        .method.put { background: #ffbe0b40; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .method.delete { background: #ff006e40; color: #ff006e; border: 1px solid #ff006e; }
        .url {
            color: #00ff9d;
            font-weight: bold;
        }
        .description {
            color: #a0aec0;
            margin: 10px 0;
            padding-left: 20px;
        }
        pre {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            color: #00ff9d;
            border: 1px solid #ff006e40;
            margin: 10px 0;
        }
        .base-url {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 4px;
            color: #ff006e;
            margin-bottom: 20px;
            border: 1px solid #ff006e;
        }
        .test-btn {
            background: #00ff9d;
            color: #0a0f1c;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            margin: 10px 0;
        }
        .test-btn:hover {
            background: #ff006e;
            color: white;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-left: 10px;
        }
        .status-online { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .status-offline { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1><i class="fas fa-code"></i> UEDF SENTINEL API v1.0</h1>
            <p style="color: #a0aec0; margin-top: 5px;">Mobile Command Interface</p>
        </div>
        <div class="api-key-box">
            <i class="fas fa-key"></i> API Key: uedf-sentinel-mobile-2026
        </div>
    </div>

    <div class="base-url">
        <i class="fas fa-link"></i> Base URL: <strong>http://localhost:8080/sentinel/api/v1/</strong>
    </div>

    <div style="margin-bottom: 20px;">
        <button class="test-btn" onclick="testConnection()">
            <i class="fas fa-plug"></i> TEST API CONNECTION
        </button>
        <span id="connection-status" style="margin-left: 10px;"></span>
    </div>

    <div class="api-section">
        <div class="api-title">
            <i class="fas fa-lock"></i> AUTHENTICATION
        </div>
        <div class="api-content">
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">/auth.php?action=login</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Authenticate user and receive access token</div>
                <pre>{
    "username": "commander",
    "password": "commander123",
    "device_id": "mobile_device_123"
}</pre>
                <div><strong>Response:</strong> { "token": "...", "user": { "id": 1, "username": "commander", "role": "commander" } }</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">/auth.php?action=logout</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Logout and invalidate token</div>
                <pre>Headers: Authorization: Bearer {token}</pre>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/auth.php?action=verify</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Verify if token is still valid</div>
                <pre>Headers: Authorization: Bearer {token}</pre>
            </div>
        </div>
    </div>

    <div class="api-section">
        <div class="api-title">
            <i class="fas fa-drone"></i> DRONE FLEET
        </div>
        <div class="api-content">
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/drones.php?action=list</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Get all drones with fleet statistics</div>
                <pre>Headers: X-API-Key: uedf-sentinel-mobile-2026</pre>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/drones.php?action=get&id={drone_id}</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Get specific drone details</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">/drones.php</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Send command to drone (launch, land, return, hover, scan, emergency)</div>
                <pre>{
    "drone_id": 1,
    "command": "launch"
}</pre>
            </div>
        </div>
    </div>

    <div class="api-section">
        <div class="api-title">
            <i class="fas fa-exclamation-triangle"></i> THREAT MONITOR
        </div>
        <div class="api-content">
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/threats.php?status=ACTIVE&limit=50</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Get threats (status: ACTIVE, INVESTIGATING, RESOLVED, all)</div>
            </div>

            <div class="endpoint">
                <span class="method put">PUT</span>
                <span class="url">/threats.php</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Update threat status</div>
                <pre>{
    "threat_id": 1,
    "status": "RESOLVED"
}</pre>
            </div>
        </div>
    </div>

    <div class="api-section">
        <div class="api-title">
            <i class="fas fa-heartbeat"></i> SYSTEM
        </div>
        <div class="api-content">
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/system.php?type=status</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Get system status and statistics</div>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/system.php?type=notifications&user_id=1</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Get user notifications</div>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/system.php?type=audit&limit=50</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Get audit logs</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">/system.php?action=notification_read</span>
                <span class="status-badge status-online">ACTIVE</span>
                <div class="description">Mark notification as read</div>
                <pre>{
    "notification_id": 123
}</pre>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px; color: #4a5568;">
        <p>© 2026 UMBUTFO ESWATINI DEFENCE FORCE</p>
        <p>API Version 1.0 | Server Time: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <script>
        async function testConnection() {
            const statusEl = document.getElementById('connection-status');
            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
            
            try {
                const response = await fetch('http://localhost:8080/sentinel/api/v1/system.php?type=status', {
                    headers: {
                        'X-API-Key': 'uedf-sentinel-mobile-2026'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    statusEl.innerHTML = '<span style="color: #00ff9d;"><i class="fas fa-check-circle"></i> API ONLINE</span>';
                } else {
                    statusEl.innerHTML = '<span style="color: #ff006e;"><i class="fas fa-exclamation-triangle"></i> API ERROR</span>';
                }
            } catch (error) {
                statusEl.innerHTML = '<span style="color: #ff006e;"><i class="fas fa-times-circle"></i> CONNECTION FAILED</span>';
            }
        }
    </script>
</body>
</html>
