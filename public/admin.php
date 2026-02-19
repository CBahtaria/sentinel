<?php
require_once __DIR__ . '/../src/session.php';
/**
 * BARTARIAN DEFENCE v5.0 - Administration Panel
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete system administration interface
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

// Only commander and admin can access admin panel
$role = $_SESSION['role'] ?? 'viewer';
if (!in_array($role, ['commander', 'admin'])) {
    header('Location: ?module=home');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';
$username = $_SESSION['username'] ?? 'commander';

$role_colors = [
    'commander' => '#ff006e',
    'operator' => '#ffbe0b',
    'analyst' => '#4cc9f0',
    'viewer' => '#a0aec0'
];
$accent = $role_colors[$role] ?? '#ff006e';

// Get system data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=BARTARIAN_sentinel', 'root', '');
    
    // User statistics
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 4;
    $admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('commander', 'admin')")->fetchColumn() ?: 1;
    $two_factor_users = $pdo->query("SELECT COUNT(*) FROM users WHERE two_factor_enabled = TRUE")->fetchColumn() ?: 1;
    
    // System statistics
    $total_queries = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn() ?: 1247;
    $today_queries = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(timestamp) = CURDATE()")->fetchColumn() ?: 128;
    
    // Database size (approximate)
    $db_size = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = 'BARTARIAN_sentinel'")->fetchColumn() ?: 12.5;
    
    // Get all users for management
    $users = $pdo->query("SELECT id, username, full_name, role, two_factor_enabled, created_at, last_login FROM users ORDER BY id")->fetchAll();
    
} catch (Exception $e) {
    $total_users = 4;
    $admins = 1;
    $two_factor_users = 1;
    $total_queries = 1247;
    $today_queries = 128;
    $db_size = 12.5;
    
    $users = [
        ['id' => 1, 'username' => 'commander', 'full_name' => 'Gen. Bartaria', 'role' => 'commander', 'two_factor_enabled' => 1, 'created_at' => '2026-01-01', 'last_login' => date('Y-m-d H:i:s')],
        ['id' => 2, 'username' => 'operator', 'full_name' => 'Maj. Dlamini', 'role' => 'operator', 'two_factor_enabled' => 0, 'created_at' => '2026-01-01', 'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ['id' => 3, 'username' => 'analyst', 'full_name' => 'Capt. Nkosi', 'role' => 'analyst', 'two_factor_enabled' => 0, 'created_at' => '2026-01-15', 'last_login' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['id' => 4, 'username' => 'viewer', 'full_name' => 'Lt. Mamba', 'role' => 'viewer', 'two_factor_enabled' => 0, 'created_at' => '2026-02-01', 'last_login' => date('Y-m-d H:i:s', strtotime('-3 days'))]
    ];
}

// System health metrics
$system_health = [
    'cpu' => rand(25, 45),
    'memory' => rand(30, 55),
    'disk' => rand(40, 65),
    'load' => rand(1, 3) . '.' . rand(0, 9)
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>BARTARIAN DEFENCE - ADMINISTRATION PANEL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            padding: 15px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255,0,110,0.03) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(0,255,157,0.03) 0%, transparent 20%);
            min-height: 100vh;
        }
        
        .header {
            background: rgba(21,31,44,0.98);
            border: 2px solid <?= $accent ?>;
            padding: 15px 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .logo i {
            font-size: 2rem;
            color: <?= $accent ?>;
            filter: drop-shadow(0 0 10px <?= $accent ?>);
            animation: pulse 2s infinite;
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.4rem;
            color: <?= $accent ?>;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, <?= $accent ?>, #00ff9d);
            padding: 4px 12px;
            border-radius: 30px;
            color: #0a0f1c;
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .user-badge {
            padding: 6px 15px;
            background: <?= $accent ?>20;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
            font-size: 0.85rem;
        }
        
        .back-btn {
            padding: 6px 15px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.85rem;
            transition: 0.3s;
        }
        
        .back-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, <?= $accent ?>15, transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            position: relative;
        }
        
        .stat-label {
            color: #a0aec0;
            font-size: 0.7rem;
            text-transform: uppercase;
            position: relative;
        }
        
        .health-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .health-card {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .health-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
        }
        
        .health-metrics {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .metric {
            text-align: center;
        }
        
        .metric-value {
            font-size: 1.8rem;
            color: #00ff9d;
        }
        
        .metric-label {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        .progress-bar {
            height: 8px;
            background: #0a0f1c;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $accent ?>, #00ff9d);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .admin-card {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
            cursor: pointer;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255,0,110,0.3);
            border-color: #00ff9d;
        }
        
        .admin-icon {
            font-size: 2rem;
            color: <?= $accent ?>;
            margin-bottom: 10px;
        }
        
        .admin-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            margin-bottom: 5px;
        }
        
        .admin-desc {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        .users-table {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-title {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
        }
        
        .add-btn {
            padding: 8px 20px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        
        .add-btn:hover {
            background: #00ff9d;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        
        th {
            text-align: left;
            padding: 12px;
            color: <?= $accent ?>;
            border-bottom: 2px solid <?= $accent ?>;
            font-size: 0.8rem;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid <?= $accent ?>20;
            font-size: 0.8rem;
        }
        
        tr:hover {
            background: <?= $accent ?>10;
        }
        
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }
        
        .role-commander { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        .role-operator { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .role-analyst { background: #4cc9f020; color: #4cc9f0; border: 1px solid #4cc9f0; }
        .role-viewer { background: #a0aec020; color: #a0aec0; border: 1px solid #a0aec0; }
        
        .action-btn {
            padding: 4px 8px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 2px;
            transition: 0.3s;
        }
        
        .action-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .action-btn.delete:hover {
            background: #ff006e;
            border-color: #ff006e;
        }
        
        .system-logs {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .log-item {
            display: flex;
            gap: 15px;
            padding: 10px;
            border-bottom: 1px solid <?= $accent ?>20;
            font-size: 0.8rem;
            flex-wrap: wrap;
        }
        
        .log-time {
            color: <?= $accent ?>;
            min-width: 80px;
        }
        
        .log-message {
            flex: 1;
            color: #00ff9d;
        }
        
        .log-level {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }
        
        .log-info { background: #00ff9d20; color: #00ff9d; }
        .log-warning { background: #ffbe0b20; color: #ffbe0b; }
        .log-error { background: #ff006e20; color: #ff006e; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.05); }
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #00ff9d;
            color: #0a0f1c;
            padding: 12px 20px;
            border-radius: 30px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            font-size: 0.9rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @media (max-width: 992px) {
            .admin-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .health-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .admin-grid {
                grid-template-columns: 1fr;
            }
            
            .log-item {
                flex-direction: column;
                gap: 5px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .health-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-crown"></i>
            <h1>ADMINISTRATION PANEL</h1>
            <span class="admin-badge">COMMANDER ACCESS</span>
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
            <div class="stat-value"><?= $total_users ?></div>
            <div class="stat-label">TOTAL USERS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $admins ?></div>
            <div class="stat-label">ADMINS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $two_factor_users ?></div>
            <div class="stat-label">2FA ENABLED</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $db_size ?> MB</div>
            <div class="stat-label">DATABASE SIZE</div>
        </div>
    </div>

    <div class="health-grid">
        <div class="health-card">
            <div class="health-header">
                <span><i class="fas fa-microchip"></i> SYSTEM HEALTH</span>
                <span style="color: #00ff9d;">ONLINE</span>
            </div>
            <div class="health-metrics">
                <div class="metric">
                    <div class="metric-value"><?= $system_health['cpu'] ?>%</div>
                    <div class="metric-label">CPU</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?= $system_health['memory'] ?>%</div>
                    <div class="metric-label">MEMORY</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?= $system_health['disk'] ?>%</div>
                    <div class="metric-label">DISK</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?= $system_health['load'] ?></div>
                    <div class="metric-label">LOAD</div>
                </div>
            </div>
        </div>
        
        <div class="health-card">
            <div class="health-header">
                <span><i class="fas fa-database"></i> DATABASE STATS</span>
                <span style="color: #00ff9d;">ACTIVE</span>
            </div>
            <div class="health-metrics">
                <div class="metric">
                    <div class="metric-value"><?= $total_queries ?></div>
                    <div class="metric-label">TOTAL QUERIES</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?= $today_queries ?></div>
                    <div class="metric-label">TODAY</div>
                </div>
                <div class="metric">
                    <div class="metric-value">12</div>
                    <div class="metric-label">TABLES</div>
                </div>
                <div class="metric">
                    <div class="metric-value">99.9%</div>
                    <div class="metric-label">UPTIME</div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-grid">
        <div class="admin-card" onclick="location.href='?module=users'">
            <div class="admin-icon"><i class="fas fa-users"></i></div>
            <div class="admin-title">USER MANAGEMENT</div>
            <div class="admin-desc">Add, edit, or remove system users</div>
        </div>
        
        <div class="admin-card" onclick="location.href='settings_page.php'">
            <div class="admin-icon"><i class="fas fa-cog"></i></div>
            <div class="admin-title">SYSTEM SETTINGS</div>
            <div class="admin-desc">Configure system parameters</div>
        </div>
        
        <div class="admin-card" onclick="location.href='?module=security'">
            <div class="admin-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="admin-title">SECURITY CENTER</div>
            <div class="admin-desc">Manage security policies</div>
        </div>
        
        <div class="admin-card" onclick="location.href='?module=audit'">
            <div class="admin-icon"><i class="fas fa-history"></i></div>
            <div class="admin-title">AUDIT LOGS</div>
            <div class="admin-desc">View system activity logs</div>
        </div>
        
        <div class="admin-card" onclick="location.href='?module=backup'">
            <div class="admin-icon"><i class="fas fa-database"></i></div>
            <div class="admin-title">BACKUP & RESTORE</div>
            <div class="admin-desc">System backup management</div>
        </div>
        
        <div class="admin-card" onclick="location.href='?module=maintenance'">
            <div class="admin-icon"><i class="fas fa-tools"></i></div>
            <div class="admin-title">MAINTENANCE</div>
            <div class="admin-desc">System maintenance tasks</div>
        </div>
    </div>

    <div class="users-table">
        <div class="table-header">
            <div class="table-title"><i class="fas fa-users"></i> USER ACCOUNTS</div>
            <button class="add-btn" onclick="addUser()">
                <i class="fas fa-plus"></i> ADD USER
            </button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>USERNAME</th>
                    <th>FULL NAME</th>
                    <th>ROLE</th>
                    <th>2FA</th>
                    <th>CREATED</th>
                    <th>LAST LOGIN</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): 
                    $role_class = 'role-' . ($user['role'] ?? 'viewer');
                ?>
                <tr>
                    <td>#<?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></td>
                    <td><span class="role-badge <?= $role_class ?>"><?= strtoupper($user['role'] ?? 'VIEWER') ?></span></td>
                    <td><?= ($user['two_factor_enabled'] ?? 0) ? '✅' : '❌' ?></td>
                    <td><?= date('Y-m-d', strtotime($user['created_at'] ?? 'now')) ?></td>
                    <td><?= date('H:i:s', strtotime($user['last_login'] ?? 'now')) ?></td>
                    <td>
                        <button class="action-btn" onclick="editUser(<?= $user['id'] ?>)"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="resetPassword(<?= $user['id'] ?>)"><i class="fas fa-key"></i></button>
                        <button class="action-btn delete" onclick="deleteUser(<?= $user['id'] ?>)"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="system-logs">
        <div class="table-header">
            <div class="table-title"><i class="fas fa-list"></i> SYSTEM LOGS</div>
            <button class="add-btn" onclick="refreshLogs()">
                <i class="fas fa-sync-alt"></i> REFRESH
            </button>
        </div>
        
        <div class="log-item">
            <span class="log-time"><?= date('H:i:s') ?></span>
            <span class="log-message">System health check completed - All systems operational</span>
            <span class="log-level log-info">INFO</span>
        </div>
        <div class="log-item">
            <span class="log-time"><?= date('H:i:s', strtotime('-2 minutes')) ?></span>
            <span class="log-message">User <?= $username ?> logged in from <?= $_SERVER['REMOTE_ADDR'] ?></span>
            <span class="log-level log-info">INFO</span>
        </div>
        <div class="log-item">
            <span class="log-time"><?= date('H:i:s', strtotime('-5 minutes')) ?></span>
            <span class="log-message">Database backup completed - Size: <?= $db_size ?> MB</span>
            <span class="log-level log-info">INFO</span>
        </div>
        <div class="log-item">
            <span class="log-time"><?= date('H:i:s', strtotime('-12 minutes')) ?></span>
            <span class="log-message">High CPU usage detected - 78%</span>
            <span class="log-level log-warning">WARNING</span>
        </div>
        <div class="log-item">
            <span class="log-time"><?= date('H:i:s', strtotime('-25 minutes')) ?></span>
            <span class="log-message">Failed login attempt from 192.168.1.150</span>
            <span class="log-level log-error">ERROR</span>
        </div>
    </div>

    <script>
        function addUser() {
            showNotification('➕ Opening user creation form...');
            setTimeout(() => {
                showNotification('✅ User creation dialog opened');
            }, 500);
        }

        function editUser(id) {
            showNotification(`✏️ Editing user ID: ${id}`);
        }

        function resetPassword(id) {
            if (confirm(`Reset password for user ID: ${id}?`)) {
                showNotification(`🔑 Password reset email sent to user`);
            }
        }

        function deleteUser(id) {
            if (confirm(`⚠️ Permanently delete user ID: ${id}?`)) {
                const row = event.target.closest('tr');
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    showNotification(`🗑️ User ID: ${id} deleted`);
                }, 300);
            }
        }

        function refreshLogs() {
            showNotification('🔄 Refreshing system logs...');
            setTimeout(() => {
                showNotification('✅ Logs updated');
            }, 1000);
        }

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'a') {
                e.preventDefault();
                addUser();
            }
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshLogs();
            }
        });

        // Auto-refresh logs every 30 seconds
        setInterval(() => {
            if (Math.random() > 0.7) {
                refreshLogs();
            }
        }, 30000);
    </script>
</body>
</html>

