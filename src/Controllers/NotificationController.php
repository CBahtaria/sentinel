<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Emergency Alert System
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Critical alerts and emergency notification management
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';
$role = $_SESSION['role'] ?? 'commander';

$role_colors = [
    'commander' => '#ff006e',
    'operator' => '#ffbe0b',
    'analyst' => '#4cc9f0',
    'viewer' => '#a0aec0'
];
$accent = $role_colors[$role] ?? '#ff006e';

// Handle alert acknowledgments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acknowledge'])) {
        // Acknowledge alert
    }
}

// Sample alerts data
$alerts = [
    [
        'id' => 1,
        'level' => 'critical',
        'title' => 'INTRUSION DETECTED',
        'message' => 'Unauthorized access attempt detected at Command Center. Multiple failed login attempts from external IP.',
        'time' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
        'acknowledged' => false,
        'source' => 'Security System',
        'action' => 'Immediate investigation required',
        'icon' => 'fa-skull-crossbones'
    ],
    [
        'id' => 2,
        'level' => 'high',
        'title' => 'DRONE BREACH',
        'message' => 'Unidentified drone detected in restricted airspace Sector 7. Intercept requested.',
        'time' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
        'acknowledged' => false,
        'source' => 'Radar System',
        'action' => 'Deploy intercept drone',
        'icon' => 'fa-drone'
    ],
    [
        'id' => 3,
        'level' => 'warning',
        'title' => 'SYSTEM OVERLOAD',
        'message' => 'High CPU usage detected (92%). System performance may be affected.',
        'time' => date('Y-m-d H:i:s', strtotime('-12 minutes')),
        'acknowledged' => true,
        'source' => 'System Monitor',
        'action' => 'Check running processes',
        'icon' => 'fa-microchip'
    ],
    [
        'id' => 4,
        'level' => 'critical',
        'title' => 'POWER FAILURE',
        'message' => 'Primary power source failure in Sector 4. Backup generators activated.',
        'time' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
        'acknowledged' => false,
        'source' => 'Power Management',
        'action' => 'Dispatch maintenance team',
        'icon' => 'fa-bolt'
    ],
    [
        'id' => 5,
        'level' => 'high',
        'title' => 'COMMS INTERRUPTION',
        'message' => 'Communication link lost with Drone-005. Attempting to reestablish connection.',
        'time' => date('Y-m-d H:i:s', strtotime('-22 minutes')),
        'acknowledged' => true,
        'source' => 'Comms System',
        'action' => 'Initiate recovery protocol',
        'icon' => 'fa-satellite-dish'
    ],
    [
        'id' => 6,
        'level' => 'warning',
        'title' => 'LOW BATTERY FLEET',
        'message' => 'Multiple drones reporting low battery. 4 drones below 20%.',
        'time' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
        'acknowledged' => false,
        'source' => 'Drone Fleet',
        'action' => 'Recall drones for charging',
        'icon' => 'fa-battery-quarter'
    ]
];

// Sort by time and priority
usort($alerts, function($a, $b) {
    $priority = ['critical' => 3, 'high' => 2, 'warning' => 1];
    $a_priority = $priority[$a['level']] ?? 0;
    $b_priority = $priority[$b['level']] ?? 0;
    
    if ($a_priority == $b_priority) {
        return strtotime($b['time']) - strtotime($a['time']);
    }
    return $b_priority - $a_priority;
});

$critical_count = count(array_filter($alerts, function($a) { return $a['level'] === 'critical' && !$a['acknowledged']; }));
$high_count = count(array_filter($alerts, function($a) { return $a['level'] === 'high' && !$a['acknowledged']; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - EMERGENCY ALERTS</title>
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
            animation: borderPulse 2s infinite;
        }
        
        @keyframes borderPulse {
            0%, 100% { border-color: <?= $accent ?>; }
            50% { border-color: #ff006e; }
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .logo i {
            font-size: 2rem;
            color: #ff006e;
            filter: drop-shadow(0 0 10px #ff006e);
            animation: alertPulse 1s infinite;
        }
        
        @keyframes alertPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.4rem;
            color: #ff006e;
        }
        
        .alert-badge {
            background: #ff006e;
            color: #0a0f1c;
            padding: 4px 12px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 0.8rem;
            animation: pulse 1s infinite;
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
        
        .stat-card.critical {
            border-color: #ff006e;
            animation: glow 1s infinite;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 10px #ff006e; }
            50% { box-shadow: 0 0 20px #ff006e; }
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
        
        .alert-levels {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .level-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.9rem;
            transition: 0.3s;
            flex: 1;
            min-width: 100px;
        }
        
        .level-btn.critical {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .level-btn.high {
            background: #ff8c00;
            color: #0a0f1c;
        }
        
        .level-btn.warning {
            background: #ffbe0b;
            color: #0a0f1c;
        }
        
        .level-btn.info {
            background: #4cc9f0;
            color: #0a0f1c;
        }
        
        .level-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.2);
        }
        
        .alerts-container {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .alert-card {
            background: #151f2c;
            border: 2px solid;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.3s ease;
        }
        
        .alert-card.critical {
            border-color: #ff006e;
            box-shadow: 0 0 20px rgba(255,0,110,0.3);
        }
        
        .alert-card.high {
            border-color: #ff8c00;
        }
        
        .alert-card.warning {
            border-color: #ffbe0b;
        }
        
        .alert-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,0,110,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        .alert-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .alert-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
        }
        
        .alert-title.critical { color: #ff006e; }
        .alert-title.high { color: #ff8c00; }
        .alert-title.warning { color: #ffbe0b; }
        
        .alert-time {
            font-size: 0.8rem;
            color: #a0aec0;
        }
        
        .alert-message {
            font-size: 1rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .alert-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .alert-source {
            display: flex;
            align-items: center;
            gap: 5px;
            color: <?= $accent ?>;
            font-size: 0.9rem;
        }
        
        .alert-action {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #00ff9d;
            font-size: 0.9rem;
        }
        
        .alert-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .ack-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        
        .ack-status.acknowledged {
            color: #00ff9d;
        }
        
        .ack-status.unacknowledged {
            color: #ff006e;
        }
        
        .alert-actions {
            display: flex;
            gap: 10px;
        }
        
        .alert-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.9rem;
        }
        
        .alert-btn.acknowledge {
            border-color: <?= $accent ?>;
            color: <?= $accent ?>;
        }
        
        .alert-btn.acknowledge:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .alert-btn.resolve {
            border-color: #00ff9d;
            color: #00ff9d;
        }
        
        .alert-btn.resolve:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        
        .alert-btn.escalate {
            border-color: #ff006e;
            color: #ff006e;
        }
        
        .alert-btn.escalate:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .alert-progress {
            height: 4px;
            background: #0a0f1c;
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .alert-progress-fill {
            height: 100%;
            width: 0%;
            animation: progress 5s linear forwards;
        }
        
        .alert-progress-fill.critical { background: #ff006e; }
        .alert-progress-fill.high { background: #ff8c00; }
        .alert-progress-fill.warning { background: #ffbe0b; }
        
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        .emergency-protocols {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .protocol-title {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        
        .protocol-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #0a0f1c;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 3px solid #ff006e;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .protocol-name {
            color: #00ff9d;
        }
        
        .protocol-btn {
            padding: 5px 15px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 20px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .protocol-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        <?php
/**
 * UEDF SENTINEL v5.0 - System Verification
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete system check and verification
 */

// Set time limit to unlimited
set_time_limit(0);

// Configuration
$required_files = [
    // Core
    'index.php',
    'login.php',
    'home.php',
    'logout.php',
    
    // AI & Analytics
    'ai-assistant.php',
    'predictive.php',
    'analytics.php',
    'reports.php',
    
    // Drone Management
    'drone-control.php',
    'drones.php',
    'recordings.php',
    
    // Threat Management
    'threat-monitor.php',
    'concurrency.php',
    
    // Security
    'security.php',
    'admin_panel.php',
    'settings.php',
    'audit_log.php',
    
    // Maps
    'map_view.php',
    
    // Notifications
    'notifications.php',
    'alerts.php',
    
    // Utilities
    'health.php',
    'test_db.php',
    'quick-access.php',
    
    // Config
    'config/database.php'
];

$required_directories = [
    'logs',
    'cache',
    'uploads',
    'backups',
    'modules',
    'api',
    'src/AI',
    'src/Database',
    'src/Security',
    'includes'
];

$results = [
    'files' => [],
    'directories' => [],
    'database' => null,
    'php' => null,
    'warnings' => []
];

// Check PHP version
$results['php'] = [
    'version' => PHP_VERSION,
    'modules' => get_loaded_extensions()
];

// Check files
foreach ($required_files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $size = $exists ? filesize(__DIR__ . '/' . $file) : 0;
    $readable = $exists ? is_readable(__DIR__ . '/' . $file) : false;
    $writable = $exists ? is_writable(__DIR__ . '/' . $file) : false;
    
    $results['files'][$file] = [
        'exists' => $exists,
        'size' => $size,
        'readable' => $readable,
        'writable' => $writable
    ];
    
    if (!$exists) {
        $results['warnings'][] = "Missing file: $file";
    }
}

// Check directories
foreach ($required_directories as $dir) {
    $exists = is_dir(__DIR__ . '/' . $dir);
    $writable = $exists ? is_writable(__DIR__ . '/' . $dir) : false;
    
    $results['directories'][$dir] = [
        'exists' => $exists,
        'writable' => $writable
    ];
    
    if (!$exists) {
        $results['warnings'][] = "Missing directory: $dir";
    }
}

// Check database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $table_counts = [];
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        $table_counts[$table] = $count;
    }
    
    $results['database'] = [
        'connected' => true,
        'tables' => $tables,
        'counts' => $table_counts,
        'total_tables' => count($tables)
    ];
    
} catch (Exception $e) {
    $results['database'] = [
        'connected' => false,
        'error' => $e->getMessage()
    ];
    $results['warnings'][] = "Database connection failed: " . $e->getMessage();
}

// Check required PHP extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'openssl', 'curl', 'gd', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $results['warnings'][] = "Missing PHP extension: $ext";
    }
}

// Calculate total size
$total_size = 0;
foreach ($results['files'] as $file) {
    if ($file['exists']) {
        $total_size += $file['size'];
    }
}
$results['total_size'] = round($total_size / 1024 / 1024, 2) . ' MB';

// Get system info
$results['system'] = [
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'script_path' => __DIR__,
    'time' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
];

// Output as HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - System Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 20px;
        }
        .summary {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-value {
            font-size: 2rem;
            color: #00ff9d;
        }
        .summary-label {
            color: #a0aec0;
            font-size: 0.8rem;
        }
        .section {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            padding: 10px;
            color: #ff006e;
            border-bottom: 2px solid #ff006e;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #ff006e40;
        }
        .success { color: #00ff9d; }
        .warning { color: #ffbe0b; }
        .error { color: #ff006e; }
        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .badge-success { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .badge-warning { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .badge-error { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        .warnings-list {
            list-style: none;
        }
        .warnings-list li {
            padding: 8px;
            margin-bottom: 5px;
            background: #ff006e20;
            border-left: 3px solid #ff006e;
            color: #ff006e;
        }
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 UEDF SENTINEL - SYSTEM VERIFICATION</h1>
        
        <div class="summary">
            <h2 class="section-title">📊 SYSTEM SUMMARY</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value"><?= count(array_filter($results['files'], fn($f) => $f['exists'])) ?></div>
                    <div class="summary-label">FILES FOUND</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?= count($results['files']) - count(array_filter($results['files'], fn($f) => $f['exists'])) ?></div>
                    <div class="summary-label">MISSING FILES</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?= $results['database']['connected'] ? '✅' : '❌' ?></div>
                    <div class="summary-label">DATABASE</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?= $results['total_size'] ?></div>
                    <div class="summary-label">TOTAL SIZE</div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($results['warnings'])): ?>
        <div class="section">
            <h2 class="section-title">⚠️ WARNINGS & ISSUES</h2>
            <ul class="warnings-list">
                <?php foreach ($results['warnings'] as $warning): ?>
                <li><?= htmlspecialchars($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2 class="section-title">📁 FILE CHECK</h2>
            <table>
                <tr>
                    <th>FILE</th>
                    <th>STATUS</th>
                    <th>SIZE</th>
                    <th>PERMISSIONS</th>
                </tr>
                <?php foreach ($results['files'] as $file => $info): ?>
                <tr>
                    <td><?= htmlspecialchars($file) ?></td>
                    <td>
                        <?php if ($info['exists']): ?>
                        <span class="badge badge-success">EXISTS</span>
                        <?php else: ?>
                        <span class="badge badge-error">MISSING</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $info['exists'] ? round($info['size'] / 1024, 2) . ' KB' : '-' ?></td>
                    <td>
                        <?php if ($info['exists']): ?>
                            <?= $info['readable'] ? 'R' : '-' ?> <?= $info['writable'] ? 'W' : '-' ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="section">
            <h2 class="section-title">📂 DIRECTORY CHECK</h2>
            <table>
                <tr>
                    <th>DIRECTORY</th>
                    <th>STATUS</th>
                    <th>WRITABLE</th>
                </tr>
                <?php foreach ($results['directories'] as $dir => $info): ?>
                <tr>
                    <td><?= htmlspecialchars($dir) ?></td>
                    <td>
                        <?php if ($info['exists']): ?>
                        <span class="badge badge-success">EXISTS</span>
                        <?php else: ?>
                        <span class="badge badge-error">MISSING</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $info['exists'] ? ($info['writable'] ? '✅' : '❌') : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="section">
            <h2 class="section-title">🗄️ DATABASE CHECK</h2>
            <?php if ($results['database']['connected']): ?>
            <p class="success">✅ Database connected successfully</p>
            <p>Total tables: <?= $results['database']['total_tables'] ?></p>
            <table>
                <tr>
                    <th>TABLE</th>
                    <th>RECORDS</th>
                </tr>
                <?php foreach ($results['database']['counts'] as $table => $count): ?>
                <tr>
                    <td><?= htmlspecialchars($table) ?></td>
                    <td><?= number_format($count) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p class="error">❌ Database connection failed: <?= htmlspecialchars($results['database']['error']) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2 class="section-title">⚙️ SYSTEM INFO</h2>
            <table>
                <tr>
                    <th>PROPERTY</th>
                    <th>VALUE</th>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><?= $results['php']['version'] ?></td>
                </tr>
                <tr>
                    <td>Server Software</td>
                    <td><?= htmlspecialchars($results['system']['server']) ?></td>
                </tr>
                <tr>
                    <td>Document Root</td>
                    <td><?= htmlspecialchars($results['system']['document_root']) ?></td>
                </tr>
                <tr>
                    <td>Script Path</td>
                    <td><?= htmlspecialchars($results['system']['script_path']) ?></td>
                </tr>
                <tr>
                    <td>Current Time</td>
                    <td><?= $results['system']['time'] ?></td>
                </tr>
                <tr>
                    <td>Your IP</td>
                    <td><?= $results['system']['ip'] ?></td>
                </tr>
            </table>
        </div>
        
        <div style="text-align: center; color: #4a5568; margin-top: 20px;">
            <p>UEDF SENTINEL v5.0 - System Verification Complete</p>
            <p><a href="?module=home" style="color: #ff006e;">← Return to Dashboard</a></p>
        </div>
    </div>
</body>
</html>
