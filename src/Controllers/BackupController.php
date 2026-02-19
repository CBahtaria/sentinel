<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Backup Manager
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete backup and restore system
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

// Only commander and admin can access backup manager
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

// Backup directory
$backup_dir = __DIR__ . '/backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Handle backup actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_backup':
                $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                $filepath = $backup_dir . $filename;
                
                // Create database backup
                $command = sprintf(
                    'mysqldump -u %s %s > %s',
                    'root',
                    'uedf_sentinel',
                    $filepath
                );
                
                system($command, $result);
                
                if ($result === 0) {
                    $message = "✅ Backup created successfully: $filename";
                    $message_type = 'success';
                } else {
                    $message = "❌ Failed to create backup";
                    $message_type = 'error';
                }
                break;
                
            case 'restore_backup':
                $backup_file = $_POST['backup_file'] ?? '';
                $filepath = $backup_dir . $backup_file;
                
                if (file_exists($filepath)) {
                    $command = sprintf(
                        'mysql -u %s %s < %s',
                        'root',
                        'uedf_sentinel',
                        $filepath
                    );
                    
                    system($command, $result);
                    
                    if ($result === 0) {
                        $message = "✅ Database restored from: $backup_file";
                        $message_type = 'success';
                    } else {
                        $message = "❌ Failed to restore database";
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'delete_backup':
                $backup_file = $_POST['backup_file'] ?? '';
                $filepath = $backup_dir . $backup_file;
                
                if (file_exists($filepath) && unlink($filepath)) {
                    $message = "✅ Backup deleted: $backup_file";
                    $message_type = 'success';
                } else {
                    $message = "❌ Failed to delete backup";
                    $message_type = 'error';
                }
                break;
                
            case 'cleanup_old':
                $files = glob($backup_dir . '*.sql');
                $now = time();
                $deleted = 0;
                
                foreach ($files as $file) {
                    if ($now - filemtime($file) > 30 * 24 * 60 * 60) { // 30 days
                        if (unlink($file)) {
                            $deleted++;
                        }
                    }
                }
                
                $message = "✅ Cleaned up $deleted old backup(s)";
                $message_type = 'success';
                break;
        }
    }
}

// Get list of backups
$backups = glob($backup_dir . '*.sql');
usort($backups, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Calculate total size
$total_size = 0;
foreach ($backups as $backup) {
    $total_size += filesize($backup);
}

// Get database size
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $db_size = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = 'uedf_sentinel'")->fetchColumn() ?: 0;
    $table_count = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'uedf_sentinel'")->fetchColumn() ?: 0;
} catch (Exception $e) {
    $db_size = 0;
    $table_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - BACKUP MANAGER</title>
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
        
        .action-bar {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 12px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .action-btn {
            padding: 12px 25px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn:hover {
            background: #00ff9d;
            transform: translateY(-2px);
        }
        
        .action-btn.warning {
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
        }
        
        .action-btn.warning:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .backups-list {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .list-title {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
        }
        
        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #0a0f1c;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 3px solid <?= $accent ?>;
            flex-wrap: wrap;
            gap: 15px;
            transition: 0.3s;
        }
        
        .backup-item:hover {
            transform: translateX(5px);
            background: <?= $accent ?>10;
        }
        
        .backup-info {
            flex: 1;
        }
        
        .backup-name {
            color: <?= $accent ?>;
            font-size: 1rem;
            margin-bottom: 5px;
        }
        
        .backup-meta {
            display: flex;
            gap: 20px;
            font-size: 0.8rem;
            color: #a0aec0;
            flex-wrap: wrap;
        }
        
        .backup-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .backup-actions {
            display: flex;
            gap: 8px;
        }
        
        .backup-btn {
            padding: 8px 15px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.8rem;
        }
        
        .backup-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .backup-btn.restore:hover {
            background: #00ff9d;
            border-color: #00ff9d;
        }
        
        .backup-btn.delete:hover {
            background: #ff006e;
            border-color: #ff006e;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #4a5568;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .progress-bar {
            height: 4px;
            background: #0a0f1c;
            border-radius: 2px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $accent ?>, #00ff9d);
            width: 0%;
            transition: width 0.3s;
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
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .action-bar {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
            
            .backup-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .backup-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .backup-meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-database"></i>
            <h1>BACKUP MANAGER</h1>
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
            <div class="stat-value"><?= count($backups) ?></div>
            <div class="stat-label">TOTAL BACKUPS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= round($total_size / 1024 / 1024, 2) ?> MB</div>
            <div class="stat-label">BACKUP SIZE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $db_size ?> MB</div>
            <div class="stat-label">DATABASE SIZE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $table_count ?></div>
            <div class="stat-label">TABLES</div>
        </div>
    </div>

    <div class="action-bar">
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="create_backup">
            <button type="submit" class="action-btn">
                <i class="fas fa-plus-circle"></i> CREATE NEW BACKUP
            </button>
        </form>
        
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="cleanup_old">
            <button type="submit" class="action-btn warning">
                <i class="fas fa-trash"></i> CLEANUP OLD (>30 DAYS)
            </button>
        </form>
        
        <div style="margin-left: auto; color: #a0aec0;">
            Last backup: <?= !empty($backups) ? date('Y-m-d H:i', filemtime($backups[0])) : 'Never' ?>
        </div>
    </div>

    <div class="backups-list">
        <div class="list-header">
            <div class="list-title"><i class="fas fa-history"></i> AVAILABLE BACKUPS</div>
            <span><?= count($backups) ?> backup(s) found</span>
        </div>
        
        <?php if (empty($backups)): ?>
        <div class="empty-state">
            <i class="fas fa-database"></i>
            <p>No backups found</p>
            <p style="font-size: 0.8rem;">Create your first backup using the button above</p>
        </div>
        <?php else: ?>
            <?php foreach ($backups as $backup): 
                $filename = basename($backup);
                $size = filesize($backup);
                $modified = filemtime($backup);
                $age = time() - $modified;
            ?>
            <div class="backup-item">
                <div class="backup-info">
                    <div class="backup-name"><?= htmlspecialchars($filename) ?></div>
                    <div class="backup-meta">
                        <span><i class="fas fa-calendar"></i> <?= date('Y-m-d H:i:s', $modified) ?></span>
                        <span><i class="fas fa-database"></i> <?= round($size / 1024 / 1024, 2) ?> MB</span>
                        <span><i class="fas fa-clock"></i> <?= floor($age / 86400) ?>d <?= floor(($age % 86400) / 3600) ?>h old</span>
                    </div>
                </div>
                
                <div class="backup-actions">
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Restore this backup? Current data will be overwritten.');">
                        <input type="hidden" name="action" value="restore_backup">
                        <input type="hidden" name="backup_file" value="<?= htmlspecialchars($filename) ?>">
                        <button type="submit" class="backup-btn restore" title="Restore">
                            <i class="fas fa-undo-alt"></i> RESTORE
                        </button>
                    </form>
                    
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this backup?');">
                        <input type="hidden" name="action" value="delete_backup">
                        <input type="hidden" name="backup_file" value="<?= htmlspecialchars($filename) ?>">
                        <button type="submit" class="backup-btn delete" title="Delete">
                            <i class="fas fa-trash"></i> DELETE
                        </button>
                    </form>
                    
                    <a href="backups/<?= urlencode($filename) ?>" class="backup-btn" download title="Download">
                        <i class="fas fa-download"></i> DOWNLOAD
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide messages
        setTimeout(() => {
            document.querySelectorAll('.message').forEach(el => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);

        // Show notification for long operations
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const action = e.target.querySelector('input[name="action"]')?.value;
                if (action === 'create_backup') {
                    showNotification('⏳ Creating backup... This may take a moment');
                } else if (action === 'restore_backup') {
                    showNotification('⏳ Restoring database... Please wait');
                }
            });
        });

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Keyboard shortcut
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                document.querySelector('input[value="create_backup"]')?.closest('form').submit();
            }
        });
    </script>
</body>
</html>
