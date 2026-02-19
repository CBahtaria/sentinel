<?php
require_once '../includes/session.php';
// Simple Drone Control Panel
if (session_status() == PHP_SESSION_NONE) 
if (!isset($_SESSION['user_id'])) { header('Location: ?module=login'); exit; }
if ($_SESSION['role'] !== 'commander') { die('Access Denied - Commander Only'); }

// Get drones
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $drones = $pdo->query("SELECT * FROM drones")->fetchAll();
} catch (Exception $e) {
    $drones = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Drone Control</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        .header { border-bottom: 2px solid #ff006e; padding: 20px; margin-bottom: 20px; }
        .drone-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
        .drone-card { background: #151f2c; border: 1px solid #ff006e; padding: 15px; border-radius: 5px; }
        button { background: #00ff9d; color: black; border: none; padding: 5px 10px; margin: 5px; cursor: pointer; }
        .emergency { background: #ff006e; color: white; }
        a { color: #00ff9d; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚁 Drone Control Panel</h1>
        <a href="?module=home">← Back</a>
    </div>
    <div class="drone-grid">
    <?php foreach ($drones as $d): ?>
        <div class="drone-card">
            <h3><?= $d['drone_name'] ?></h3>
            <p>ID: <?= $d['drone_id'] ?></p>
            <p>Status: <?= $d['status'] ?></p>
            <p>Battery: <?= $d['battery_level'] ?>%</p>
            <button onclick="alert('Launching <?= $d['drone_id'] ?>')">Launch</button>
            <button onclick="alert('Landing <?= $d['drone_id'] ?>')">Land</button>
            <button class="emergency" onclick="alert('Emergency! <?= $d['drone_id'] ?>')">Emergency</button>
        </div>
    <?php endforeach; ?>
    </div>
</body>
</html>
