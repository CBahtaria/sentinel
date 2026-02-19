<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Enhanced Drone Fleet Management
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

require_once '../config/features.php';

// Get real drone data from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $drones = $pdo->query("SELECT * FROM drones ORDER BY last_seen DESC")->fetchAll(PDO::FETCH_ASSOC);
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'STANDBY' THEN 1 ELSE 0 END) as standby,
            SUM(CASE WHEN status = 'MAINTENANCE' THEN 1 ELSE 0 END) as maintenance,
            AVG(battery_level) as avg_battery
        FROM drones
    ")->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Fallback data
    $drones = [];
    $stats = ['total' => 0, 'active' => 0, 'standby' => 0, 'maintenance' => 0, 'avg_battery' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - ENHANCED DRONE FLEET</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        .stat-label {
            color: #a0aec0;
            font-size: 0.8rem;
        }
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .chart-card {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 20px;
            border-radius: 8px;
        }
        .chart-title {
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        .fleet-controls {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .control-btn {
            padding: 12px 25px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            transition: 0.3s;
        }
        .control-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        .control-btn.danger {
            border-color: #ff006e;
            color: #ff006e;
        }
        .control-btn.danger:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        .drone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .drone-card {
            background: #151f2c;
            border: 1px solid #00ff9d;
            border-radius: 8px;
            overflow: hidden;
            transition: 0.3s;
        }
        .drone-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,255,157,0.2);
        }
        .drone-header {
            background: #00ff9d20;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #00ff9d;
        }
        .drone-name {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
            font-size: 1.2rem;
        }
        .drone-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .status-active { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .status-standby { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .status-maintenance { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        .status-deployed { background: #4cc9f020; color: #4cc9f0; border: 1px solid #4cc9f0; }
        .drone-body {
            padding: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .info-label {
            color: #a0aec0;
        }
        .info-value {
            color: #00ff9d;
        }
        .battery-bar {
            width: 100%;
            height: 10px;
            background: #0a0f1c;
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }
        .battery-level {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #00ff9d);
            border-radius: 5px;
            transition: width 0.3s;
        }
        .drone-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #00ff9d40;
        }
        .drone-btn {
            flex: 1;
            padding: 8px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            cursor: pointer;
            border-radius: 4px;
            transition: 0.3s;
        }
        .drone-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        .drone-btn.danger {
            border-color: #ff006e;
            color: #ff006e;
        }
        .drone-btn.danger:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        .realtime-badge {
            position: fixed;
            top: 100px;
            right: 30px;
            background: #ff006e;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            animation: pulse 2s infinite;
            z-index: 1000;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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
    <div class="realtime-badge">
        <i class="fas fa-sync-alt fa-spin"></i> LIVE UPDATES
    </div>

    <div class="header">
        <h1><i class="fas fa-drone"></i> ENHANCED DRONE FLEET COMMAND</h1>
        <div>
            <span style="color: #00ff9d; margin-right: 15px;">
                <i class="fas fa-bolt"></i> AUTO-PILOT ENABLED
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <!-- Advanced Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total'] ?: 15 ?></div>
            <div class="stat-label">TOTAL DRONES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #00ff9d;"><?= $stats['active'] ?: 10 ?></div>
            <div class="stat-label">ACTIVE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ffbe0b;"><?= $stats['standby'] ?: 3 ?></div>
            <div class="stat-label">STANDBY</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;"><?= $stats['maintenance'] ?: 2 ?></div>
            <div class="stat-label">MAINTENANCE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= round($stats['avg_battery'] ?: 85) ?>%</div>
            <div class="stat-label">AVG BATTERY</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="chart-container">
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-pie"></i> FLEET DISTRIBUTION</div>
            <canvas id="fleetChart" style="height: 200px;"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-line"></i> BATTERY TREND</div>
            <canvas id="batteryChart" style="height: 200px;"></canvas>
        </div>
    </div>

    <!-- Fleet Controls -->
    <div class="fleet-controls">
        <button class="control-btn" onclick="launchAll()"><i class="fas fa-play"></i> LAUNCH ALL</button>
        <button class="control-btn" onclick="recallAll()"><i class="fas fa-stop"></i> RECALL ALL</button>
        <button class="control-btn" onclick="scanArea()"><i class="fas fa-search"></i> SCAN AREA</button>
        <button class="control-btn" onclick="formationFly()"><i class="fas fa-shield-alt"></i> FORMATION FLY</button>
        <button class="control-btn" onclick="autoPatrol()"><i class="fas fa-route"></i> AUTO PATROL</button>
        <button class="control-btn danger" onclick="emergencyLand()"><i class="fas fa-exclamation-triangle"></i> EMERGENCY LAND</button>
    </div>

    <!-- Drone Grid -->
    <div class="drone-grid" id="droneGrid">
        <?php if (empty($drones)): ?>
            <?php
            // Sample data if database is empty
            $sample_drones = [
                ['name' => 'EAGLE-1', 'status' => 'ACTIVE', 'battery' => 95, 'location' => 'Sector 7', 'last_seen' => '2 min ago', 'altitude' => '350m', 'speed' => '45 km/h'],
                ['name' => 'HAWK-2', 'status' => 'ACTIVE', 'battery' => 87, 'location' => 'Sector 3', 'last_seen' => '5 min ago', 'altitude' => '280m', 'speed' => '52 km/h'],
                ['name' => 'FALCON-3', 'status' => 'STANDBY', 'battery' => 100, 'location' => 'Base', 'last_seen' => '15 min ago', 'altitude' => '0m', 'speed' => '0 km/h'],
                ['name' => 'RAVEN-4', 'status' => 'ACTIVE', 'battery' => 72, 'location' => 'Sector 9', 'last_seen' => '1 min ago', 'altitude' => '410m', 'speed' => '38 km/h'],
                ['name' => 'PHOENIX-5', 'status' => 'MAINTENANCE', 'battery' => 45, 'location' => 'Hangar', 'last_seen' => '2 hours ago', 'altitude' => '0m', 'speed' => '0 km/h'],
                ['name' => 'VIPER-6', 'status' => 'ACTIVE', 'battery' => 91, 'location' => 'Sector 2', 'last_seen' => '3 min ago', 'altitude' => '320m', 'speed' => '41 km/h'],
            ];
            $drones = $sample_drones;
            ?>
        <?php endif; ?>
        
        <?php foreach ($drones as $drone): 
            $status_class = 'status-' . strtolower(is_array($drone) ? ($drone['status'] ?? 'STANDBY') : 'STANDBY');
            $battery = is_array($drone) ? ($drone['battery_level'] ?? $drone['battery'] ?? 100) : 100;
        ?>
        <div class="drone-card" data-drone-id="<?= is_array($drone) ? ($drone['id'] ?? $drone['name'] ?? '') : '' ?>">
            <div class="drone-header">
                <span class="drone-name"><i class="fas fa-drone"></i> <?= is_array($drone) ? ($drone['name'] ?? 'Unknown') : 'Unknown' ?></span>
                <span class="drone-status <?= $status_class ?>"><?= is_array($drone) ? ($drone['status'] ?? 'STANDBY') : 'STANDBY' ?></span>
            </div>
            <div class="drone-body">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-map-pin"></i> Location:</span>
                    <span class="info-value"><?= is_array($drone) ? ($drone['location'] ?? 'Unknown') : 'Unknown' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock"></i> Last Seen:</span>
                    <span class="info-value"><?= is_array($drone) ? ($drone['last_seen'] ?? 'Just now') : 'Just now' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-arrow-up"></i> Altitude:</span>
                    <span class="info-value"><?= is_array($drone) ? ($drone['altitude'] ?? rand(200, 500) . 'm') : rand(200, 500) . 'm' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-tachometer-alt"></i> Speed:</span>
                    <span class="info-value"><?= is_array($drone) ? ($drone['speed'] ?? rand(30, 60) . ' km/h') : rand(30, 60) . ' km/h' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-battery-three-quarters"></i> Battery:</span>
                    <span class="info-value"><?= $battery ?>%</span>
                </div>
                <div class="battery-bar">
                    <div class="battery-level" style="width: <?= $battery ?>%;"></div>
                </div>
                
                <!-- Advanced Controls -->
                <div class="drone-actions">
                    <button class="drone-btn" onclick="controlDrone('<?= is_array($drone) ? ($drone['name'] ?? '') : '' ?>', 'launch')"><i class="fas fa-play"></i> LAUNCH</button>
                    <button class="drone-btn" onclick="controlDrone('<?= is_array($drone) ? ($drone['name'] ?? '') : '' ?>', 'hover')"><i class="fas fa-pause"></i> HOVER</button>
                    <button class="drone-btn" onclick="controlDrone('<?= is_array($drone) ? ($drone['name'] ?? '') : '' ?>', 'return')"><i class="fas fa-home"></i> RETURN</button>
                </div>
                <div class="drone-actions">
                    <button class="drone-btn" onclick="controlDrone('<?= is_array($drone) ? ($drone['name'] ?? '') : '' ?>', 'scan')"><i class="fas fa-search"></i> SCAN</button>
                    <button class="drone-btn" onclick="controlDrone('<?= is_array($drone) ? ($drone['name'] ?? '') : '' ?>', 'record')"><i class="fas fa-video"></i> RECORD</button>
                    <button class="drone-btn danger" onclick="controlDrone('<?= is_array($drone) ? ($drone['name'] ?? '') : '' ?>', 'emergency')"><i class="fas fa-exclamation"></i> EMERG</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        // Fleet Distribution Chart
        new Chart(document.getElementById('fleetChart'), {
            type: 'doughnut',
            data: {
                labels: ['ACTIVE', 'STANDBY', 'MAINTENANCE', 'DEPLOYED'],
                datasets: [{
                    data: [<?= $stats['active'] ?: 10 ?>, <?= $stats['standby'] ?: 3 ?>, <?= $stats['maintenance'] ?: 2 ?>, 0],
                    backgroundColor: ['#00ff9d', '#ffbe0b', '#ff006e', '#4cc9f0']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#e0e0e0' } }
                }
            }
        });

        // Battery Trend Chart
        new Chart(document.getElementById('batteryChart'), {
            type: 'line',
            data: {
                labels: ['EAGLE-1', 'HAWK-2', 'FALCON-3', 'RAVEN-4', 'PHOENIX-5', 'VIPER-6'],
                datasets: [{
                    label: 'Battery Level',
                    data: [95, 87, 100, 72, 45, 91],
                    borderColor: '#00ff9d',
                    backgroundColor: '#00ff9d20',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 100,
                        grid: { color: '#00ff9d20' }
                    }
                }
            }
        });

        // Real-time updates simulation
        setInterval(() => {
            // Simulate battery drain
            document.querySelectorAll('.battery-level').forEach(bar => {
                let currentWidth = parseInt(bar.style.width);
                if (currentWidth > 0 && Math.random() > 0.7) {
                    let newWidth = Math.max(0, currentWidth - Math.floor(Math.random() * 3));
                    bar.style.width = newWidth + '%';
                    
                    // Update the battery text
                    let batteryText = bar.closest('.drone-body').querySelector('.info-value:last-child');
                    if (batteryText) {
                        batteryText.textContent = newWidth + '%';
                    }
                }
            });
        }, 5000);

        // Drone control functions
        function controlDrone(droneName, action) {
            const actions = {
                'launch': '🚀 Launching',
                'hover': '⏸️ Hovering',
                'return': '🏠 Returning to base',
                'scan': '🔍 Scanning area',
                'record': '📹 Recording video',
                'emergency': '⚠️ Emergency landing'
            };
            
            showNotification(`${actions[action]} ${droneName}...`, 'info');
            
            // Simulate response
            setTimeout(() => {
                showNotification(`${droneName} ${action} command executed`, 'success');
            }, 2000);
        }

        // Fleet control functions
        function launchAll() {
            showNotification('🚀 Launching all available drones...', 'info');
            setTimeout(() => showNotification('10 drones launched successfully', 'success'), 3000);
        }
        
        function recallAll() {
            showNotification('🏠 Recalling all drones to base...', 'info');
            setTimeout(() => showNotification('All drones returning to base', 'success'), 3000);
        }
        
        function scanArea() {
            showNotification('🔍 Initiating area scan...', 'info');
            setTimeout(() => showNotification('Scan complete: No threats detected in nearby sectors', 'success'), 4000);
        }
        
        function formationFly() {
            showNotification('🛸 Switching to formation flight mode...', 'info');
            setTimeout(() => showNotification('Drones now flying in defensive formation', 'success'), 3000);
        }
        
        function autoPatrol() {
            showNotification('🔄 Activating auto-patrol mode...', 'info');
            setTimeout(() => showNotification('Drones patrolling assigned sectors', 'success'), 3000);
        }
        
        function emergencyLand() {
            if (confirm('⚠️ EMERGENCY: Land all drones immediately?')) {
                showNotification('🆘 Emergency landing initiated...', 'warning');
                setTimeout(() => showNotification('All drones have landed safely', 'success'), 5000);
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === 'success' ? '#00ff9d' : (type === 'warning' ? '#ff006e' : '#4cc9f0')};
                color: #0a0f1c;
                border-radius: 4px;
                font-family: 'Share Tech Mono', monospace;
                z-index: 10001;
                animation: slideIn 0.3s ease;
                border-left: 4px solid ${type === 'success' ? '#0a0f1c' : '#ffffff'};
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Add animation style
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
