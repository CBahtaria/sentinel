<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL - Mission Planning System
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

require_once '../config/database.php';
$db = Database::getInstance()->getConnection();

// Get available drones
$drones = $db->query("SELECT id, name, status FROM drones ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get existing missions
$missions = $db->query("
    SELECT m.*, d.name as drone_name,
    (SELECT COUNT(*) FROM mission_logs WHERE mission_id = m.id) as log_count
    FROM missions m
    LEFT JOIN drones d ON m.assigned_drone_id = d.id
    ORDER BY m.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - MISSION PLANNER</title>
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
            padding: 20px;
        }
        .header {
            background: #151f2c;
            border: 2px solid #4cc9f0;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
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
        .mission-container {
            display: grid;
            grid-template-columns: 350px 1fr 300px;
            gap: 20px;
            flex: 1;
            min-height: 0;
        }
        .panel {
            background: #151f2c;
            border: 1px solid #4cc9f0;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .panel-header {
            padding: 15px;
            background: #4cc9f020;
            border-bottom: 1px solid #4cc9f0;
            font-family: 'Orbitron', sans-serif;
            color: #4cc9f0;
        }
        .panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        #map {
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            color: #4cc9f0;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            background: #0a0f1c;
            border: 1px solid #4cc9f0;
            color: #00ff9d;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            background: #4cc9f0;
            border: none;
            color: #0a0f1c;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            width: 100%;
            margin-bottom: 10px;
        }
        .btn:hover {
            background: #00ff9d;
        }
        .btn-danger {
            background: #ff006e;
            color: white;
        }
        .waypoints-list {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        .waypoint-item {
            background: #0a0f1c;
            border: 1px solid #4cc9f0;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
        }
        .waypoint-item:hover {
            border-color: #00ff9d;
        }
        .mission-item {
            background: #0a0f1c;
            border: 1px solid #4cc9f0;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 4px;
            cursor: pointer;
        }
        .mission-item:hover {
            border-color: #00ff9d;
        }
        .mission-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
        }
        .status-draft { background: #4a5568; color: white; }
        .status-scheduled { background: #ffbe0b; color: black; }
        .status-active { background: #00ff9d; color: black; }
        .status-completed { background: #4cc9f0; color: black; }
        .status-aborted { background: #ff006e; color: white; }
        .playback-controls {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .playback-btn {
            flex: 1;
            padding: 8px;
            background: #0a0f1c;
            border: 1px solid #4cc9f0;
            color: #4cc9f0;
            cursor: pointer;
            border-radius: 4px;
        }
        .playback-btn:hover {
            background: #4cc9f0;
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
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-map-marked-alt"></i> MISSION PLANNER</h1>
        <div>
            <span style="color: #4cc9f0; margin-right: 15px;">
                <i class="fas fa-drone"></i> <?= count($drones) ?> DRONES AVAILABLE
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="mission-container">
        <!-- Left Panel - Mission Creation -->
        <div class="panel">
            <div class="panel-header">
                <i class="fas fa-plus-circle"></i> NEW MISSION
            </div>
            <div class="panel-content">
                <form id="missionForm">
                    <div class="form-group">
                        <label>MISSION NAME</label>
                        <input type="text" id="missionName" placeholder="e.g., Northern Patrol" required>
                    </div>
                    <div class="form-group">
                        <label>TYPE</label>
                        <select id="missionType">
                            <option value="patrol">Patrol</option>
                            <option value="surveillance">Surveillance</option>
                            <option value="recon">Reconnaissance</option>
                            <option value="emergency">Emergency</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ASSIGN DRONE</label>
                        <select id="assignedDrone">
                            <option value="">Select Drone</option>
                            <?php foreach ($drones as $drone): ?>
                                <option value="<?= $drone['id'] ?>"><?= $drone['name'] ?> (<?= $drone['status'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ALTITUDE (m)</label>
                        <input type="number" id="altitude" value="100" min="50" max="500">
                    </div>
                    <div class="form-group">
                        <label>SPEED (km/h)</label>
                        <input type="number" id="speed" value="30" min="10" max="100">
                    </div>
                    <div class="form-group">
                        <label>DESCRIPTION</label>
                        <textarea id="missionDesc" rows="2" placeholder="Mission objectives..."></textarea>
                    </div>
                    <button type="button" class="btn" onclick="addWaypoint()">
                        <i class="fas fa-map-pin"></i> ADD CURRENT POSITION
                    </button>
                    <button type="button" class="btn" onclick="clearWaypoints()">
                        <i class="fas fa-trash"></i> CLEAR WAYPOINTS
                    </button>
                    
                    <div class="waypoints-list" id="waypointsList">
                        <div style="color: #a0aec0; text-align: center; padding: 20px;">
                            Click on map to add waypoints
                        </div>
                    </div>
                    
                    <button type="button" class="btn" onclick="saveMission()">
                        <i class="fas fa-save"></i> SAVE MISSION
                    </button>
                    <button type="button" class="btn" onclick="startMission()">
                        <i class="fas fa-play"></i> START MISSION NOW
                    </button>
                </form>
            </div>
        </div>

        <!-- Center Panel - Map -->
        <div style="position: relative;">
            <div id="map"></div>
        </div>

        <!-- Right Panel - Missions List -->
        <div class="panel">
            <div class="panel-header">
                <i class="fas fa-history"></i> SAVED MISSIONS
            </div>
            <div class="panel-content">
                <div id="missionsList">
                    <?php foreach ($missions as $mission): ?>
                        <div class="mission-item" onclick="loadMission(<?= $mission['id'] ?>)">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: #4cc9f0;"><?= htmlspecialchars($mission['name']) ?></strong>
                                <span class="mission-status status-<?= $mission['status'] ?>">
                                    <?= strtoupper($mission['status']) ?>
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: #a0aec0; margin-top: 5px;">
                                <i class="fas fa-drone"></i> <?= $mission['drone_name'] ?? 'Unassigned' ?> |
                                <i class="fas fa-map-pin"></i> <?= count(json_decode($mission['waypoints'], true)) ?> waypoints |
                                <i class="fas fa-chart-line"></i> <?= $mission['log_count'] ?> logs
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission Playback Controls (hidden by default) -->
    <div id="playbackControls" style="display: none; margin-top: 20px; background: #151f2c; border: 1px solid #4cc9f0; border-radius: 8px; padding: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #4cc9f0; font-family: 'Orbitron';">
                <i class="fas fa-play-circle"></i> MISSION PLAYBACK
            </span>
            <div class="playback-controls">
                <button class="playback-btn" onclick="playbackStart()"><i class="fas fa-play"></i></button>
                <button class="playback-btn" onclick="playbackPause()"><i class="fas fa-pause"></i></button>
                <button class="playback-btn" onclick="playbackStop()"><i class="fas fa-stop"></i></button>
                <button class="playback-btn" onclick="playbackSpeed(0.5)">0.5x</button>
                <button class="playback-btn" onclick="playbackSpeed(1)">1x</button>
                <button class="playback-btn" onclick="playbackSpeed(2)">2x</button>
            </div>
        </div>
        <div id="playbackProgress" style="margin-top: 10px; height: 4px; background: #0a0f1c; border-radius: 2px;">
            <div id="playbackBar" style="width: 0%; height: 100%; background: #4cc9f0; border-radius: 2px;"></div>
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

        // Store waypoints
        let waypoints = [];
        let waypointMarkers = [];
        let polyline = null;

        // Click handler to add waypoints
        map.on('click', function(e) {
            const latlng = e.latlng;
            addWaypointAt(latlng.lat, latlng.lng);
        });

        function addWaypointAt(lat, lng) {
            const sequence = waypoints.length + 1;
            const waypoint = {
                lat: lat,
                lng: lng,
                alt: document.getElementById('altitude').value,
                speed: document.getElementById('speed').value,
                action: 'fly'
            };
            
            waypoints.push(waypoint);
            updateWaypointsDisplay();
            updateMapMarkers();
        }

        function addWaypoint() {
            const center = map.getCenter();
            addWaypointAt(center.lat, center.lng);
        }

        function updateMapMarkers() {
            // Clear existing markers
            waypointMarkers.forEach(marker => map.removeLayer(marker));
            waypointMarkers = [];
            
            // Remove old polyline
            if (polyline) map.removeLayer(polyline);
            
            // Add new markers and line
            if (waypoints.length > 0) {
                const latlngs = waypoints.map(w => [w.lat, w.lng]);
                
                // Add markers with numbers
                waypoints.forEach((w, i) => {
                    const marker = L.marker([w.lat, w.lng], {
                        icon: L.divIcon({
                            html: `<div style="background: #4cc9f0; color: #0a0f1c; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid #00ff9d;">${i+1}</div>`,
                            className: 'waypoint-marker',
                            iconSize: [24, 24]
                        })
                    }).addTo(map);
                    
                    marker.bindPopup(`
                        <b>Waypoint ${i+1}</b><br>
                        Altitude: ${w.alt}m<br>
                        Speed: ${w.speed} km/h
                    `);
                    
                    waypointMarkers.push(marker);
                });
                
                // Draw line
                polyline = L.polyline(latlngs, { color: '#4cc9f0', weight: 3, opacity: 0.7 }).addTo(map);
            }
        }

        function updateWaypointsDisplay() {
            const list = document.getElementById('waypointsList');
            if (waypoints.length === 0) {
                list.innerHTML = '<div style="color: #a0aec0; text-align: center; padding: 20px;">Click on map to add waypoints</div>';
                return;
            }
            
            list.innerHTML = waypoints.map((w, i) => `
                <div class="waypoint-item" draggable="true" ondragstart="dragStart(event, ${i})" ondragover="dragOver(event)" ondrop="drop(event, ${i})">
                    <div>
                        <strong style="color: #4cc9f0;">Waypoint ${i+1}</strong><br>
                        <small>${w.lat.toFixed(4)}, ${w.lng.toFixed(4)}</small>
                    </div>
                    <div>
                        <button class="playback-btn" onclick="removeWaypoint(${i})" style="padding: 2px 5px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function removeWaypoint(index) {
            waypoints.splice(index, 1);
            updateWaypointsDisplay();
            updateMapMarkers();
        }

        function clearWaypoints() {
            waypoints = [];
            updateWaypointsDisplay();
            updateMapMarkers();
        }

        // Drag and drop for reordering waypoints
        let dragIndex;
        function dragStart(event, index) {
            dragIndex = index;
            event.dataTransfer.setData('text/plain', index);
        }
        
        function dragOver(event) {
            event.preventDefault();
        }
        
        function drop(event, dropIndex) {
            event.preventDefault();
            if (dragIndex === undefined) return;
            
            const newWaypoints = [...waypoints];
            const [removed] = newWaypoints.splice(dragIndex, 1);
            newWaypoints.splice(dropIndex, 0, removed);
            waypoints = newWaypoints;
            
            updateWaypointsDisplay();
            updateMapMarkers();
            dragIndex = undefined;
        }

        function saveMission() {
            const mission = {
                name: document.getElementById('missionName').value,
                type: document.getElementById('missionType').value,
                drone_id: document.getElementById('assignedDrone').value,
                altitude: document.getElementById('altitude').value,
                speed: document.getElementById('speed').value,
                description: document.getElementById('missionDesc').value,
                waypoints: waypoints
            };
            
            if (!mission.name) {
                alert('Please enter a mission name');
                return;
            }
            
            if (waypoints.length < 2) {
                alert('Please add at least 2 waypoints');
                return;
            }
            
            fetch('/sentinel/api/missions.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(mission)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Mission saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }

        function startMission() {
            saveMission();
            // After save, start mission
        }

        function loadMission(missionId) {
            fetch(`/sentinel/api/missions.php?action=get&id=${missionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const mission = data.mission;
                        waypoints = mission.waypoints;
                        updateWaypointsDisplay();
                        updateMapMarkers();
                        
                        // Fit map to waypoints
                        const bounds = L.latLngBounds(waypoints.map(w => [w.lat, w.lng]));
                        map.fitBounds(bounds);
                        
                        // Show playback controls
                        document.getElementById('playbackControls').style.display = 'block';
                    }
                });
        }

        // Playback functions
        let playbackInterval;
        let currentWaypoint = 0;
        
        function playbackStart() {
            if (currentWaypoint >= waypoints.length) currentWaypoint = 0;
            
            playbackInterval = setInterval(() => {
                if (currentWaypoint < waypoints.length) {
                    const wp = waypoints[currentWaypoint];
                    map.panTo([wp.lat, wp.lng]);
                    document.getElementById('playbackBar').style.width = 
                        ((currentWaypoint + 1) / waypoints.length * 100) + '%';
                    currentWaypoint++;
                } else {
                    playbackStop();
                }
            }, 2000);
        }
        
        function playbackPause() {
            clearInterval(playbackInterval);
        }
        
        function playbackStop() {
            clearInterval(playbackInterval);
            currentWaypoint = 0;
            document.getElementById('playbackBar').style.width = '0%';
        }
        
        function playbackSpeed(speed) {
            // Implement speed change
        }
    </script>
</body>
</html>
