<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Admin Panel
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

// Only commander can access admin panel
if ($_SESSION['role'] !== 'commander') {
    header('Location: ?module=home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - ADMIN PANEL</title>
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
            border: 2px solid #ff006e;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            text-decoration: none;
            border-radius: 4px;
        }
        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ff006e;
            padding-bottom: 10px;
        }
        .tab {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background: #ff006e;
            color: #0a0f1c;
        }
        .admin-content {
            background: #151f2c;
            border: 1px solid #ff006e;
            border-radius: 8px;
            padding: 30px;
            min-height: 500px;
        }
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table th {
            text-align: left;
            padding: 15px;
            background: #ff006e20;
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
        }
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #ff006e40;
        }
        .users-table tr:hover {
            background: #ff006e10;
        }
        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .role-commander { background: #ff006e40; color: #ff006e; }
        .role-operator { background: #ffbe0b40; color: #ffbe0b; }
        .role-analyst { background: #4cc9f040; color: #4cc9f0; }
        .role-viewer { background: #a0aec040; color: #a0aec0; }
        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            cursor: pointer;
            border-radius: 4px;
        }
        .action-btn.delete {
            border-color: #ff006e;
            color: #ff006e;
        }
        .system-card {
            background: #0a0f1c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .system-card h3 {
            color: #ff006e;
            margin-bottom: 15px;
        }
        .config-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ff006e40;
        }
        .config-label {
            color: #a0aec0;
        }
        .config-value {
            color: #00ff9d;
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
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-cog"></i> ADMINISTRATION PANEL</h1>
        <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
    </div>

    <div class="admin-tabs">
        <div class="tab active" onclick="showTab('users')">USERS</div>
        <div class="tab" onclick="showTab('system')">SYSTEM</div>
        <div class="tab" onclick="showTab('database')">DATABASE</div>
        <div class="tab" onclick="showTab('logs')">LOGS</div>
        <div class="tab" onclick="showTab('security')">SECURITY</div>
    </div>

    <div class="admin-content">
        <!-- Users Tab -->
        <div class="tab-pane active" id="users">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="color: #ff006e;">USER MANAGEMENT</h2>
                <button class="action-btn" onclick="addUser()"><i class="fas fa-plus"></i> ADD USER</button>
            </div>
            
            <table class="users-table">
                <tr>
                    <th>USERNAME</th>
                    <th>FULL NAME</th>
                    <th>ROLE</th>
                    <th>LAST LOGIN</th>
                    <th>ACTIONS</th>
                </tr>
                <tr>
                    <td>commander</td>
                    <td>Gen. Bartaria</td>
                    <td><span class="role-badge role-commander">COMMANDER</span></td>
                    <td>2026-02-17 10:23</td>
                    <td>
                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>operator</td>
                    <td>Sgt. Mamba</td>
                    <td><span class="role-badge role-operator">OPERATOR</span></td>
                    <td>2026-02-17 09:55</td>
                    <td>
                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>analyst</td>
                    <td>Capt. Nkosi</td>
                    <td><span class="role-badge role-analyst">ANALYST</span></td>
                    <td>2026-02-17 08:45</td>
                    <td>
                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>viewer</td>
                    <td>Lt. Dlamini</td>
                    <td><span class="role-badge role-viewer">VIEWER</span></td>
                    <td>2026-02-17 07:30</td>
                    <td>
                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            </table>
        </div>

        <!-- System Tab -->
        <div class="tab-pane" id="system">
            <h2 style="color: #ff006e; margin-bottom: 20px;">SYSTEM CONFIGURATION</h2>
            
            <div class="system-card">
                <h3><i class="fas fa-server"></i> SERVER STATUS</h3>
                <div class="config-row">
                    <span class="config-label">Uptime:</span>
                    <span class="config-value">15 days, 7 hours</span>
                </div>
                <div class="config-row">
                    <span class="config-label">CPU Usage:</span>
                    <span class="config-value">32%</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Memory Usage:</span>
                    <span class="config-value">4.2GB / 16GB</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Disk Usage:</span>
                    <span class="config-value">156GB / 500GB</span>
                </div>
            </div>

            <div class="system-card">
                <h3><i class="fas fa-sliders-h"></i> SYSTEM SETTINGS</h3>
                <div class="config-row">
                    <span class="config-label">Session Timeout:</span>
                    <span class="config-value">30 minutes</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Max Login Attempts:</span>
                    <span class="config-value">5</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Backup Frequency:</span>
                    <span class="config-value">Daily at 02:00</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Log Retention:</span>
                    <span class="config-value">90 days</span>
                </div>
            </div>

            <button class="action-btn" onclick="saveSystemConfig()"><i class="fas fa-save"></i> SAVE CHANGES</button>
        </div>

        <!-- Database Tab -->
        <div class="tab-pane" id="database">
            <h2 style="color: #ff006e; margin-bottom: 20px;">DATABASE MANAGEMENT</h2>
            
            <div class="system-card">
                <h3><i class="fas fa-database"></i> DATABASE STATISTICS</h3>
                <div class="config-row">
                    <span class="config-label">Database Size:</span>
                    <span class="config-value">2.3 GB</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Tables:</span>
                    <span class="config-value">15</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Total Records:</span>
                    <span class="config-value">45,678</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Last Backup:</span>
                    <span class="config-value">2026-02-17 02:00</span>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button class="action-btn" onclick="backupDB()"><i class="fas fa-download"></i> BACKUP NOW</button>
                <button class="action-btn" onclick="optimizeDB()"><i class="fas fa-tachometer-alt"></i> OPTIMIZE</button>
                <button class="action-btn delete" onclick="restoreDB()"><i class="fas fa-undo"></i> RESTORE</button>
            </div>
        </div>

        <!-- Logs Tab -->
        <div class="tab-pane" id="logs">
            <h2 style="color: #ff006e; margin-bottom: 20px;">SYSTEM LOGS</h2>
            
            <div style="margin-bottom: 20px;">
                <select style="padding: 10px; background: #0a0f1c; color: #00ff9d; border: 1px solid #ff006e;">
                    <option>All Logs</option>
                    <option>Error Logs</option>
                    <option>Access Logs</option>
                    <option>Security Logs</option>
                </select>
                <button class="action-btn" style="margin-left: 10px;"><i class="fas fa-search"></i> SEARCH</button>
            </div>

            <div style="background: #0a0f1c; padding: 20px; border-radius: 8px; font-family: monospace; height: 400px; overflow-y: auto;">
                <div style="color: #ff006e;">[2026-02-17 10:23:45] INFO: User login successful - commander</div>
                <div style="color: #ff8c00;">[2026-02-17 09:55:12] WARNING: Battery low - EAGLE-1</div>
                <div style="color: #ff006e;">[2026-02-17 09:30:01] ERROR: Threat detected - Critical severity</div>
                <div style="color: #00ff9d;">[2026-02-17 08:45:33] INFO: Configuration updated</div>
                <div style="color: #00ff9d;">[2026-02-17 08:12:19] INFO: Backup completed</div>
            </div>
        </div>

        <!-- Security Tab -->
        <div class="tab-pane" id="security">
            <h2 style="color: #ff006e; margin-bottom: 20px;">SECURITY SETTINGS</h2>
            
            <div class="system-card">
                <h3><i class="fas fa-shield-alt"></i> ACCESS CONTROL</h3>
                <div class="config-row">
                    <span class="config-label">Two-Factor Authentication:</span>
                    <span class="config-value">ENABLED</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Password Policy:</span>
                    <span class="config-value">STRONG</span>
                </div>
                <div class="config-row">
                    <span class="config-label">Session Encryption:</span>
                    <span class="config-value">AES-256</span>
                </div>
            </div>

            <div class="system-card">
                <h3><i class="fas fa-ban"></i> IP BLACKLIST</h3>
                <textarea style="width: 100%; height: 100px; background: #0a0f1c; border: 1px solid #ff006e; color: #00ff9d; padding: 10px;" placeholder="Enter IP addresses to block...">192.168.1.100
10.0.0.56
172.16.0.23</textarea>
            </div>

            <button class="action-btn" onclick="saveSecurity()"><i class="fas fa-save"></i> UPDATE SECURITY</button>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        function showTab(tabName) {
            // Update tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            // Update content
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
        }
        
        function addUser() {
            alert('Add user functionality would open a form');
        }
        
        function saveSystemConfig() {
            alert('System configuration saved');
        }
        
        function backupDB() {
            alert('Database backup started');
        }
        
        function optimizeDB() {
            alert('Database optimization complete');
        }
        
        function restoreDB() {
            if (confirm('Are you sure you want to restore database? This will overwrite current data.')) {
                alert('Database restore initiated');
            }
        }
        
        function saveSecurity() {
            alert('Security settings updated');
        }
    </script>

    <style>
        .ai-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(255,0,110,0.4);
            animation: pulse 2s infinite;
            z-index: -1;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.5); opacity: 0; }
        }
    </style>
</body>
</html>
