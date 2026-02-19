<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL - Enterprise Dashboard
 */
if (session_status() == PHP_SESSION_NONE) 
if (!isset($_SESSION['user_id'])) { header('Location: ?module=login'); exit; }
$role = $_SESSION['role'] ?? 'viewer';
$full_name = $_SESSION['full_name'] ?? 'Operator';

// Sample data
$weather = ['temp' => 28, 'condition' => 'Sunny', 'humidity' => 65];
$news = [
    ['title' => 'UEDF Drone Exercise Successful', 'time' => '2 hours ago'],
    ['title' => 'Border Security Enhanced', 'time' => '5 hours ago'],
    ['title' => 'New Command Center Opening', 'time' => '1 day ago']
];
$drone_health = [
    ['name' => 'Eagle Eye', 'health' => 95, 'status' => 'Excellent'],
    ['name' => 'Shadow Hawk', 'health' => 82, 'status' => 'Good'],
    ['name' => 'Night Stalker', 'health' => 67, 'status' => 'Fair'],
    ['name' => 'Vigilant', 'health' => 45, 'status' => 'Poor'],
    ['name' => 'Guardian', 'health' => 23, 'status' => 'Critical']
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enterprise Dashboard</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        .header { border-bottom: 2px solid #ff006e; padding: 20px; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; margin-bottom: 20px; }
        .card { background: #151f2c; border: 1px solid #ff006e; padding: 15px; border-radius: 5px; }
        .weather { border-color: #4cc9f0; }
        .good { color: #00ff9d; }
        .warning { color: #ffbe0b; }
        .critical { color: #ff006e; }
        a { color: #00ff9d; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏢 Enterprise Command Center</h1>
        <p>Welcome, <?= $full_name ?> (<?= strtoupper($role) ?>)</p>
        <a href="?module=home">← Back to Command Center</a>
    </div>
    
    <div class="grid">
        <div class="card weather">
            <h2>Weather - Eswatini</h2>
            <p>Temperature: <?= $weather['temp'] ?>°C</p>
            <p>Condition: <?= $weather['condition'] ?></p>
            <p>Humidity: <?= $weather['humidity'] ?>%</p>
        </div>
        
        <div class="card">
            <h2>System Status</h2>
            <p>Drones: 15 Active</p>
            <p>Threats: 3 Critical</p>
            <p>Nodes: 24 Online</p>
        </div>
        
        <div class="card">
            <h2>Quick Actions</h2>
            <button onclick="alert('Deploying drones')" style="background:#00ff9d; color:black; border:none; padding:10px; margin:5px; width:100%;">Deploy All Drones</button>
            <button onclick="alert('Running diagnostics')" style="background:#ff006e; color:white; border:none; padding:10px; margin:5px; width:100%;">Emergency Scan</button>
        </div>
    </div>
    
    <h2>Drone Health Predictions</h2>
    <div class="grid">
        <?php foreach ($drone_health as $d): ?>
        <div class="card">
            <h3><?= $d['name'] ?></h3>
            <p>Health: <span class="<?= strtolower($d['status']) ?>"><?= $d['health'] ?>% (<?= $d['status'] ?>)</span></p>
        </div>
        <?php endforeach; ?>
    </div>
    
    <h2>Latest News</h2>
    <div class="card">
        <?php foreach ($news as $item): ?>
        <div style="padding:10px; border-bottom:1px solid #ff006e20;">
            <strong><?= $item['title'] ?></strong><br>
            <small style="color:#a0aec0;"><?= $item['time'] ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
