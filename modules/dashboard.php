<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Command Dashboard
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    
}

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$role = $_SESSION['role'] ?? 'viewer';
$full_name = $_SESSION['full_name'] ?? 'Operator';

// Role-based access control
if (!in_array($role, ['commander', 'operator'])) {
    header('Location: ?module=home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - COMMAND DASHBOARD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            min-height: 100vh;
            padding: 20px;
        }
        .header {
            background: rgba(10,15,28,0.95);
            border: 2px solid #ff006e;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
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
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            text-decoration: none;
            border-radius: 4px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }
        .card-value {
            font-size: 3rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        .card-label {
            color: #a0aec0;
            margin-top: 10px;
        }
        .activity-feed {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
        }
        .feed-title {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
            margin-bottom: 20px;
        }
        .feed-item {
            padding: 10px;
            border-bottom: 1px solid #ff006e40;
            display: flex;
            gap: 15px;
        }
        .feed-time {
            color: #00ff9d;
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
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-tachometer-alt"></i>
            <h1>COMMAND DASHBOARD</h1>
        </div>
        <div>
            <span style="color: #ff006e;"><?= strtoupper($full_name) ?> | <?= strtoupper($role) ?></span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <i class="fas fa-drone" style="font-size: 2rem; color: #00ff9d;"></i>
            <div class="card-value">15</div>
            <div class="card-label">TOTAL DRONES</div>
        </div>
        <div class="dashboard-card">
            <i class="fas fa-shield-alt" style="font-size: 2rem; color: #ff006e;"></i>
            <div class="card-value">5</div>
            <div class="card-label">ACTIVE THREATS</div>
        </div>
        <div class="dashboard-card">
            <i class="fas fa-users" style="font-size: 2rem; color: #4cc9f0;"></i>
            <div class="card-value">12</div>
            <div class="card-label">PERSONNEL</div>
        </div>
    </div>

    <div class="activity-feed">
        <div class="feed-title"><i class="fas fa-history"></i> RECENT ACTIVITY</div>
        <div class="feed-item">
            <span class="feed-time">[10:30]</span>
            <span>Drone patrol completed - Sector 7</span>
        </div>
        <div class="feed-item">
            <span class="feed-time">[09:45]</span>
            <span>New threat detected - Critical severity</span>
        </div>
        <div class="feed-item">
            <span class="feed-time">[08:20]</span>
            <span>System diagnostics: All systems operational</span>
        </div>
        <div class="feed-item">
            <span class="feed-time">[07:15]</span>
            <span>Shift change - Commander Bartaria online</span>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>
</body>
</html>
