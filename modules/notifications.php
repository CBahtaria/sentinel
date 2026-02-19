<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - System Notifications
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$role = $_SESSION['role'] ?? 'viewer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - NOTIFICATIONS</title>
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
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 4px;
        }
        .notifications-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
        }
        .filters-panel {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 20px;
            border-radius: 8px;
        }
        .filter-title {
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        .filter-group {
            margin-bottom: 20px;
        }
        .filter-group label {
            display: block;
            color: #a0aec0;
            margin-bottom: 8px;
        }
        .filter-option {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0;
            padding: 8px;
            background: #0a0f1c;
            border-radius: 4px;
            cursor: pointer;
        }
        .filter-option:hover {
            background: #00ff9d20;
        }
        .filter-option input[type="checkbox"] {
            accent-color: #00ff9d;
        }
        .notifications-list {
            background: #151f2c;
            border: 1px solid #00ff9d;
            border-radius: 8px;
            overflow: hidden;
        }
        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #00ff9d40;
            display: flex;
            gap: 15px;
            transition: 0.3s;
        }
        .notification-item:hover {
            background: #00ff9d10;
        }
        .notification-item.unread {
            background: #00ff9d20;
            border-left: 4px solid #00ff9d;
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .icon-critical { background: #ff006e40; color: #ff006e; }
        .icon-high { background: #ff8c0040; color: #ff8c00; }
        .icon-info { background: #4cc9f040; color: #4cc9f0; }
        .icon-success { background: #00ff9d40; color: #00ff9d; }
        .notification-content {
            flex: 1;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .notification-title {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
        }
        .notification-time {
            color: #a0aec0;
            font-size: 0.85rem;
        }
        .notification-message {
            color: #e0e0e0;
            margin-bottom: 8px;
        }
        .notification-actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        .action-btn {
            padding: 5px 12px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            cursor: pointer;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .action-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        .mark-read {
            color: #4cc9f0;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 1.8rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
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
        <h1><i class="fas fa-bell"></i> SYSTEM NOTIFICATIONS</h1>
        <div>
            <button class="action-btn" onclick="markAllRead()"><i class="fas fa-check-double"></i> MARK ALL READ</button>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-value">24</div>
            <div>UNREAD</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;">3</div>
            <div>CRITICAL</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">156</div>
            <div>TOTAL</div>
        </div>
    </div>

    <div class="notifications-container">
        <div class="filters-panel">
            <div class="filter-title"><i class="fas fa-filter"></i> FILTERS</div>
            
            <div class="filter-group">
                <label>STATUS</label>
                <div class="filter-option">
                    <input type="checkbox" checked> <span>Unread</span>
                </div>
                <div class="filter-option">
                    <input type="checkbox"> <span>Read</span>
                </div>
            </div>
            
            <div class="filter-group">
                <label>SEVERITY</label>
                <div class="filter-option">
                    <input type="checkbox" checked> <span style="color: #ff006e;">Critical</span>
                </div>
                <div class="filter-option">
                    <input type="checkbox" checked> <span style="color: #ff8c00;">High</span>
                </div>
                <div class="filter-option">
                    <input type="checkbox"> <span style="color: #ffbe0b;">Medium</span>
                </div>
                <div class="filter-option">
                    <input type="checkbox"> <span style="color: #4cc9f0;">Low</span>
                </div>
            </div>
            
            <div class="filter-group">
                <label>TYPE</label>
                <div class="filter-option">
                    <input type="checkbox" checked> <span>Threat Alerts</span>
                </div>
                <div class="filter-option">
                    <input type="checkbox" checked> <span>System Updates</span>
                </div>
                <div class="filter-option">
                    <input type="checkbox"> <span>Drone Status</span>
                </div>
                <div class="filter-option">
                    <input type="checkbox"> <span>User Actions</span>
                </div>
            </div>
            
            <button class="action-btn" style="width: 100%; margin-top: 10px;">APPLY FILTERS</button>
        </div>

        <div class="notifications-list">
            <?php
            $notifications = [
                ['title' => 'CRITICAL THREAT DETECTED', 'message' => 'Unauthorized drone incursion in Sector 7. Immediate response required.', 'time' => '5 min ago', 'type' => 'critical', 'unread' => true],
                ['title' => 'Drone Status Update', 'message' => 'EAGLE-1 battery low (15%). Return to base for charging.', 'time' => '15 min ago', 'type' => 'high', 'unread' => true],
                ['title' => 'System Update Complete', 'message' => 'Security patches applied successfully. System reboot required.', 'time' => '32 min ago', 'type' => 'info', 'unread' => true],
                ['title' => 'New Intel Available', 'message' => 'Satellite imagery processed for Northern Border region.', 'time' => '1 hour ago', 'type' => 'info', 'unread' => false],
                ['title' => 'Maintenance Scheduled', 'message' => 'Drone fleet maintenance scheduled for 02:00 hours.', 'time' => '2 hours ago', 'type' => 'success', 'unread' => false],
                ['title' => 'HIGH SEVERITY THREAT', 'message' => 'Suspicious network activity detected from external IP.', 'time' => '3 hours ago', 'type' => 'high', 'unread' => false],
            ];
            
            foreach ($notifications as $notif):
                $unread_class = $notif['unread'] ? 'unread' : '';
                $icon_class = 'icon-' . $notif['type'];
            ?>
            <div class="notification-item <?= $unread_class ?>">
                <div class="notification-icon <?= $icon_class ?>">
                    <i class="fas fa-<?= $notif['type'] == 'critical' ? 'skull' : ($notif['type'] == 'high' ? 'exclamation' : 'info') ?>"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <span class="notification-title"><?= $notif['title'] ?></span>
                        <span class="notification-time"><?= $notif['time'] ?></span>
                    </div>
                    <div class="notification-message"><?= $notif['message'] ?></div>
                    <div class="notification-actions">
                        <?php if ($notif['unread']): ?>
                            <span class="mark-read" onclick="markRead(this)"><i class="fas fa-check"></i> Mark as Read</span>
                        <?php endif; ?>
                        <span class="mark-read" onclick="viewDetails(this)"><i class="fas fa-eye"></i> View Details</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        function markAllRead() {
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('unread');
            });
            alert('All notifications marked as read');
        }
        
        function markRead(element) {
            const notification = element.closest('.notification-item');
            notification.classList.remove('unread');
            element.style.display = 'none';
        }
        
        function viewDetails(element) {
            alert('Viewing notification details...');
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
