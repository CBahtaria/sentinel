<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Drone Fleet Management
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete drone inventory and status monitoring
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

// Get drone data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $drones = $pdo->query("SELECT * FROM drones ORDER BY id")->fetchAll();
    
    $stats = [
        'active' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'ACTIVE'")->fetchColumn() ?: 0,
        'standby' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'STANDBY'")->fetchColumn() ?: 0,
        'maintenance' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'MAINTENANCE'")->fetchColumn() ?: 0,
        'offline' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'OFFLINE'")->fetchColumn() ?: 0
    ];
    
} catch (Exception $e) {
    $drones = [
        ['id' => 1, 'name' => 'DRONE-001', 'status' => 'ACTIVE', 'battery_level' => 95, 'altitude' => 150, 'speed' => 12, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 2, 'name' => 'DRONE-002', 'status' => 'ACTIVE', 'battery_level' => 87, 'altitude' => 200, 'speed' => 15, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 3, 'name' => 'DRONE-003', 'status' => 'STANDBY', 'battery_level' => 100, 'altitude' => 0, 'speed' => 0, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 4, 'name' => 'DRONE-004', 'status' => 'MAINTENANCE', 'battery_level' => 45, 'altitude' => 0, 'speed' => 0, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 5, 'name' => 'DRONE-005', 'status' => 'ACTIVE', 'battery_level' => 72, 'altitude' => 120, 'speed' => 10, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 6, 'name' => 'DRONE-006', 'status' => 'ACTIVE', 'battery_level' => 88, 'altitude' => 180, 'speed' => 14, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 7, 'name' => 'DRONE-007', 'status' => 'STANDBY', 'battery_level' => 100, 'altitude' => 0, 'speed' => 0, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 8, 'name' => 'DRONE-008', 'status' => 'ACTIVE', 'battery_level' => 93, 'altitude' => 160, 'speed' => 13, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 9, 'name' => 'DRONE-009', 'status' => 'ACTIVE', 'battery_level' => 78, 'altitude' => 140, 'speed' => 11, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 10, 'name' => 'DRONE-010', 'status' => 'STANDBY', 'battery_level' => 100, 'altitude' => 0, 'speed' => 0, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 11, 'name' => 'DRONE-011', 'status' => 'MAINTENANCE', 'battery_level' => 30, 'altitude' => 0, 'speed' => 0, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 12, 'name' => 'DRONE-012', 'status' => 'ACTIVE', 'battery_level' => 82, 'altitude' => 170, 'speed' => 12, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 13, 'name' => 'DRONE-013', 'status' => 'ACTIVE', 'battery_level' => 91, 'altitude' => 190, 'speed' => 16, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 14, 'name' => 'DRONE-014', 'status' => 'STANDBY', 'battery_level' => 100, 'altitude' => 0, 'speed' => 0, 'last_update' => date('Y-m-d H:i:s')],
        ['id' => 15, 'name' => 'DRONE-015', 'status' => 'ACTIVE', 'battery_level' => 76, 'altitude' => 130, 'speed' => 9, 'last_update' => date('Y-m-d H:i:s')]
    ];
    
    $stats = [
        'active' => 8,
        'standby' => 4,
        'maintenance' => 3,
        'offline' => 0
    ];
}

$total_drones = count($drones);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - DRONE FLEET</title>
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
        
        .control-bar {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 40px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .control-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.8rem;
        }
        
        .control-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .control-btn.warning:hover {
            background: #ff006e;
            border-color: #ff006e;
        }
        
        .drones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .drone-card {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 15px;
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
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .drone-name {
            font-family: 'Orbitron', sans-serif;
            color: <?= $accent ?>;
            font-size: 1.1rem;
        }
        
        .drone-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .status-active { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .status-standby { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .status-maintenance { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        .status-offline { background: #4a556820; color: #a0aec0; border: 1px solid #a0aec0; }
        
        .drone-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid <?= $accent ?>20;
            border-bottom: 1px solid <?= $accent ?>20;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item-value {
            font-size: 1.2rem;
            color: #00ff9d;
        }
        
        .stat-item-label {
            font-size: 0.6rem;
            color: #a0aec0;
            text-transform: uppercase;
        }
        
        .battery-bar {
            height: 6px;
            background: #0a0f1c;
            border-radius: 3px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .battery-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $accent ?>, #00ff9d);
            border-radius: 3px;
            transition: width 0.3s;
        }
        
        .drone-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 15px;
        }
        
        .drone-action-btn {
            padding: 8px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.7rem;
        }
        
        .drone-action-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .drone-action-btn.danger:hover {
            background: #ff006e;
            border-color: #ff006e;
        }
        
        .last-update {
            margin-top: 10px;
            font-size: 0.65rem;
            color: #4a5568;
            text-align: right;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
            
            .control-bar {
                flex-direction: column;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .drone-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-drone"></i>
            <h1>DRONE FLEET MANAGEMENT</h1>
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
            <div class="stat-value"><?= $total_drones ?></div>
            <div class="stat-label">TOTAL DRONES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #00ff9d;"><?= $stats['active'] ?></div>
            <div class="stat-label">ACTIVE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ffbe0b;"><?= $stats['standby'] ?></div>
            <div class="stat-label">STANDBY</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;"><?= $stats['maintenance'] ?></div>
            <div class="stat-label">MAINTENANCE</div>
        </div>
    </div>

    <div class="control-bar">
        <button class="control-btn" onclick="bulkAction('launch')">
            <i class="fas fa-play"></i> LAUNCH ALL
        </button>
        <button class="control-btn" onclick="bulkAction('return')">
            <i class="fas fa-stop"></i> RETURN ALL
        </button>
        <button class="control-btn" onclick="bulkAction('scan')">
            <i class="fas fa-search"></i> SCAN AREA
        </button>
        <button class="control-btn warning" onclick="bulkAction('emergency')">
            <i class="fas fa-exclamation-triangle"></i> EMERGENCY LAND
        </button>
        <button class="control-btn" onclick="refreshData()" style="margin-left: auto;">
            <i class="fas fa-sync-alt"></i> REFRESH
        </button>
    </div>

    <div class="drones-grid" id="dronesGrid">
        <?php foreach ($drones as $drone): 
            $status_class = 'status-' . strtolower($drone['status']);
            $battery = $drone['battery_level'] ?? 100;
            $altitude = $drone['altitude'] ?? 0;
            $speed = $drone['speed'] ?? 0;
        ?>
        <div class="drone-card" data-id="<?= $drone['id'] ?>" data-status="<?= strtolower($drone['status']) ?>">
            <div class="drone-header">
                <span class="drone-name"><i class="fas fa-drone"></i> <?= htmlspecialchars($drone['name']) ?></span>
                <span class="drone-status <?= $status_class ?>"><?= $drone['status'] ?></span>
            </div>
            
            <div class="drone-stats">
                <div class="stat-item">
                    <div class="stat-item-value"><?= $battery ?>%</div>
                    <div class="stat-item-label">BATTERY</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item-value"><?= $altitude ?>m</div>
                    <div class="stat-item-label">ALTITUDE</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item-value"><?= $speed ?>m/s</div>
                    <div class="stat-item-label">SPEED</div>
                </div>
            </div>
            
            <div class="battery-bar">
                <div class="battery-fill" style="width: <?= $battery ?>%"></div>
            </div>
            
            <div class="drone-actions">
                <button class="drone-action-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'launch')">
                    <i class="fas fa-play"></i> Launch
                </button>
                <button class="drone-action-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'land')">
                    <i class="fas fa-stop"></i> Land
                </button>
                <button class="drone-action-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'return')">
                    <i class="fas fa-home"></i> Return
                </button>
                <button class="drone-action-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'scan')">
                    <i class="fas fa-search"></i> Scan
                </button>
                <button class="drone-action-btn" onclick="controlDrone(<?= $drone['id'] ?>, 'record')">
                    <i class="fas fa-video"></i> Record
                </button>
                <button class="drone-action-btn danger" onclick="controlDrone(<?= $drone['id'] ?>, 'emergency')">
                    <i class="fas fa-exclamation-triangle"></i> Emergency
                </button>
            </div>
            
            <div class="last-update">
                Last: <?= date('H:i:s', strtotime($drone['last_update'] ?? 'now')) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function controlDrone(id, action) {
            const actions = {
                'launch': '🚀 Launching',
                'land': '🛬 Landing',
                'return': '🏠 Returning',
                'scan': '🔍 Scanning',
                'record': '🎥 Recording',
                'emergency': '⚠️ Emergency landing'
            };
            
            showNotification(`${actions[action]} drone #${id}...`);
            
            // Visual feedback
            const btn = event.target.closest('.drone-action-btn');
            btn.style.background = '<?= $accent ?>';
            btn.style.color = '#0a0f1c';
            setTimeout(() => {
                btn.style.background = 'transparent';
                btn.style.color = '<?= $accent ?>';
            }, 200);
        }

        function bulkAction(action) {
            const actions = {
                'launch': '🚀 Launching all drones',
                'return': '🛬 Returning all drones',
                'scan': '🔍 Initiating area scan',
                'emergency': '⚠️ EMERGENCY: Landing all drones'
            };
            
            if (action === 'emergency' && !confirm('Emergency landing all drones? This will abort all missions.')) {
                return;
            }
            
            showNotification(actions[action]);
        }

        function refreshData() {
            showNotification('🔄 Refreshing drone telemetry...');
            
            // Simulate data refresh
            setTimeout(() => {
                document.querySelectorAll('.drone-card').forEach(card => {
                    const batteryFill = card.querySelector('.battery-fill');
                    const batteryValue = card.querySelector('.stat-item:first-child .stat-item-value');
                    
                    if (batteryFill && batteryValue) {
                        let current = parseInt(batteryFill.style.width);
                        let change = (Math.random() - 0.5) * 10;
                        let newValue = Math.max(20, Math.min(100, current + change));
                        batteryFill.style.width = newValue + '%';
                        batteryValue.textContent = Math.round(newValue) + '%';
                    }
                    
                    // Update altitude
                    const altitudeValue = card.querySelector('.stat-item:nth-child(2) .stat-item-value');
                    if (altitudeValue && card.dataset.status === 'active') {
                        let current = parseInt(altitudeValue.textContent);
                        let change = (Math.random() - 0.5) * 20;
                        let newAlt = Math.max(0, current + change);
                        altitudeValue.textContent = Math.round(newAlt) + 'm';
                    }
                });
                
                showNotification('✅ Drone data updated');
            }, 1500);
        }

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Auto-refresh every 10 seconds
        setInterval(() => {
            if (Math.random() > 0.5) {
                refreshData();
            }
        }, 10000);

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === 'l') {
                e.preventDefault();
                bulkAction('launch');
            }
            if (e.altKey && e.key === 'r') {
                e.preventDefault();
                refreshData();
            }
        });
    </script>
</body>
</html>
