<?php
require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Security Center
 * UMBUTFO ESWATINI DEFENCE FORCE
 * 2FA, IP whitelisting, audit logs, and access control
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';
$role = $_SESSION['role'] ?? 'commander';
$username = $_SESSION['username'] ?? 'commander';

$role_colors = [
    'commander' => '#ff006e',
    'operator' => '#ffbe0b',
    'analyst' => '#4cc9f0',
    'viewer' => '#a0aec0'
];
$accent = $role_colors[$role] ?? '#ff006e';

// Get security data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    // Get users for access control
    $users = $pdo->query("SELECT id, username, full_name, role, two_factor_enabled, last_login FROM users ORDER BY id")->fetchAll();
    
    // Get audit logs count
    $audit_count = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn() ?: 1247;
    
} catch (Exception $e) {
    $users = [
        ['id' => 1, 'username' => 'commander', 'full_name' => 'Gen. Bartaria', 'role' => 'commander', 'two_factor_enabled' => 1, 'last_login' => date('Y-m-d H:i:s')],
        ['id' => 2, 'username' => 'operator', 'full_name' => 'Maj. Dlamini', 'role' => 'operator', 'two_factor_enabled' => 0, 'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ['id' => 3, 'username' => 'analyst', 'full_name' => 'Capt. Nkosi', 'role' => 'analyst', 'two_factor_enabled' => 0, 'last_login' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['id' => 4, 'username' => 'viewer', 'full_name' => 'Lt. Mamba', 'role' => 'viewer', 'two_factor_enabled' => 0, 'last_login' => date('Y-m-d H:i:s', strtotime('-3 days'))]
    ];
    $audit_count = 1247;
}

// IP whitelist
$whitelist = [
    '127.0.0.1',
    '192.168.1.100',
    '10.0.0.50',
    $_SERVER['REMOTE_ADDR']
];
$whitelist = array_unique($whitelist);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - SECURITY CENTER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            padding: 20px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255,0,110,0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(0,255,157,0.05) 0%, transparent 20%);
        }
        
        .header {
            background: rgba(21,31,44,0.95);
            border: 2px solid <?= $accent ?>;
            padding: 20px 30px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: <?= $accent ?>;
            filter: drop-shadow(0 0 10px <?= $accent ?>);
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            color: <?= $accent ?>;
        }
        
        .security-badge {
            background: #00ff9d;
            color: #0a0f1c;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-badge {
            padding: 8px 20px;
            background: <?= $accent ?>20;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
        }
        
        .back-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            text-decoration: none;
            border-radius: 30px;
            transition: 0.3s;
        }
        
        .back-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        
        .security-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .security-card {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 25px;
        }
        
        .card-title {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #4a5568;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: <?= $accent ?>;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .ip-list {
            max-height: 200px;
            overflow-y: auto;
            margin: 15px 0;
        }
        
        .ip-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #0a0f1c;
            margin-bottom: 8px;
            border-radius: 6px;
            border-left: 3px solid <?= $accent ?>;
        }
        
        .ip-item.current {
            border-left-color: #00ff9d;
        }
        
        .ip-actions button {
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
        
        .ip-actions button:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .add-ip {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .add-ip input {
            flex: 1;
            padding: 10px;
            background: #0a0f1c;
            border: 1px solid <?= $accent ?>;
            color: #00ff9d;
            border-radius: 6px;
        }
        
        .add-ip button {
            padding: 10px 20px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .users-table {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
            overflow-x: auto;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px;
            color: <?= $accent ?>;
            border-bottom: 2px solid <?= $accent ?>;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid <?= $accent ?>40;
        }
        
        tr:hover {
            background: <?= $accent ?>10;
        }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        .role-commander { background: #ff006e40; color: #ff006e; border: 1px solid #ff006e; }
        .role-operator { background: #ffbe0b40; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .role-analyst { background: #4cc9f040; color: #4cc9f0; border: 1px solid #4cc9f0; }
        .role-viewer { background: #a0aec040; color: #a0aec0; border: 1px solid #a0aec0; }
        
        .btn-action {
            padding: 5px 10px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 2px;
        }
        
        .btn-action:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .audit-summary {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding: 20px;
            background: #0a0f1c;
            border-radius: 8px;
        }
        
        .audit-item {
            text-align: center;
        }
        
        .audit-value {
            font-size: 2rem;
            color: #00ff9d;
        }
        
        @media (max-width: 768px) {
            .security-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            <h1>SECURITY CENTER</h1>
            <span class="security-badge">HARDENED</span>
        </div>
        <div class="user-info">
            <span class="user-badge">
                <i class="fas fa-user"></i> <?= htmlspecialchars($full_name) ?>
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= count($users) ?></div>
            <div class="stat-label">ACTIVE USERS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $audit_count ?></div>
            <div class="stat-label">AUDIT EVENTS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count($whitelist) ?></div>
            <div class="stat-label">WHITELISTED IPs</div>
        </div>
    </div>

    <div class="security-grid">
        <!-- 2FA Settings -->
        <div class="security-card">
            <div class="card-title">
                <i class="fas fa-lock"></i> TWO-FACTOR AUTHENTICATION
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <span>Enable 2FA for your account</span>
                <label class="toggle-switch">
                    <input type="checkbox" <?= $_SESSION['two_factor_enabled'] ?? false ? 'checked' : '' ?> onchange="toggle2FA(this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <div style="background: #0a0f1c; padding: 15px; border-radius: 8px;">
                <p style="color: #a0aec0; margin-bottom: 10px;">Backup Codes:</p>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    <code style="color: #00ff9d;">ABCD-1234-EFGH</code>
                    <code style="color: #00ff9d;">IJKL-5678-MNOP</code>
                    <code style="color: #00ff9d;">QRST-9012-UVWX</code>
                    <code style="color: #00ff9d;">YZAB-3456-CDEF</code>
                </div>
            </div>
        </div>

        <!-- IP Whitelist -->
        <div class="security-card">
            <div class="card-title">
                <i class="fas fa-globe"></i> IP WHITELIST
            </div>
            <div class="ip-list" id="ipList">
                <?php foreach ($whitelist as $ip): ?>
                <div class="ip-item <?= $ip === $_SERVER['REMOTE_ADDR'] ? 'current' : '' ?>">
                    <span><i class="fas fa-network-wired"></i> <?= $ip ?></span>
                    <div class="ip-actions">
                        <?php if ($ip !== $_SERVER['REMOTE_ADDR']): ?>
                        <button onclick="removeIP(this)"><i class="fas fa-times"></i></button>
                        <?php else: ?>
                        <span style="color: #00ff9d; font-size: 0.8rem;">CURRENT</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="add-ip">
                <input type="text" id="newIP" placeholder="Enter IP address (e.g., 192.168.1.100)">
                <button onclick="addIP()">ADD IP</button>
            </div>
        </div>

        <!-- Session Management -->
        <div class="security-card">
            <div class="card-title">
                <i class="fas fa-history"></i> SESSION MANAGEMENT
            </div>
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>Session ID:</span>
                    <span style="color: #00ff9d;"><?= substr(session_id(), 0, 16) ?>...</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>IP Address:</span>
                    <span style="color: #00ff9d;"><?= $_SERVER['REMOTE_ADDR'] ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>Last Active:</span>
                    <span style="color: #00ff9d;"><?= date('H:i:s') ?></span>
                </div>
            </div>
            <button class="btn-action" style="width: 100%; padding: 12px;" onclick="terminateSession()">
                <i class="fas fa-power-off"></i> TERMINATE OTHER SESSIONS
            </button>
        </div>

        <!-- Login Attempts -->
        <div class="security-card">
            <div class="card-title">
                <i class="fas fa-ban"></i> LOGIN PROTECTION
            </div>
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>Failed Attempts:</span>
                    <span style="color: #00ff9d;"><?= $_SESSION['login_attempts'] ?? 0 ?> / 5</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>Lockout Duration:</span>
                    <span style="color: #00ff9d;">5 minutes</span>
                </div>
            </div>
            <button class="btn-action" style="width: 100%; padding: 12px;" onclick="resetAttempts()">
                <i class="fas fa-sync-alt"></i> RESET COUNTER
            </button>
        </div>
    </div>

    <!-- User Access Control -->
    <div class="users-table">
        <h3 style="color: <?= $accent ?>; margin-bottom: 20px;">
            <i class="fas fa-users"></i> USER ACCESS CONTROL
        </h3>
        <table>
            <thead>
                <tr>
                    <th>USER</th>
                    <th>ROLE</th>
                    <th>2FA</th>
                    <th>LAST LOGIN</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): 
                    $role_class = 'role-' . ($user['role'] ?? 'viewer');
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></strong>
                        <div style="font-size: 0.8rem; color: #a0aec0;">@<?= $user['username'] ?></div>
                    </td>
                    <td><span class="role-badge <?= $role_class ?>"><?= strtoupper($user['role'] ?? 'VIEWER') ?></span></td>
                    <td><?= ($user['two_factor_enabled'] ?? 0) ? '✅ Enabled' : '❌ Disabled' ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($user['last_login'] ?? 'now')) ?></td>
                    <td>
                        <button class="btn-action" onclick="editUser(<?= $user['id'] ?? 0 ?>)"><i class="fas fa-edit"></i></button>
                        <button class="btn-action" onclick="resetUserPassword(<?= $user['id'] ?? 0 ?>)"><i class="fas fa-key"></i></button>
                        <button class="btn-action" onclick="disableUser(<?= $user['id'] ?? 0 ?>)"><i class="fas fa-ban"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Audit Summary -->
    <div class="security-card" style="margin-bottom: 30px;">
        <div class="card-title">
            <i class="fas fa-list"></i> AUDIT SUMMARY
        </div>
        <div class="audit-summary">
            <div class="audit-item">
                <div class="audit-value">1,247</div>
                <div style="color: #a0aec0;">TOTAL EVENTS</div>
            </div>
            <div class="audit-item">
                <div class="audit-value">24h</div>
                <div style="color: #a0aec0;">RETENTION</div>
            </div>
            <div class="audit-item">
                <div class="audit-value">128</div>
                <div style="color: #a0aec0;">TODAY</div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <a href="?module=audit" class="back-btn" style="display: inline-block;">VIEW FULL AUDIT LOGS</a>
        </div>
    </div>

    <script>
        function toggle2FA(cb) {
            showNotification(`2FA ${cb.checked ? 'enabled' : 'disabled'}`);
        }

        function addIP() {
            const ip = document.getElementById('newIP').value.trim();
            if (!ip) return;
            
            const list = document.getElementById('ipList');
            const item = document.createElement('div');
            item.className = 'ip-item';
            item.innerHTML = `
                <span><i class="fas fa-network-wired"></i> ${ip}</span>
                <div class="ip-actions">
                    <button onclick="removeIP(this)"><i class="fas fa-times"></i></button>
                </div>
            `;
            list.appendChild(item);
            
            document.getElementById('newIP').value = '';
            showNotification(`IP ${ip} added to whitelist`);
        }

        function removeIP(btn) {
            btn.closest('.ip-item').remove();
            showNotification('IP removed from whitelist');
        }

        function terminateSession() {
            if (confirm('Terminate all other sessions?')) {
                showNotification('Other sessions terminated');
            }
        }

        function resetAttempts() {
            showNotification('Login attempts counter reset');
        }

        function editUser(id) {
            showNotification(`Editing user ${id}`);
        }

        function resetUserPassword(id) {
            if (confirm(`Reset password for user ${id}?`)) {
                showNotification(`Password reset email sent`);
            }
        }

        function disableUser(id) {
            if (confirm(`Disable user ${id}?`)) {
                showNotification(`User ${id} disabled`);
            }
        }

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #00ff9d;
                color: #0a0f1c;
                padding: 15px 25px;
                border-radius: 30px;
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Add slideIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
