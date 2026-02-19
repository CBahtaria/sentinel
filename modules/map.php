<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Military Intelligence Map
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - MILITARY MAP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: #151f2c;
            border-bottom: 2px solid #4cc9f0;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #4cc9f0;
        }
        .back-btn {
            padding: 8px 15px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 4px;
        }
        #map {
            flex: 1;
            width: 100%;
            z-index: 1;
        }
        .map-overlay {
            position: absolute;
            top: 100px;
            right: 20px;
            background: #151f2c;
            border: 2px solid #4cc9f0;
            padding: 15px;
            border-radius: 8px;
            z-index: 1000;
            min-width: 200px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
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
            z-index: 2000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-map"></i> MILITARY INTELLIGENCE MAP</h1>
        <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
    </div>

    <div id="map"></div>

    <div class="map-overlay">
        <h3 style="color: #4cc9f0; margin-bottom: 10px;">LEGEND</h3>
        <div class="legend-item">
            <div class="legend-color" style="background: #00ff9d;"></div>
            <span>Active Drones</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #ff006e;"></div>
            <span>Threats</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #4cc9f0;"></div>
            <span>Military Nodes</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #ffbe0b;"></div>
            <span>Patrol Routes</span>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map centered on Eswatini
        const map = L.map('map').setView([-26.5, 31.5], 9);
        
        // Add dark map tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; UEDF Sentinel'
        }).addTo(map);

        // Add drone markers
        const drones = [
            { pos: [-26.2, 31.8], name: 'EAGLE-1', status: 'active' },
            { pos: [-26.8, 31.3], name: 'HAWK-2', status: 'active' },
            { pos: [-26.4, 31.9], name: 'FALCON-3', status: 'standby' }
        ];

        drones.forEach(drone => {
            const marker = L.circleMarker(drone.pos, {
                radius: 8,
                color: drone.status === 'active' ? '#00ff9d' : '#ffbe0b',
                fillColor: drone.status === 'active' ? '#00ff9d' : '#ffbe0b',
                fillOpacity: 0.8
            }).addTo(map);
            
            marker.bindPopup(`<b>${drone.name}</b><br>Status: ${drone.status}`);
        });

        // Add threat markers
        const threats = [
            { pos: [-26.1, 31.9], severity: 'critical' },
            { pos: [-26.9, 31.2], severity: 'high' }
        ];

        threats.forEach(threat => {
            const marker = L.circleMarker(threat.pos, {
                radius: 10,
                color: '#ff006e',
                fillColor: '#ff006e',
                fillOpacity: 0.6
            }).addTo(map);
            
            marker.bindPopup(`<b>Threat Detected</b><br>Severity: ${threat.severity}`);
        });

        // Add military nodes
        const nodes = [
            { pos: [-26.32, 31.13], name: 'NORTHERN COMMAND' },
            { pos: [-26.48, 31.37], name: 'CENTRAL BASE' },
            { pos: [-26.85, 31.95], name: 'SOUTHERN OUTPOST' }
        ];

        nodes.forEach(node => {
            const marker = L.marker(node.pos).addTo(map);
            marker.bindPopup(`<b>${node.name}</b>`);
        });
    </script>
</body>
</html>
