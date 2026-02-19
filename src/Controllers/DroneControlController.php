<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Advanced Drone Control
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';
$role = $_SESSION['role'] ?? 'commander';

// Get drone data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $drones = $pdo->query("SELECT * FROM drones ORDER BY id")->fetchAll();
} catch (Exception $e) {
    // Fallback data
    $drones = [
        ['id' => 1, 'name' => 'DRONE-001', 'status' => 'ACTIVE', 'battery' => 95, 'altitude' => 150, 'speed' => 12],
        ['id' => 2, 'name' => 'DRONE-002', 'status' => 'ACTIVE', 'battery' => 87, 'altitude' => 200, 'speed' => 15],
        ['id' => 3, 'name' => 'DRONE-003', 'status' => 'STANDBY', 'battery' => 100, 'altitude' => 0, 'speed' => 0],
        ['id' => 4, 'name' => 'DRONE-004', 'status' => 'MAINTENANCE', 'battery' => 45, 'altitude' => 0, 'speed' => 0],
        ['id' => 5, 'name' => 'DRONE-005', 'status' => 'ACTIVE', 'battery' => 72, 'altitude' => 120, 'speed' => 10],
        ['id' => 6, 'name' => 'DRONE-006', 'status' => 'ACTIVE', 'battery' => 88, 'altitude' => 180, 'speed' => 14],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - DRONE CONTROL</title>
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
            border: 2px solid #ff006e;
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
            font-size: 2rem;
            color: #ff006e;
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
            font-size: 1.8rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-badge {
            padding: 8px 20px;
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 30px;
        }
        
        .back-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 30px;
            transition: 0.3s;
        }
        
        .back-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        
        .drone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .drone-card {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
        }
        
        .drone-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255,0,110,0.3);
        }
        
        .drone-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .drone-name {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
            font-size: 1.2rem;
        }
        
        .drone-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .status-active { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .status-standby { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .status-maintenance { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        
        .drone-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item-value {
            font-size: 1.2rem;
            color: #00ff9d;
        }
        
        .stat-item-label {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        .battery-bar {
            height: 8px;
            background: #0a0f1c;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .battery-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #00ff9d);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .drone-controls {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        
        .control-btn {
            padding: 10px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .control-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .control-btn.danger:hover {
            background: #ff006e;
            border-color: #ff006e;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 25px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            flex: 1;
            min-width: 120px;
        }
        
        .action-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #00ff9d;
            color: #0a0f1c;
            padding: 15px 25px;
            border-radius: 30px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-drone"></i>
            <h1>ADVANCED DRONE CONTROL</h1>
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
            <div class="stat-value"><?= count($drones) ?></div>
            <div class="stat-label">TOTAL DRONES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= array_sum(array_column($drones, 'battery')) / count($drones) ?>%</div>
            <div class="stat-label">AVG BATTERY</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count(array_filter($drones, fn($d) => $d['status'] == 'ACTIVE')) ?></div>
            <div class="stat-label">ACTIVE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count(array_filter($drones, fn($d) => $d['status'] == 'STANDBY')) ?></div>
            <div class="stat-label">STANDBY</div>
        </div>
    </div>

    <div class="quick-actions">
        <button class="action-btn" onclick="controlAll('launch')"><i class="fas fa-play"></i> LAUNCH ALL</button>
        <button class="action-btn" onclick="controlAll('return')"><i class="fas fa-stop"></i> RETURN ALL</button>
        <button class="action-btn" onclick="controlAll('scan')"><i class="fas fa-search"></i> SCAN AREA</button>
        <button class="action-btn danger" onclick="controlAll('emergency')"><i class="fas fa-exclamation-triangle"></i> EMERGENCY</button>
    </div>

    <div class="drone-grid">
        <?php foreach ($drones as $drone): ?>
        <div class="drone-card" id="drone-<?= $drone['id'] ?>">
            <div class="drone-header">
                <span class="drone-name"><i class="fas fa-drone"></i> <?= $drone['name'] ?></span>
                <span class="drone-status status-<?= strtolower($drone['status']) ?>"><?= $drone['status'] ?></span>
            </div>
            
            <div class="drone-stats">
                <div class="stat-item">
                    <div class="stat-item-value"><?= $drone['battery'] ?>%</div>
                    <div class="stat-item-label">BATTERY</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item-value"><?= $drone['altitude'] ?? 0 ?>m</div>
                    <div class="stat-item-label">ALTITUDE</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item-value"><?= $drone['speed'] ?? 0 ?>m/s</div>
                    <div class="stat-item-label">SPEED</div>
                </div>
            </div>
            
            <div class="battery-bar">
                <div class="battery-fill" style="width: <?= $drone['battery'] ?>%"></div>
            </div>
            
            <div class="drone-controls">
                <button class="control-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'start')">
                    <i class="fas fa-play"></i>
                </button>
                <button class="control-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'stop')">
                    <i class="fas fa-stop"></i>
                </button>
                <button class="control-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'return')">
                    <i class="fas fa-home"></i>
                </button>
                <button class="control-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'record')">
                    <i class="fas fa-video"></i>
                </button>
                <button class="control-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'snapshot')">
                    <i class="fas fa-camera"></i>
                </button>
                <button class="control-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'scan')">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        function controlDrone(id, action) {
            const actions = {
                'start': 'Starting',
                'stop': 'Stopping',
                'return': 'Returning to base',
                'record': 'Recording started',
                'snapshot': 'Snapshot taken',
                'scan': 'Scanning area'
            };
            
            showNotification(`${actions[action]} Drone ${id}`);
            
            // Visual feedback
            const btn = event.target.closest('.control-btn');
            btn.style.background = '#ff006e';
            btn.style.color = '#0a0f1c';
            setTimeout(() => {
                btn.style.background = 'transparent';
                btn.style.color = '#ff006e';
            }, 200);
        }

        function controlAll(action) {
            const messages = {
                'launch': 'Launching all drones',
                'return': 'Returning all drones to base',
                'scan': 'Initiating area scan with all drones',
                'emergency': '⚠️ EMERGENCY: All drones returning to base'
            };
            
            if (action === 'emergency') {
                if (confirm('Emergency landing all drones? This will abort all missions.')) {
                    showNotification(messages[action]);
                }
            } else {
                showNotification(messages[action]);
            }
        }

        // Simulate telemetry updates
        setInterval(() => {
            document.querySelectorAll('.drone-card').forEach(card => {
                const batteryFill = card.querySelector('.battery-fill');
                const batteryValue = card.querySelector('.stat-item:first-child .stat-item-value');
                
                if (batteryFill && batteryValue) {
                    let current = parseInt(batteryFill.style.width);
                    if (current > 0 && Math.random() > 0.7) {
                        const change = Math.random() * 2 - 1;
                        const newValue = Math.max(0, Math.min(100, current + change));
                        batteryFill.style.width = newValue + '%';
                        batteryValue.textContent = Math.round(newValue) + '%';
                    }
                }
            });
        }, 5000);
    </script>
</body>
</html>
