<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Complete Tactical Map
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Interactive map with live drone tracking and node visualization
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

// Role-based accent color
$role_colors = [
    'commander' => '#ff006e',
    'operator' => '#ffbe0b',
    'analyst' => '#4cc9f0',
    'viewer' => '#a0aec0'
];
$accent = $role_colors[$role] ?? '#ff006e';

// Get real drone data from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    // Get active drones with telemetry
    $drones = $pdo->query("
        SELECT id, name, status, battery_level, altitude, speed, 
               location_lat, location_lng 
        FROM drones 
        WHERE status IN ('ACTIVE', 'STANDBY')
        ORDER BY id
    ")->fetchAll();
    
    // Get nodes (facilities, towers, etc.)
    $nodes = $pdo->query("SELECT id, name, type, status, location_lat, location_lng FROM nodes WHERE status = 'ACTIVE'")->fetchAll();
    
    // Get active threats for map overlay
    $threats = $pdo->query("SELECT id, type, severity, location_lat, location_lng FROM threats WHERE status = 'ACTIVE'")->fetchAll();
    
} catch (Exception $e) {
    // Fallback data
    $drones = [
        ['id' => 1, 'name' => 'DRONE-001', 'status' => 'ACTIVE', 'battery_level' => 95, 'altitude' => 150, 'speed' => 12, 'location_lat' => 31.57, 'location_lng' => -87.21],
        ['id' => 2, 'name' => 'DRONE-002', 'status' => 'ACTIVE', 'battery_level' => 87, 'altitude' => 200, 'speed' => 15, 'location_lat' => 31.58, 'location_lng' => -87.23],
        ['id' => 3, 'name' => 'DRONE-003', 'status' => 'STANDBY', 'battery_level' => 100, 'altitude' => 0, 'speed' => 0, 'location_lat' => 31.55, 'location_lng' => -87.19],
        ['id' => 4, 'name' => 'DRONE-004', 'status' => 'ACTIVE', 'battery_level' => 72, 'altitude' => 120, 'speed' => 10, 'location_lat' => 31.60, 'location_lng' => -87.25],
        ['id' => 5, 'name' => 'DRONE-005', 'status' => 'ACTIVE', 'battery_level' => 88, 'altitude' => 180, 'speed' => 14, 'location_lat' => 31.53, 'location_lng' => -87.18],
    ];
    
    $nodes = [
        ['id' => 1, 'name' => 'Command Center', 'type' => 'HQ', 'status' => 'ACTIVE', 'location_lat' => 31.56, 'location_lng' => -87.20],
        ['id' => 2, 'name' => 'Radar Station Alpha', 'type' => 'RADAR', 'status' => 'ACTIVE', 'location_lat' => 31.59, 'location_lng' => -87.22],
        ['id' => 3, 'name' => 'Comms Tower 1', 'type' => 'COMMS', 'status' => 'ACTIVE', 'location_lat' => 31.54, 'location_lng' => -87.17],
    ];
    
    $threats = [
        ['id' => 1, 'type' => 'Unauthorized Access', 'severity' => 'CRITICAL', 'location_lat' => 31.57, 'location_lng' => -87.21],
        ['id' => 2, 'type' => 'Drone Intrusion', 'severity' => 'HIGH', 'location_lat' => 31.58, 'location_lng' => -87.24],
    ];
}

// Calculate map center (average of all points)
$all_lats = array_merge(array_column($drones, 'location_lat'), array_column($nodes, 'location_lat'), array_column($threats, 'location_lat'));
$all_lngs = array_merge(array_column($drones, 'location_lng'), array_column($nodes, 'location_lng'), array_column($threats, 'location_lng'));
$center_lat = !empty($all_lats) ? array_sum($all_lats) / count($all_lats) : 31.56;
$center_lng = !empty($all_lngs) ? array_sum($all_lngs) / count($all_lngs) : -87.20;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - TACTICAL MAP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            padding: 15px;
            min-height: 100vh;
        }
        
        /* Header */
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
        
        .satellite-badge {
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
        
        /* Map Container */
        .map-container {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            height: 500px;
            position: relative;
            overflow: hidden;
        }
        
        #tacticalMap {
            height: 100%;
            width: 100%;
            border-radius: 8px;
            z-index: 1;
        }
        
        /* Map Controls */
        .map-controls {
            position: absolute;
            top: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .map-control-btn {
            width: 40px;
            height: 40px;
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: 0.3s;
            backdrop-filter: blur(5px);
        }
        
        .map-control-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
            transform: scale(1.1);
        }
        
        /* Stats Panel */
        .stats-panel {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.5rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        
        .stat-label {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        /* Legend */
        .legend {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 10px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }
        
        .legend-color.drone { background: <?= $accent ?>; box-shadow: 0 0 10px <?= $accent ?>; }
        .legend-color.node { background: #00ff9d; box-shadow: 0 0 10px #00ff9d; }
        .legend-color.threat { background: #ff006e; box-shadow: 0 0 10px #ff006e; }
        
        /* Drone List Panel */
        .drone-list-panel {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 15px;
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .drone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 12px;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .drone-list-item {
            background: #0a0f1c;
            border: 1px solid <?= $accent ?>40;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .drone-list-item:hover {
            border-color: <?= $accent ?>;
            transform: translateX(5px);
            background: <?= $accent ?>10;
        }
        
        .drone-list-icon {
            width: 40px;
            height: 40px;
            background: <?= $accent ?>20;
            border: 1px solid <?= $accent ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: <?= $accent ?>;
        }
        
        .drone-list-info {
            flex: 1;
        }
        
        .drone-list-name {
            font-size: 0.9rem;
            color: <?= $accent ?>;
        }
        
        .drone-list-status {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        .drone-list-battery {
            width: 50px;
            height: 4px;
            background: #151f2c;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 4px;
        }
        
        .drone-list-battery-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $accent ?>, #00ff9d);
        }
        
        .refresh-btn {
            padding: 6px 15px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        
        .refresh-btn:hover {
            background: #00ff9d;
            transform: translateY(-2px);
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .map-controls {
                top: 15px;
                right: 15px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .legend {
                gap: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .map-container {
                height: 350px;
            }
        }
        
        /* Custom Leaflet Styles */
        .leaflet-container {
            background: #0a0f1c;
        }
        
        .leaflet-control-attribution {
            background: rgba(21,31,44,0.8);
            color: #a0aec0;
            font-size: 0.6rem;
        }
        
        .leaflet-control-attribution a {
            color: <?= $accent ?>;
        }
        
        .leaflet-popup-content-wrapper {
            background: #151f2c;
            color: #e0e0e0;
            border: 2px solid <?= $accent ?>;
            border-radius: 8px;
            font-family: 'Share Tech Mono', monospace;
        }
        
        .leaflet-popup-tip {
            background: <?= $accent ?>;
        }
        
        .leaflet-popup-close-button {
            color: <?= $accent ?> !important;
        }
        
        .custom-popup .drone-popup {
            min-width: 200px;
        }
        
        .custom-popup h3 {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 8px;
        }
        
        .custom-popup p {
            margin: 4px 0;
            font-size: 0.8rem;
        }
        
        .custom-popup .battery-bar {
            height: 4px;
            background: #0a0f1c;
            border-radius: 2px;
            margin: 8px 0;
        }
        
        .custom-popup .battery-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $accent ?>, #00ff9d);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-map-marked-alt"></i>
            <h1>TACTICAL MAP</h1>
            <span class="satellite-badge">SATELLITE VIEW</span>
        </div>
        <div class="user-info">
            <span class="user-badge">
                <i class="fas fa-user"></i> <?= htmlspecialchars($full_name) ?>
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <!-- Map Container -->
    <div class="map-container">
        <div id="tacticalMap"></div>
        
        <!-- Map Controls -->
        <div class="map-controls">
            <button class="map-control-btn" onclick="centerMap()" title="Center Map">
                <i class="fas fa-crosshairs"></i>
            </button>
            <button class="map-control-btn" onclick="toggleSatellite()" title="Toggle Satellite">
                <i class="fas fa-satellite"></i>
            </button>
            <button class="map-control-btn" onclick="zoomIn()" title="Zoom In">
                <i class="fas fa-plus"></i>
            </button>
            <button class="map-control-btn" onclick="zoomOut()" title="Zoom Out">
                <i class="fas fa-minus"></i>
            </button>
            <button class="map-control-btn" onclick="locateMe()" title="My Location">
                <i class="fas fa-location-dot"></i>
            </button>
        </div>
    </div>

    <!-- Stats Panel -->
    <div class="stats-panel">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?= count($drones) ?></div>
                <div class="stat-label">ACTIVE DRONES</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= count($nodes) ?></div>
                <div class="stat-label">FACILITIES</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" style="color: #ff006e;"><?= count($threats) ?></div>
                <div class="stat-label">THREATS</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="liveTime"><?= date('H:i:s') ?></div>
                <div class="stat-label">LIVE UPDATE</div>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color drone"></div>
                <span>Active Drone</span>
            </div>
            <div class="legend-item">
                <div class="legend-color node"></div>
                <span>Facility</span>
            </div>
            <div class="legend-item">
                <div class="legend-color threat"></div>
                <span>Threat</span>
            </div>
        </div>
    </div>

    <!-- Drone List Panel -->
    <div class="drone-list-panel">
        <div class="panel-header">
            <span><i class="fas fa-drone"></i> DRONE FLEET (<?= count($drones) ?>)</span>
            <button class="refresh-btn" onclick="refreshDroneData()">
                <i class="fas fa-sync-alt"></i> REFRESH
            </button>
        </div>
        
        <div class="drone-grid" id="droneList">
            <?php foreach ($drones as $drone): ?>
            <div class="drone-list-item" onclick="focusOnDrone(<?= $drone['location_lat'] ?? 31.56 ?>, <?= $drone['location_lng'] ?? -87.20 ?>)">
                <div class="drone-list-icon">
                    <i class="fas fa-drone"></i>
                </div>
                <div class="drone-list-info">
                    <div class="drone-list-name"><?= htmlspecialchars($drone['name']) ?></div>
                    <div class="drone-list-status">
                        ALT: <?= $drone['altitude'] ?? 0 ?>m | SPD: <?= $drone['speed'] ?? 0 ?>m/s
                    </div>
                    <div class="drone-list-battery">
                        <div class="drone-list-battery-fill" style="width: <?= $drone['battery_level'] ?? 100 ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Initialize map
        let map;
        let droneMarkers = [];
        let nodeMarkers = [];
        let threatMarkers = [];
        let currentLayer = 'standard';

        function initMap() {
            // Create map with custom style
            map = L.map('tacticalMap').setView([<?= $center_lat ?>, <?= $center_lng ?>], 12);
            
            // Add dark tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>, &copy; CartoDB',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);
            
            // Add drone markers
            <?php foreach ($drones as $drone): ?>
            (function() {
                let droneIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: <?= $accent ?>; width: 16px; height: 16px; border-radius: 50%; border: 2px solid #0a0f1c; box-shadow: 0 0 15px <?= $accent ?>; animation: pulse 2s infinite;"></div>' +
                          '<div style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); background: #151f2c; border: 1px solid <?= $accent ?>; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; white-space: nowrap; color: <?= $accent ?>;"><?= $drone['name'] ?></div>',
                    iconSize: [16, 16],
                    popupAnchor: [0, -20]
                });
                
                let marker = L.marker([<?= $drone['location_lat'] ?? 31.56 ?>, <?= $drone['location_lng'] ?? -87.20 ?>], {
                    icon: droneIcon,
                    zIndexOffset: 1000
                }).addTo(map);
                
                marker.bindPopup(`
                    <div class="custom-popup">
                        <h3><i class="fas fa-drone"></i> ${'<?= $drone['name'] ?>'}</h3>
                        <p><i class="fas fa-bolt"></i> Battery: ${<?= $drone['battery_level'] ?? 100 ?>}%</p>
                        <div class="battery-bar"><div class="battery-fill" style="width: ${<?= $drone['battery_level'] ?? 100 ?>}%"></div></div>
                        <p><i class="fas fa-arrow-up"></i> Altitude: ${<?= $drone['altitude'] ?? 0 ?>}m</p>
                        <p><i class="fas fa-tachometer-alt"></i> Speed: ${<?= $drone['speed'] ?? 0 ?>}m/s</p>
                        <p><i class="fas fa-microchip"></i> Status: ${'<?= $drone['status'] ?>'}</p>
                        <button onclick="controlDrone(${<?= $drone['id'] ?>})" style="background: <?= $accent ?>; color: #0a0f1c; border: none; padding: 5px 10px; border-radius: 4px; margin-top: 8px; cursor: pointer; width: 100%;">
                            <i class="fas fa-gamepad"></i> CONTROL
                        </button>
                    </div>
                `);
                
                droneMarkers.push(marker);
            })();
            <?php endforeach; ?>
            
            // Add node markers
            <?php foreach ($nodes as $node): ?>
            (function() {
                let nodeIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #00ff9d; width: 12px; height: 12px; border-radius: 2px; border: 2px solid #0a0f1c; box-shadow: 0 0 10px #00ff9d;"></div>' +
                          '<div style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); background: #151f2c; border: 1px solid #00ff9d; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; white-space: nowrap; color: #00ff9d;"><?= $node['name'] ?></div>',
                    iconSize: [12, 12],
                    popupAnchor: [0, -20]
                });
                
                let marker = L.marker([<?= $node['location_lat'] ?? 31.56 ?>, <?= $node['location_lng'] ?? -87.20 ?>], {
                    icon: nodeIcon
                }).addTo(map);
                
                marker.bindPopup(`
                    <div class="custom-popup">
                        <h3 style="color: #00ff9d;"><i class="fas fa-building"></i> ${'<?= $node['name'] ?>'}</h3>
                        <p><i class="fas fa-tag"></i> Type: ${'<?= $node['type'] ?>'}</p>
                        <p><i class="fas fa-microchip"></i> Status: ${'<?= $node['status'] ?>'}</p>
                    </div>
                `);
                
                nodeMarkers.push(marker);
            })();
            <?php endforeach; ?>
            
            // Add threat markers
            <?php foreach ($threats as $threat): 
                $severityColor = '#ff006e';
                if ($threat['severity'] == 'HIGH') $severityColor = '#ff8c00';
                elseif ($threat['severity'] == 'MEDIUM') $severityColor = '#ffbe0b';
                elseif ($threat['severity'] == 'LOW') $severityColor = '#00ff9d';
            ?>
            (function() {
                let threatIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: <?= $severityColor ?>; width: 20px; height: 20px; clip-path: polygon(50% 0%, 0% 100%, 100% 100%); border: 2px solid #0a0f1c; box-shadow: 0 0 20px <?= $severityColor ?>; animation: pulse 1s infinite;"></div>' +
                          '<div style="position: absolute; top: 25px; left: 50%; transform: translateX(-50%); background: #151f2c; border: 1px solid <?= $severityColor ?>; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; white-space: nowrap; color: <?= $severityColor ?>;">THREAT</div>',
                    iconSize: [20, 20],
                    popupAnchor: [0, -25]
                });
                
                let marker = L.marker([<?= $threat['location_lat'] ?? 31.56 ?>, <?= $threat['location_lng'] ?? -87.20 ?>], {
                    icon: threatIcon,
                    zIndexOffset: 2000
                }).addTo(map);
                
                marker.bindPopup(`
                    <div class="custom-popup">
                        <h3 style="color: <?= $severityColor ?>;"><i class="fas fa-exclamation-triangle"></i> THREAT</h3>
                        <p><i class="fas fa-tag"></i> Type: ${'<?= $threat['type'] ?>'}</p>
                        <p><i class="fas fa-exclamation-circle"></i> Severity: <span style="color: <?= $severityColor ?>;">${'<?= $threat['severity'] ?>'}</span></p>
                        <button onclick="respondToThreat(${<?= $threat['id'] ?>})" style="background: <?= $severityColor ?>; color: #0a0f1c; border: none; padding: 5px 10px; border-radius: 4px; margin-top: 8px; cursor: pointer; width: 100%;">
                            <i class="fas fa-shield-alt"></i> RESPOND
                        </button>
                    </div>
                `);
                
                threatMarkers.push(marker);
            })();
            <?php endforeach; ?>
        }

        // Map control functions
        function centerMap() {
            map.setView([<?= $center_lat ?>, <?= $center_lng ?>], 12);
            showNotification('Map centered');
        }

        function toggleSatellite() {
            if (currentLayer === 'standard') {
                map.eachLayer((layer) => {
                    if (layer instanceof L.TileLayer) {
                        map.removeLayer(layer);
                    }
                });
                
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19
                }).addTo(map);
                
                currentLayer = 'satellite';
                showNotification('Satellite view enabled');
            } else {
                map.eachLayer((layer) => {
                    if (layer instanceof L.TileLayer) {
                        map.removeLayer(layer);
                    }
                });
                
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap, CartoDB',
                    subdomains: 'abcd',
                    maxZoom: 19
                }).addTo(map);
                
                currentLayer = 'standard';
                showNotification('Standard view enabled');
            }
        }

        function zoomIn() {
            map.zoomIn();
        }

        function zoomOut() {
            map.zoomOut();
        }

        function locateMe() {
            map.locate({setView: true, maxZoom: 15});
            showNotification('Locating you...');
        }

        function focusOnDrone(lat, lng) {
            map.setView([lat, lng], 15);
            showNotification('Focusing on drone');
        }

        function controlDrone(id) {
            showNotification(`🎮 Opening drone control for ID: ${id}`);
            setTimeout(() => {
                window.location.href = '?module=drone-control';
            }, 1000);
        }

        function respondToThreat(id) {
            showNotification(`🚨 Dispatching response unit to threat #${id}`);
        }

        function refreshDroneData() {
            showNotification('🔄 Updating drone telemetry...');
            
            // Simulate data refresh
            setTimeout(() => {
                // Randomly update battery levels
                document.querySelectorAll('.drone-list-battery-fill').forEach(fill => {
                    let newBattery = Math.floor(Math.random() * 30) + 70;
                    fill.style.width = newBattery + '%';
                });
                
                // Update drone markers (simulate movement)
                droneMarkers.forEach((marker, index) => {
                    let lat = marker.getLatLng().lat + (Math.random() - 0.5) * 0.01;
                    let lng = marker.getLatLng().lng + (Math.random() - 0.5) * 0.01;
                    marker.setLatLng([lat, lng]);
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

        // Update time
        function updateTime() {
            document.getElementById('liveTime').textContent = new Date().toLocaleTimeString();
        }
        setInterval(updateTime, 1000);

        // Simulate drone movement
        setInterval(() => {
            droneMarkers.forEach((marker, index) => {
                if (Math.random() > 0.7) {
                    let lat = marker.getLatLng().lat + (Math.random() - 0.5) * 0.005;
                    let lng = marker.getLatLng().lng + (Math.random() - 0.5) * 0.005;
                    marker.setLatLng([lat, lng]);
                }
            });
        }, 3000);

        // Initialize map when page loads
        window.addEventListener('load', initMap);

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === 'c') centerMap();
            if (e.altKey && e.key === 's') toggleSatellite();
            if (e.altKey && e.key === 'l') locateMe();
        });

        // Add custom CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(1.2); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
