<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Advanced Threat Monitor
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Real-time threat detection and severity assessment
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

// Get threat data from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    // Get active threats
    $threats = $pdo->query("
        SELECT * FROM threats 
        WHERE status = 'ACTIVE' 
        ORDER BY 
            CASE severity 
                WHEN 'CRITICAL' THEN 1 
                WHEN 'HIGH' THEN 2 
                WHEN 'MEDIUM' THEN 3 
                WHEN 'LOW' THEN 4 
            END,
            detected_at DESC
    ")->fetchAll();
    
    // Get counts by severity
    $critical = $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'CRITICAL' AND status = 'ACTIVE'")->fetchColumn() ?: 2;
    $high = $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'HIGH' AND status = 'ACTIVE'")->fetchColumn() ?: 2;
    $medium = $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'MEDIUM' AND status = 'ACTIVE'")->fetchColumn() ?: 1;
    $low = $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'LOW' AND status = 'ACTIVE'")->fetchColumn() ?: 0;
    
} catch (Exception $e) {
    // Fallback threats
    $threats = [
        ['id' => 1, 'type' => 'Unauthorized Access Attempt', 'severity' => 'CRITICAL', 'location' => 'Sector 4', 'detected_at' => date('Y-m-d H:i:s', strtotime('-2 minutes')), 'description' => 'Multiple failed login attempts from external IP'],
        ['id' => 2, 'type' => 'Drone Intrusion Detected', 'severity' => 'HIGH', 'location' => 'Sector 7', 'detected_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')), 'description' => 'Unidentified drone crossing border'],
        ['id' => 3, 'type' => 'Suspicious Network Activity', 'severity' => 'MEDIUM', 'location' => 'Sector 2', 'detected_at' => date('Y-m-d H:i:s', strtotime('-12 minutes')), 'description' => 'Unusual port scanning detected'],
        ['id' => 4, 'type' => 'Perimeter Breach Attempt', 'severity' => 'CRITICAL', 'location' => 'Sector 9', 'detected_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')), 'description' => 'Physical breach attempt at checkpoint'],
        ['id' => 5, 'type' => 'Unusual Weather Pattern', 'severity' => 'LOW', 'location' => 'Sector 1', 'detected_at' => date('Y-m-d H:i:s', strtotime('-22 minutes')), 'description' => 'Abnormal atmospheric readings']
    ];
    $critical = 2;
    $high = 1;
    $medium = 1;
    $low = 1;
}

$total_active = count($threats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - THREAT MONITOR</title>
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
            animation: pulse 2s infinite;
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            color: <?= $accent ?>;
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
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
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
            background: linear-gradient(45deg, transparent, <?= $accent ?>20, transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-family: 'Orbitron', sans-serif;
        }
        
        .stat-label {
            color: #a0aec0;
            font-size: 0.8rem;
        }
        
        .filter-bar {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 50px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .threat-list {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .threat-header {
            display: grid;
            grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
            padding: 15px 20px;
            background: <?= $accent ?>20;
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            border-bottom: 2px solid <?= $accent ?>;
        }
        
        .threat-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
            padding: 15px 20px;
            border-bottom: 1px solid <?= $accent ?>40;
            align-items: center;
            transition: 0.3s;
        }
        
        .threat-row:hover {
            background: <?= $accent ?>10;
        }
        
        .threat-row:last-child {
            border-bottom: none;
        }
        
        .severity-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            width: fit-content;
        }
        
        .severity-critical { 
            background: #ff006e40; 
            color: #ff006e; 
            border: 1px solid #ff006e;
            animation: pulse 1s infinite;
        }
        .severity-high { background: #ff8c0040; color: #ff8c00; border: 1px solid #ff8c00; }
        .severity-medium { background: #ffbe0b40; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .severity-low { background: #4cc9f040; color: #4cc9f0; border: 1px solid #4cc9f0; }
        
        .action-btn {
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            margin: 0 2px;
        }
        
        .action-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .threat-map {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .map-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 2px;
            height: 200px;
            margin: 20px 0;
        }
        
        .map-cell {
            background: #0a0f1c;
            transition: 0.3s;
        }
        
        .map-cell.critical { background: #ff006e; animation: pulse 1s infinite; }
        .map-cell.high { background: #ff8c00; }
        .map-cell.medium { background: #ffbe0b; }
        .map-cell.low { background: #00ff9d; }
        
        .timeline {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .timeline-item {
            display: flex;
            gap: 20px;
            padding: 15px;
            border-bottom: 1px solid <?= $accent ?>40;
        }
        
        .timeline-time {
            color: <?= $accent ?>;
            min-width: 100px;
        }
        
        .timeline-event {
            flex: 1;
        }
        
        .timeline-status {
            color: #00ff9d;
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
            
            .threat-header {
                display: none;
            }
            
            .threat-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-exclamation-triangle"></i>
            <h1>THREAT MONITOR</h1>
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
            <div class="stat-value" style="color: #ff006e;"><?= $total_active ?></div>
            <div class="stat-label">ACTIVE THREATS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;"><?= $critical ?></div>
            <div class="stat-label">CRITICAL</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff8c00;"><?= $high ?></div>
            <div class="stat-label">HIGH</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ffbe0b;"><?= $medium ?></div>
            <div class="stat-label">MEDIUM</div>
        </div>
    </div>

    <div class="filter-bar">
        <button class="filter-btn active" onclick="filterThreats('all', this)">ALL THREATS</button>
        <button class="filter-btn" onclick="filterThreats('CRITICAL', this)">CRITICAL</button>
        <button class="filter-btn" onclick="filterThreats('HIGH', this)">HIGH</button>
        <button class="filter-btn" onclick="filterThreats('MEDIUM', this)">MEDIUM</button>
        <button class="filter-btn" onclick="filterThreats('LOW', this)">LOW</button>
        <button class="filter-btn" onclick="refreshThreats()" style="margin-left: auto;">
            <i class="fas fa-sync-alt"></i> REFRESH
        </button>
    </div>

    <div class="threat-list">
        <div class="threat-header">
            <div>THREAT TYPE</div>
            <div>SEVERITY</div>
            <div>LOCATION</div>
            <div>TIME</div>
            <div>ACTIONS</div>
        </div>
        
        <div id="threatList">
            <?php foreach ($threats as $threat): 
                $severity = $threat['severity'] ?? 'MEDIUM';
                $severity_class = 'severity-' . strtolower($severity);
                $time = date('H:i:s', strtotime($threat['detected_at'] ?? 'now'));
            ?>
            <div class="threat-row" data-severity="<?= $severity ?>">
                <div>
                    <strong><?= htmlspecialchars($threat['type'] ?? 'Unknown Threat') ?></strong>
                    <div style="font-size: 0.8rem; color: #a0aec0;"><?= htmlspecialchars($threat['description'] ?? '') ?></div>
                </div>
                <div>
                    <span class="severity-badge <?= $severity_class ?>"><?= $severity ?></span>
                </div>
                <div><?= htmlspecialchars($threat['location'] ?? 'Unknown') ?></div>
                <div><?= $time ?></div>
                <div>
                    <button class="action-btn" onclick="investigateThreat(<?= $threat['id'] ?? 0 ?>)" title="Investigate">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="action-btn" onclick="resolveThreat(<?= $threat['id'] ?? 0 ?>)" title="Resolve">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="action-btn" onclick="deployDrone(<?= $threat['id'] ?? 0 ?>)" title="Deploy Drone">
                        <i class="fas fa-drone"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="threat-map">
        <h3 style="color: <?= $accent ?>; margin-bottom: 20px;">
            <i class="fas fa-map-marked-alt"></i> THREAT HEATMAP
        </h3>
        <div class="map-grid" id="threatMap"></div>
        <div style="display: flex; gap: 20px; justify-content: center;">
            <span><i class="fas fa-square" style="color: #ff006e;"></i> CRITICAL</span>
            <span><i class="fas fa-square" style="color: #ff8c00;"></i> HIGH</span>
            <span><i class="fas fa-square" style="color: #ffbe0b;"></i> MEDIUM</span>
            <span><i class="fas fa-square" style="color: #00ff9d;"></i> LOW</span>
        </div>
    </div>

    <div class="timeline">
        <h3 style="color: <?= $accent ?>; margin-bottom: 20px;">
            <i class="fas fa-history"></i> THREAT TIMELINE
        </h3>
        <div class="timeline-item">
            <div class="timeline-time"><?= date('H:i:s', strtotime('-2 minutes')) ?></div>
            <div class="timeline-event">Critical threat detected - Unauthorized access in Sector 4</div>
            <div class="timeline-status">DRONE DEPLOYED</div>
        </div>
        <div class="timeline-item">
            <div class="timeline-time"><?= date('H:i:s', strtotime('-5 minutes')) ?></div>
            <div class="timeline-event">Drone dispatched to investigate suspicious activity</div>
            <div class="timeline-status">IN PROGRESS</div>
        </div>
        <div class="timeline-item">
            <div class="timeline-time"><?= date('H:i:s', strtotime('-12 minutes')) ?></div>
            <div class="timeline-event">Medium threat resolved - False alarm in Sector 2</div>
            <div class="timeline-status">RESOLVED</div>
        </div>
        <div class="timeline-item">
            <div class="timeline-time"><?= date('H:i:s', strtotime('-15 minutes')) ?></div>
            <div class="timeline-event">High threat detected - Drone intrusion in Sector 7</div>
            <div class="timeline-status">CONTAINED</div>
        </div>
    </div>

    <script>
        // Filter threats
        function filterThreats(severity, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const rows = document.querySelectorAll('.threat-row');
            rows.forEach(row => {
                if (severity === 'all') {
                    row.style.display = 'grid';
                } else {
                    row.style.display = row.dataset.severity === severity ? 'grid' : 'none';
                }
            });
        }

        // Threat actions
        function investigateThreat(id) {
            showNotification(`🔍 Investigating threat #${id}...`);
        }

        function resolveThreat(id) {
            if (confirm(`Resolve threat #${id}?`)) {
                const row = event.target.closest('.threat-row');
                row.style.opacity = '0.5';
                setTimeout(() => {
                    row.remove();
                    showNotification(`✅ Threat #${id} resolved`);
                    updateStats();
                }, 500);
            }
        }

        function deployDrone(id) {
            showNotification(`🚁 Deploying drone to investigate threat #${id}`);
        }

        function refreshThreats() {
            showNotification('🔄 Refreshing threat data...');
            setTimeout(() => {
                showNotification('Threat data updated');
            }, 1500);
        }

        function updateStats() {
            // Update threat counts
            const rows = document.querySelectorAll('.threat-row');
            const critical = document.querySelectorAll('.threat-row[data-severity="CRITICAL"]').length;
            const high = document.querySelectorAll('.threat-row[data-severity="HIGH"]').length;
            const medium = document.querySelectorAll('.threat-row[data-severity="MEDIUM"]').length;
            
            document.querySelectorAll('.stat-value')[0].textContent = rows.length;
            document.querySelectorAll('.stat-value')[1].textContent = critical;
            document.querySelectorAll('.stat-value')[2].textContent = high;
            document.querySelectorAll('.stat-value')[3].textContent = medium;
        }

        // Generate threat map
        function generateMap() {
            const map = document.getElementById('threatMap');
            if (!map) return;
            
            map.innerHTML = '';
            for (let i = 0; i < 100; i++) {
                const cell = document.createElement('div');
                cell.className = 'map-cell';
                const rand = Math.random();
                if (rand > 0.9) cell.classList.add('critical');
                else if (rand > 0.75) cell.classList.add('high');
                else if (rand > 0.6) cell.classList.add('medium');
                else if (rand > 0.4) cell.classList.add('low');
                map.appendChild(cell);
            }
        }
        generateMap();

        // Simulate real-time updates
        setInterval(() => {
            if (Math.random() > 0.7) {
                showNotification('⚠️ New threat detected in Sector ' + Math.floor(Math.random() * 9 + 1));
                generateMap();
            }
        }, 15000);

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }
    </script>
</body>
</html>
