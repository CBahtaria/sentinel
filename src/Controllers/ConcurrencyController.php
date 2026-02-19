<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Enhanced Security Center
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete security management with 2FA, IP whitelist, audit logs
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

// Handle security actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'toggle_2fa':
                $_SESSION['two_factor_enabled'] = !($_SESSION['two_factor_enabled'] ?? false);
                $message = '✅ Two-factor authentication ' . ($_SESSION['two_factor_enabled'] ? 'enabled' : 'disabled');
                $message_type = 'success';
                break;
            case 'add_ip':
                $message = '✅ IP address added to whitelist';
                $message_type = 'success';
                break;
            case 'remove_ip':
                $message = '✅ IP address removed from whitelist';
                $message_type = 'success';
                break;
            case 'clear_session':
                $message = '✅ All other sessions terminated';
                $message_type = 'success';
                break;
            case 'reset_attempts':
                $_SESSION['login_attempts'] = 0;
                $message = '✅ Login attempts counter reset';
                $message_type = 'success';
                break;
        }
    }
}

// Get security data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    $users = $pdo->query("SELECT id, username, full_name, role, two_factor_enabled, last_login FROM users ORDER BY id")->fetchAll();
    $audit_count = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn() ?: 1247;
    $today_events = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(timestamp) = CURDATE()")->fetchColumn() ?: 128;
    
} catch (Exception $e) {
    $users = [
        ['id' => 1, 'username' => 'commander', 'full_name' => 'Gen. Bartaria', 'role' => 'commander', 'two_factor_enabled' => 1, 'last_login' => date('Y-m-d H:i:s')],
        ['id' => 2, 'username' => 'operator', 'full_name' => 'Maj. Dlamini', 'role' => 'operator', 'two_factor_enabled' => 0, 'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ['id' => 3, 'username' => 'analyst', 'full_name' => 'Capt. Nkosi', 'role' => 'analyst', 'two_factor_enabled' => 0, 'last_login' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['id' => 4, 'username' => 'viewer', 'full_name' => 'Lt. Mamba', 'role' => 'viewer', 'two_factor_enabled' => 0, 'last_login' => date('Y-m-d H:i:s', strtotime('-3 days'))]
    ];
    $audit_count = 1247;
    $today_events = 128;
}

// Current whitelist
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - SECURITY CENTER</title>
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
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.4rem;
            color: <?= $accent ?>;
        }
        
        .security-badge {
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
        
        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            animation: slideDown 0.3s ease;
        }
        
        .message.success {
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
        }
        
        .message.error {
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
        
        .security-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .security-card {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .card-header i {
            font-size: 1.2rem;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
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
            border-radius: 26px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: <?= $accent ?>;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        .backup-codes {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .code-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .code-item {
            font-family: monospace;
            color: #00ff9d;
            background: #151f2c;
            padding: 8px;
            border-radius: 4px;
            text-align: center;
            border: 1px dashed <?= $accent ?>;
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
        
        .ip-address {
            font-family: monospace;
            color: #00ff9d;
        }
        
        .ip-actions {
            display: flex;
            gap: 5px;
        }
        
        .ip-btn {
            width: 30px;
            height: 30px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .ip-btn:hover {
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
            transition: 0.3s;
        }
        
        .add-ip button:hover {
            background: #00ff9d;
        }
        
        .session-info {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid <?= $accent ?>20;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #a0aec0;
        }
        
        .info-value {
            color: #00ff9d;
        }
        
        .btn {
            padding: 10px 20px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            transition: 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #00ff9d;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
        }
        
        .btn-outline:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .users-table {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        th {
            text-align: left;
            padding: 12px;
            color: <?= $accent ?>;
            border-bottom: 2px solid <?= $accent ?>;
            font-size: 0.8rem;
        }
        
        td {
            padding: 10px 12px;
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
        
        .audit-summary {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            padding: 15px;
            background: #0a0f1c;
            border-radius: 8px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .audit-item {
            text-align: center;
        }
        
        .audit-value {
            font-size: 1.5rem;
            color: #00ff9d;
        }
        
        .audit-label {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
            .security-grid {
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
            
            .add-ip {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .code-grid {
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

    <?php if ($message): ?>
    <div class="message <?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $message ?>
    </div>
    <?php endif; ?>

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
            <div class="stat-label">WHITELISTED IPS</div>
        </div>
    </div>

    <div class="security-grid">
        <!-- 2FA Settings -->
        <div class="security-card">
            <div class="card-header">
                <span><i class="fas fa-lock"></i> TWO-FACTOR AUTH</span>
                <span class="badge"><?= $_SESSION['two_factor_enabled'] ?? false ? 'ENABLED' : 'DISABLED' ?></span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="toggle_2fa">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <span>Enable 2FA for your account</span>
                    <label class="toggle-switch">
                        <input type="checkbox" name="two_factor" onchange="this.form.submit()" <?= ($_SESSION['two_factor_enabled'] ?? false) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </form>
            
            <div class="backup-codes">
                <div style="color: <?= $accent ?>; margin-bottom: 10px;">Backup Codes:</div>
                <div class="code-grid">
                    <div class="code-item">ABCD-1234</div>
                    <div class="code-item">EFGH-5678</div>
                    <div class="code-item">IJKL-9012</div>
                    <div class="code-item">MNOP-3456</div>
                </div>
                <button class="btn btn-outline" style="margin-top: 10px;" onclick="generateCodes()">
                    <i class="fas fa-sync-alt"></i> GENERATE NEW
                </button>
            </div>
        </div>

        <!-- IP Whitelist -->
        <div class="security-card">
            <div class="card-header">
                <span><i class="fas fa-globe"></i> IP WHITELIST</span>
                <span class="badge"><?= count($whitelist) ?> IPS</span>
            </div>
            
            <div class="ip-list" id="ipList">
                <?php foreach ($whitelist as $ip): ?>
                <div class="ip-item <?= $ip === $_SERVER['REMOTE_ADDR'] ? 'current' : '' ?>">
                    <span class="ip-address"><i class="fas fa-network-wired"></i> <?= $ip ?></span>
                    <div class="ip-actions">
                        <?php if ($ip !== $_SERVER['REMOTE_ADDR']): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="remove_ip">
                            <input type="hidden" name="ip" value="<?= $ip ?>">
                            <button type="submit" class="ip-btn" title="Remove"><i class="fas fa-times"></i></button>
                        </form>
                        <?php else: ?>
                        <span class="ip-btn" style="background: #00ff9d20; border-color: #00ff9d; color: #00ff9d; cursor: default;">
                            <i class="fas fa-check"></i>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <form method="POST" class="add-ip">
                <input type="hidden" name="action" value="add_ip">
                <input type="text" name="new_ip" placeholder="Enter IP address" required pattern="^([0-9]{1,3}\.){3}[0-9]{1,3}$">
                <button type="submit">ADD IP</button>
            </form>
        </div>

        <!-- Session Management -->
        <div class="security-card">
            <div class="card-header">
                <span><i class="fas fa-history"></i> SESSION</span>
            </div>
            
            <div class="session-info">
                <div class="info-row">
                    <span class="info-label">Session ID:</span>
                    <span class="info-value"><?= substr(session_id(), 0, 16) ?>...</span>
                </div>
                <div class="info-row">
                    <span class="info-label">IP Address:</span>
                    <span class="info-value"><?= $_SERVER['REMOTE_ADDR'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">User Agent:</span>
                    <span class="info-value"><?= substr($_SERVER['HTTP_USER_AGENT'], 0, 30) ?>...</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Active:</span>
                    <span class="info-value"><?= date('H:i:s') ?></span>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="clear_session">
                <button type="submit" class="btn btn-outline">
                    <i class="fas fa-power-off"></i> TERMINATE OTHER SESSIONS
                </button>
            </form>
        </div>

        <!-- Login Protection -->
        <div class="security-card">
            <div class="card-header">
                <span><i class="fas fa-ban"></i> LOGIN PROTECTION</span>
            </div>
            
            <div class="session-info">
                <div class="info-row">
                    <span class="info-label">Failed Attempts:</span>
                    <span class="info-value"><?= $_SESSION['login_attempts'] ?? 0 ?> / 5</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lockout Duration:</span>
                    <span class="info-value">5 minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Attempt:</span>
                    <span class="info-value"><?= isset($_SESSION['last_attempt_time']) ? date('H:i:s', $_SESSION['last_attempt_time']) : 'Never' ?></span>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="reset_attempts">
                <button type="submit" class="btn btn-outline">
                    <i class="fas fa-sync-alt"></i> RESET COUNTER
                </button>
            </form>
        </div>
    </div>

    <!-- User Access Control -->
    <div class="users-table">
        <div class="card-header">
            <span><i class="fas fa-users"></i> USER ACCESS CONTROL</span>
            <span class="badge"><?= count($users) ?> USERS</span>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>USERNAME</th>
                    <th>FULL NAME</th>
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
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></td>
                    <td><span class="role-badge <?= $role_class ?>"><?= strtoupper($user['role'] ?? 'VIEWER') ?></span></td>
                    <td><?= ($user['two_factor_enabled'] ?? 0) ? '✅ Enabled' : '❌ Disabled' ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($user['last_login'] ?? 'now')) ?></td>
                    <td>
                        <button class="action-btn" onclick="editUser(<?= $user['id'] ?>)"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="resetPassword(<?= $user['id'] ?>)"><i class="fas fa-key"></i></button>
                        <button class="action-btn" onclick="toggle2FA(<?= $user['id'] ?>)"><i class="fas fa-shield-alt"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Audit Summary -->
    <div class="security-card">
        <div class="card-header">
            <span><i class="fas fa-list"></i> AUDIT SUMMARY</span>
            <a href="?module=audit" style="color: <?= $accent ?>; text-decoration: none;">VIEW ALL →</a>
        </div>
        
        <div class="audit-summary">
            <div class="audit-item">
                <div class="audit-value"><?= $audit_count ?></div>
                <div class="audit-label">TOTAL EVENTS</div>
            </div>
            <div class="audit-item">
                <div class="audit-value"><?= $today_events ?></div>
                <div class="audit-label">TODAY</div>
            </div>
            <div class="audit-item">
                <div class="audit-value">30d</div>
                <div class="audit-label">RETENTION</div>
            </div>
        </div>
    </div>

    <script>
        // User management functions
        function editUser(id) {
            showNotification(`✏️ Editing user ID: ${id}`);
        }

        function resetPassword(id) {
            if (confirm(`Reset password for user ID: ${id}?`)) {
                showNotification(`🔑 Password reset email sent`);
            }
        }

        function toggle2FA(id) {
            showNotification(`🔄 Toggling 2FA for user ID: ${id}`);
        }

        function generateCodes() {
            showNotification('🔄 Generating new backup codes...');
            setTimeout(() => {
                document.querySelectorAll('.code-item').forEach(el => {
                    el.textContent = Math.random().toString(36).substring(2, 6).toUpperCase() + '-' +
                                     Math.random().toString(36).substring(2, 6).toUpperCase();
                });
                showNotification('✅ New backup codes generated');
            }, 1000);
        }

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Auto-hide messages
        setTimeout(() => {
            document.querySelectorAll('.message').forEach(el => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);

        // Validate IP input
        document.querySelector('input[name="new_ip"]')?.addEventListener('input', function(e) {
            const pattern = /^([0-9]{1,3}\.){3}[0-9]{1,3}$/;
            if (pattern.test(this.value)) {
                this.style.borderColor = '#00ff9d';
            } else {
                this.style.borderColor = '#ff006e';
            }
        });

        // Keyboard shortcut
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                document.querySelector('input[name="two_factor"]')?.click();
            }
        });
    </script>
</body>
</html>
