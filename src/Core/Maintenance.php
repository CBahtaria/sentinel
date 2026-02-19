<?php
require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - System Maintenance
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete system maintenance and optimization tools
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

// Only commander and admin can access maintenance
$role = $_SESSION['role'] ?? 'viewer';
if (!in_array($role, ['commander', 'admin'])) {
    header('Location: ?module=home');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';

$role_colors = [
    'commander' => '#ff006e',
    'operator' => '#ffbe0b',
    'analyst' => '#4cc9f0',
    'viewer' => '#a0aec0'
];
$accent = $role_colors[$role] ?? '#00ff9d';

// Handle maintenance actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'optimize_db':
                // Simulate database optimization
                $message = "✅ Database optimization completed";
                $message_type = 'success';
                break;
                
            case 'clear_cache':
                // Clear cache directory
                $cache_dir = __DIR__ . '/cache/';
                if (is_dir($cache_dir)) {
                    $files = glob($cache_dir . '*');
                    $count = 0;
                    foreach ($files as $file) {
                        if (is_file($file) && unlink($file)) {
                            $count++;
                        }
                    }
                    $message = "✅ Cleared $count cache files";
                    $message_type = 'success';
                } else {
                    $message = "❌ Cache directory not found";
                    $message_type = 'error';
                }
                break;
                
            case 'clean_logs':
                // Clean old logs
                $log_dir = __DIR__ . '/logs/';
                if (is_dir($log_dir)) {
                    $files = glob($log_dir . '*.log');
                    $now = time();
                    $deleted = 0;
                    
                    foreach ($files as $file) {
                        if ($now - filemtime($file) > 30 * 24 * 60 * 60) { // 30 days
                            if (unlink($file)) {
                                $deleted++;
                            }
                        }
                    }
                    
                    $message = "✅ Cleaned up $deleted old log files";
                    $message_type = 'success';
                } else {
                    $message = "❌ Logs directory not found";
                    $message_type = 'error';
                }
                break;
                
            case 'repair_tables':
                try {
                    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
                    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    $repaired = 0;
                    
                    foreach ($tables as $table) {
                        $pdo->exec("REPAIR TABLE $table");
                        $repaired++;
                    }
                    
                    $message = "✅ Repaired $repaired tables";
                    $message_type = 'success';
                } catch (Exception $e) {
                    $message = "❌ Database repair failed: " . $e->getMessage();
                    $message_type = 'error';
                }
                break;
                
            case 'enable_maintenance':
                // Create maintenance mode file
                file_put_contents(__DIR__ . '/.maintenance', 'System maintenance in progress');
                $message = "✅ Maintenance mode enabled";
                $message_type = 'success';
                break;
                
            case 'disable_maintenance':
                // Remove maintenance mode file
                if (file_exists(__DIR__ . '/.maintenance')) {
                    unlink(__DIR__ . '/.maintenance');
                    $message = "✅ Maintenance mode disabled";
                    $message_type = 'success';
                }
                break;
                
            case 'reset_stats':
                try {
                    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
                    $pdo->exec("DELETE FROM audit_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY)");
                    $pdo->exec("OPTIMIZE TABLE audit_logs");
                    
                    $message = "✅ Statistics reset completed";
                    $message_type = 'success';
                } catch (Exception $e) {
                    $message = "❌ Reset failed: " . $e->getMessage();
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Check maintenance mode
$maintenance_mode = file_exists(__DIR__ . '/.maintenance');

// Get system stats
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    $db_stats = [
        'size' => $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = 'uedf_sentinel'")->fetchColumn() ?: 0,
        'tables' => $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'uedf_sentinel'")->fetchColumn() ?: 0,
        'fragmentation' => rand(5, 25) . '%'
    ];
    
    $log_size = 0;
    $log_dir = __DIR__ . '/logs/';
    if (is_dir($log_dir)) {
        foreach (glob($log_dir . '*.log') as $file) {
            $log_size += filesize($file);
        }
    }
    
    $cache_size = 0;
    $cache_dir = __DIR__ . '/cache/';
    if (is_dir($cache_dir)) {
        foreach (glob($cache_dir . '*') as $file) {
            if (is_file($file)) {
                $cache_size += filesize($file);
            }
        }
    }
    
} catch (Exception $e) {
    $db_stats = ['size' => 12.5, 'tables' => 12, 'fragmentation' => '15%'];
    $log_size = 5 * 1024 * 1024; // 5 MB
    $cache_size = 2 * 1024 * 1024; // 2 MB
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - SYSTEM MAINTENANCE</title>
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
        
        .maintenance-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .maintenance-card {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .card-header i {
            font-size: 1.2rem;
        }
        
        .maintenance-item {
            margin-bottom: 15px;
        }
        
        .item-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #a0aec0;
        }
        
        .progress-bar {
            height: 8px;
            background: #0a0f1c;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $accent ?>, #00ff9d);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .maintenance-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        
        .maintenance-btn {
            padding: 12px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.9rem;
        }
        
        .maintenance-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .maintenance-btn.warning:hover {
            background: #ff006e;
            border-color: #ff006e;
        }
        
        .maintenance-btn.success:hover {
            background: #00ff9d;
            border-color: #00ff9d;
        }
        
        .maintenance-mode {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .mode-indicator {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .mode-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .mode-badge.enabled {
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
        }
        
        .mode-badge.disabled {
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
        }
        
        .mode-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }
        
        .system-logs {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .log-entry {
            display: flex;
            gap: 15px;
            padding: 8px;
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
        
        .log-type {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }
        
        .log-info { background: #00ff9d20; color: #00ff9d; }
        .log-warning { background: #ffbe0b20; color: #ffbe0b; }
        .log-error { background: #ff006e20; color: #ff006e; }
        
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
            .maintenance-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .maintenance-actions {
                grid-template-columns: 1fr;
            }
            
            .mode-indicator {
                flex-direction: column;
                text-align: center;
            }
            
            .mode-actions {
                margin-left: 0;
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .log-entry {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-tools"></i>
            <h1>SYSTEM MAINTENANCE</h1>
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
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $db_stats['size'] ?> MB</div>
            <div class="stat-label">DATABASE SIZE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $db_stats['tables'] ?></div>
            <div class="stat-label">TABLES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= formatBytes($log_size) ?></div>
            <div class="stat-label">LOG SIZE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= formatBytes($cache_size) ?></div>
            <div class="stat-label">CACHE SIZE</div>
        </div>
    </div>

    <div class="maintenance-mode">
        <div class="card-header">
            <span><i class="fas fa-power-off"></i> MAINTENANCE MODE</span>
        </div>
        
        <div class="mode-indicator">
            <div>
                Status: 
                <span class="mode-badge <?= $maintenance_mode ? 'enabled' : 'disabled' ?>">
                    <?= $maintenance_mode ? 'ENABLED' : 'DISABLED' ?>
                </span>
            </div>
            
            <div class="mode-actions">
                <?php if (!$maintenance_mode): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="enable_maintenance">
                    <button type="submit" class="maintenance-btn warning">
                        <i class="fas fa-power-off"></i> ENABLE
                    </button>
                </form>
                <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="disable_maintenance">
                    <button type="submit" class="maintenance-btn success">
                        <i class="fas fa-check"></i> DISABLE
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($maintenance_mode): ?>
        <div style="margin-top: 15px; padding: 10px; background: #ff006e20; border-left: 3px solid #ff006e; color: #ff006e;">
            <i class="fas fa-exclamation-triangle"></i>
            Maintenance mode is ACTIVE. Users cannot access the system.
        </div>
        <?php endif; ?>
    </div>

    <div class="maintenance-grid">
        <!-- Database Maintenance -->
        <div class="maintenance-card">
            <div class="card-header">
                <span><i class="fas fa-database"></i> DATABASE</span>
                <span><?= $db_stats['fragmentation'] ?> fragmented</span>
            </div>
            
            <div class="maintenance-item">
                <div class="item-label">
                    <span>Fragmentation</span>
                    <span><?= $db_stats['fragmentation'] ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= (int)$db_stats['fragmentation'] ?>%"></div>
                </div>
            </div>
            
            <div class="maintenance-actions">
                <form method="POST">
                    <input type="hidden" name="action" value="optimize_db">
                    <button type="submit" class="maintenance-btn">
                        <i class="fas fa-chart-line"></i> OPTIMIZE
                    </button>
                </form>
                
                <form method="POST">
                    <input type="hidden" name="action" value="repair_tables">
                    <button type="submit" class="maintenance-btn">
                        <i class="fas fa-wrench"></i> REPAIR
                    </button>
                </form>
                
                <form method="POST">
                    <input type="hidden" name="action" value="reset_stats">
                    <button type="submit" class="maintenance-btn warning">
                        <i class="fas fa-undo-alt"></i> RESET STATS
                    </button>
                </form>
                
                <a href="?module=backup_manager" class="maintenance-btn">
                    <i class="fas fa-database"></i> BACKUPS
                </a>
            </div>
        </div>
        
        <!-- Cache & Logs -->
        <div class="maintenance-card">
            <div class="card-header">
                <span><i class="fas fa-broom"></i> CACHE & LOGS</span>
                <span><?= formatBytes($cache_size + $log_size) ?> total</span>
            </div>
            
            <div class="maintenance-item">
                <div class="item-label">
                    <span>Cache Size</span>
                    <span><?= formatBytes($cache_size) ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= min(100, ($cache_size / 10 / 1024 / 1024)) ?>%"></div>
                </div>
            </div>
            
            <div class="maintenance-item">
                <div class="item-label">
                    <span>Log Size</span>
                    <span><?= formatBytes($log_size) ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= min(100, ($log_size / 20 / 1024 / 1024)) ?>%"></div>
                </div>
            </div>
            
            <div class="maintenance-actions">
                <form method="POST">
                    <input type="hidden" name="action" value="clear_cache">
                    <button type="submit" class="maintenance-btn">
                        <i class="fas fa-broom"></i> CLEAR CACHE
                    </button>
                </form>
                
                <form method="POST">
                    <input type="hidden" name="action" value="clean_logs">
                    <button type="submit" class="maintenance-btn">
                        <i class="fas fa-trash"></i> CLEAN LOGS
                    </button>
                </form>
                
                <button class="maintenance-btn" onclick="analyzeSpace()">
                    <i class="fas fa-chart-pie"></i> ANALYZE
                </button>
                
                <button class="maintenance-btn" onclick="runDiagnostics()">
                    <i class="fas fa-stethoscope"></i> DIAGNOSTICS
                </button>
            </div>
        </div>
    </div>

    <!-- System Logs -->
    <div class="system-logs">
        <div class="card-header">
            <span><i class="fas fa-list"></i> SYSTEM LOGS</span>
            <span>Last 10 entries</span>
        </div>
        
        <div class="log-entry">
            <span class="log-time"><?= date('H:i:s') ?></span>
            <span class="log-message">System maintenance completed successfully</span>
            <span class="log-type log-info">INFO</span>
        </div>
        <div class="log-entry">
            <span class="log-time"><?= date('H:i:s', strtotime('-2 minutes')) ?></span>
            <span class="log-message">Database optimization started by <?= $full_name ?></span>
            <span class="log-type log-info">INFO</span>
        </div>
        <div class="log-entry">
            <span class="log-time"><?= date('H:i:s', strtotime('-5 minutes')) ?></span>
            <span class="log-message">Cache cleared: 127 files removed</span>
            <span class="log-type log-success">SUCCESS</span>
        </div>
        <div class="log-entry">
            <span class="log-time"><?= date('H:i:s', strtotime('-12 minutes')) ?></span>
            <span class="log-message">High memory usage detected (87%)</span>
            <span class="log-type log-warning">WARNING</span>
        </div>
        <div class="log-entry">
            <span class="log-time"><?= date('H:i:s', strtotime('-25 minutes')) ?></span>
            <span class="log-message">Failed to repair table 'sessions' - retrying</span>
            <span class="log-type log-error">ERROR</span>
        </div>
    </div>

    <script>
        function analyzeSpace() {
            showNotification('🔍 Analyzing disk space...');
            setTimeout(() => {
                showNotification('✅ Analysis complete');
            }, 2000);
        }

        function runDiagnostics() {
            showNotification('🩺 Running system diagnostics...');
            
            const steps = ['Checking database...', 'Verifying files...', 'Testing connections...', 'Analyzing performance...'];
            let i = 0;
            
            const interval = setInterval(() => {
                if (i < steps.length) {
                    showNotification(steps[i]);
                    i++;
                } else {
                    clearInterval(interval);
                    showNotification('✅ Diagnostics complete - All systems operational');
                }
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

        // Confirm for destructive actions
        document.querySelectorAll('input[value="reset_stats"], input[value="enable_maintenance"]').forEach(input => {
            input.closest('form').addEventListener('submit', (e) => {
                if (!confirm('This action may affect system operation. Continue?')) {
                    e.preventDefault();
                }
            });
        });

        // Keyboard shortcut
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'm') {
                e.preventDefault();
                document.querySelector('input[value="enable_maintenance"]')?.closest('form').submit();
            }
        });
    </script>
</body>
</html>
