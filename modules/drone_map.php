<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL - Real-time Drone Tracking Map
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
    <title>UEDF SENTINEL - Drone Tracking</title>
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
            border-bottom: 2px solid #00ff9d;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
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
        .controls-panel {
            position: absolute;
            top: 100px;
            right: 20px;
            background: #151f2c;
            border: 2px solid #00ff9d;
            border-radius: 8px;
            padding: 15px;
            z-index: 1000;
            width: 280px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .panel-title {
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
            border-bottom: 1px solid #00ff9d;
            padding-bottom: 5px;
        }
        .drone-list {
            margin-bottom: 15px;
        }
        .drone-item {
            background: #0a0f1c;
            border: 1px solid #ff006e;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .drone-item:hover {
            border-color: #00ff9d;
            transform: translateX(-5px);
        }
        .drone-name {
            color: #00ff9d;
            font-weight: bold;
        }
        .drone-status {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
        }
        .status-active { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .status-standby { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .status-maintenance { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        .drone-detail {
            font-size: 0.8rem;
            color: #a0aec0;
            margin-top: 5px;
        }
        .battery-bar {
            width: 100%;
            height: 4px;
            background: #0a0f1c;
            border-radius: 2px;
            margin-top: 5px;
        }
        .battery-level {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #00ff9d);
            border-radius: 2px;
        }
        .stats-box {
            background: #0a0f1c;
            border: 1px solid #00ff9d;
            border-radius: 4px;
            padding: 10px;
            margin-top: 15px;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .refresh-btn {
            width: 100%;
            padding: 10px;
            background: #ff006e;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
            font-family: 'Orbitron', sans-serif;
        }
        .refresh-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
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
        .live-badge {
            background: #ff006e;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-satellite-dish"></i> REAL-TIME DRONE TRACKING</h1>
        <div>
            <span class="live-badge"><i class="fas fa-circle"></i> LIVE</span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div id="map"></div>

    <div class="controls-panel">
        <div class="panel-title"><i class="fas fa-drone"></i> ACTIVE DRONES</div>
        <div class="drone-list" id="droneList">
            <div style="text-align: center; color: #a0aec0;">Loading drones...</div>
        </div>
        
        <div class="stats-box">
            <div class="stat-row">
                <span>FLEET STATUS:</span>
                <span id="fleetStatus" style="color: #00ff9d;">-</span>
            </div>
            <div class="stat-row">
                <span>ACTIVE:</span>
                <span id="activeCount" style="color: #00ff9d;">-</span>
            </div>
            <div class="stat-row">
                <span>AVG BATTERY:</span>
                <span id="avgBattery" style="color: #00ff9d;">-</span>
            </div>
            <div class="stat-row">
                <span>LAST UPDATE:</span>
                <span id="lastUpdate" style="color: #00ff9d;">-</span>
            </div>
        </div>
        
        <button class="refresh-btn" onclick="refreshData()">
            <i class="fas fa-sync-alt"></i> REFRESH DATA
        </button>
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

        // Store markers
        let markers = {};
        let droneData = [];

        // Load drone data
        function loadDroneData() {
            fetch('/sentinel/api/drone_tracking.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        droneData = data.data;
                        updateMap();
                        updatePanel();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Update map markers
        function updateMap() {
            // Clear existing markers
            Object.values(markers).forEach(marker => map.removeLayer(marker));
            markers = {};

            droneData.forEach(drone => {
                if (drone.latitude && drone.longitude) {
                    // Choose icon color based on status
                    const iconColor = drone.status === 'ACTIVE' ? '#00ff9d' : 
                                     (drone.status === 'STANDBY' ? '#ffbe0b' : '#ff006e');
                    
                    const icon = L.divIcon({
                        html: `<i class="fas fa-drone" style="color: ${iconColor}; font-size: 24px; filter: drop-shadow(0 0 10px ${iconColor});"></i>`,
                        className: 'drone-marker',
                        iconSize: [24, 24]
                    });

                    const marker = L.marker([drone.latitude, drone.longitude], { icon }).addTo(map);
                    
                    // Popup content
                    marker.bindPopup(`
                        <div style="font-family: 'Share Tech Mono'; background: #151f2c; color: #e0e0e0; padding: 10px;">
                            <h3 style="color: #00ff9d; margin: 0 0 10px 0;">${drone.name}</h3>
                            <p><strong>Status:</strong> <span style="color: ${iconColor};">${drone.status}</span></p>
                            <p><strong>Battery:</strong> ${drone.battery_level}%</p>
                            <p><strong>Location:</strong> ${drone.location}</p>
                            <p><strong>Altitude:</strong> ${drone.altitude}</p>
                            <p><strong>Speed:</strong> ${drone.speed}</p>
                            <p><strong>Heading:</strong> ${drone.heading}</p>
                            <p><strong>Last Seen:</strong> ${drone.last_seen}</p>
                        </div>
                    `);

                    markers[drone.id] = marker;
                }
            });
        }

        // Update control panel
        function updatePanel() {
            const list = document.getElementById('droneList');
            const activeCount = droneData.filter(d => d.status === 'ACTIVE').length;
            const avgBattery = Math.round(droneData.reduce((sum, d) => sum + d.battery_level, 0) / droneData.length);
            
            // Update stats
            document.getElementById('fleetStatus').textContent = `${droneData.length} TOTAL`;
            document.getElementById('activeCount').textContent = activeCount;
            document.getElementById('avgBattery').textContent = avgBattery + '%';
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();

            // Update drone list
            list.innerHTML = droneData.map(drone => {
                const statusClass = 'status-' + drone.status.toLowerCase();
                const batteryColor = drone.battery_level > 60 ? '#00ff9d' : 
                                    (drone.battery_level > 30 ? '#ffbe0b' : '#ff006e');
                
                return `
                    <div class="drone-item" onclick="flyToDrone(${drone.id})">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="drone-name">${drone.name}</span>
                            <span class="drone-status ${statusClass}">${drone.status}</span>
                        </div>
                        <div class="drone-detail">
                            <i class="fas fa-map-pin"></i> ${drone.location}<br>
                            <i class="fas fa-battery-three-quarters" style="color: ${batteryColor}"></i> ${drone.battery_level}%
                        </div>
                        <div class="battery-bar">
                            <div class="battery-level" style="width: ${drone.battery_level}%; background: ${batteryColor};"></div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Fly to specific drone
        function flyToDrone(id) {
            if (markers[id]) {
                map.flyTo(markers[id].getLatLng(), 12);
                markers[id].openPopup();
            }
        }

        // Refresh data
        function refreshData() {
            loadDroneData();
        }

        // Auto-refresh every 10 seconds
        setInterval(loadDroneData, 10000);

        // Initial load
        loadDroneData();
    </script>
</body>
</html>
