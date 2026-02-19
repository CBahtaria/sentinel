<?php
namespace Sentinel\Controllers;

/**
 * UEDF SENTINEL v5.0 - Advanced Drone Recordings & Media Management
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'viewer';
$full_name = $_SESSION['full_name'] ?? 'Gen. Bartaria';

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create recordings table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS recordings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            drone_id INT,
            drone_name VARCHAR(50),
            filename VARCHAR(255),
            file_path VARCHAR(500),
            thumbnail_path VARCHAR(500),
            duration INT,
            file_size BIGINT,
            resolution VARCHAR(20),
            fps INT,
            format VARCHAR(10),
            recording_type ENUM('video', 'snapshot', 'timelapse') DEFAULT 'video',
            location_lat DECIMAL(10,8),
            location_lng DECIMAL(11,8),
            altitude INT,
            camera_angle INT,
            weather_conditions TEXT,
            tags TEXT,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            recorded_at DATETIME,
            processed BOOLEAN DEFAULT FALSE,
            analyzed BOOLEAN DEFAULT FALSE,
            threat_detected BOOLEAN DEFAULT FALSE,
            object_detected TEXT,
            confidence_score FLOAT,
            INDEX idx_drone (drone_id),
            INDEX idx_date (created_at),
            INDEX idx_type (recording_type)
        );
        
        CREATE TABLE IF NOT EXISTS snapshots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recording_id INT,
            timestamp DATETIME,
            file_path VARCHAR(500),
            thumbnail_path VARCHAR(500),
            metadata TEXT,
            analyzed BOOLEAN DEFAULT FALSE,
            detected_objects TEXT,
            threat_level ENUM('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'LOW',
            FOREIGN KEY (recording_id) REFERENCES recordings(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS analytics_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recording_id INT,
            event_type VARCHAR(50),
            timestamp DATETIME,
            confidence FLOAT,
            metadata TEXT,
            FOREIGN KEY (recording_id) REFERENCES recordings(id) ON DELETE CASCADE
        );
    ");
    
    // Get recordings from database
    $stmt = $pdo->query("
        SELECT r.*, 
               (SELECT COUNT(*) FROM snapshots WHERE recording_id = r.id) as snapshot_count,
               (SELECT COUNT(*) FROM analytics_events WHERE recording_id = r.id) as event_count
        FROM recordings r 
        ORDER BY r.created_at DESC 
        LIMIT 50
    ");
    $db_recordings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $total_recordings = $pdo->query("SELECT COUNT(*) FROM recordings")->fetchColumn() ?: 24;
    $total_snapshots = $pdo->query("SELECT COUNT(*) FROM snapshots")->fetchColumn() ?: 156;
    $total_storage = $pdo->query("SELECT SUM(file_size) FROM recordings")->fetchColumn() ?: 1572864000;
    $threat_detected = $pdo->query("SELECT COUNT(*) FROM recordings WHERE threat_detected = TRUE")->fetchColumn() ?: 7;
    
} catch (PDOException $e) {
    // Fallback data if database not available
    $db_recordings = [];
    $total_recordings = 24;
    $total_snapshots = 156;
    $total_storage = 1572864000; // 1.46 GB
    $threat_detected = 7;
}

// Format storage size
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Sample recordings for fallback
$sample_recordings = [
    [
        'id' => 1,
        'drone_name' => 'DRONE-001',
        'filename' => 'border_patrol_20260217_1423.mp4',
        'duration' => 923,
        'file_size' => 245000000,
        'resolution' => '4K',
        'recording_type' => 'video',
        'created_at' => '2026-02-17 14:23:45',
        'threat_detected' => true,
        'snapshot_count' => 12,
        'event_count' => 3
    ],
    [
        'id' => 2,
        'drone_name' => 'DRONE-003',
        'filename' => 'sector7_surveillance_20260217.mp4',
        'duration' => 1845,
        'file_size' => 500000000,
        'resolution' => '1080p',
        'recording_type' => 'video',
        'created_at' => '2026-02-17 09:12:33',
        'threat_detected' => false,
        'snapshot_count' => 24,
        'event_count' => 0
    ],
    [
        'id' => 3,
        'drone_name' => 'DRONE-002',
        'filename' => 'night_patrol_20260216_2345.mp4',
        'duration' => 2765,
        'file_size' => 750000000,
        'resolution' => '1080p',
        'recording_type' => 'video',
        'created_at' => '2026-02-16 23:45:12',
        'threat_detected' => true,
        'snapshot_count' => 45,
        'event_count' => 7
    ],
    [
        'id' => 4,
        'drone_name' => 'DRONE-005',
        'filename' => 'training_exercise_20260216.mp4',
        'duration' => 3600,
        'file_size' => 1000000000,
        'resolution' => '4K',
        'recording_type' => 'video',
        'created_at' => '2026-02-16 10:05:00',
        'threat_detected' => false,
        'snapshot_count' => 8,
        'event_count' => 0
    ],
    [
        'id' => 5,
        'drone_name' => 'DRONE-004',
        'filename' => 'maintenance_log_20260215.mp4',
        'duration' => 452,
        'file_size' => 120000000,
        'resolution' => '720p',
        'recording_type' => 'video',
        'created_at' => '2026-02-15 16:30:22',
        'threat_detected' => false,
        'snapshot_count' => 5,
        'event_count' => 0
    ]
];

$recordings = !empty($db_recordings) ? $db_recordings : $sample_recordings;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - DRONE RECORDINGS</title>
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
        
        /* Header */
        .header {
            background: #151f2c;
            border: 2px solid #ff006e;
            padding: 20px 30px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(255,0,110,0.2);
            backdrop-filter: blur(10px);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo i {
            font-size: 2rem;
            color: #ff006e;
            filter: drop-shadow(0 0 10px #ff006e);
            animation: pulse 2s infinite;
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
            font-size: 1.8rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-badge {
            padding: 8px 20px;
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 30px;
        }
        
        .back-btn {
            padding: 10px 25px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 30px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
            box-shadow: 0 0 20px #ff006e;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 12px;
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
            background: linear-gradient(45deg, transparent, rgba(255,0,110,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        .stat-value {
            font-size: 2.2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            position: relative;
        }
        
        .stat-label {
            color: #a0aec0;
            font-size: 0.8rem;
            text-transform: uppercase;
            position: relative;
        }
        
        /* Filter Bar */
        .filter-bar {
            background: #151f2c;
            border: 1px solid #ff006e;
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
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Share Tech Mono', monospace;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: #ff006e;
            color: #0a0f1c;
        }
        
        .search-box {
            flex: 1;
            display: flex;
            align-items: center;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            border-radius: 30px;
            padding: 8px 20px;
        }
        
        .search-box i {
            color: #ff006e;
            margin-right: 10px;
        }
        
        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            color: #00ff9d;
            font-family: 'Share Tech Mono', monospace;
            outline: none;
        }
        
        /* Recordings Grid */
        .recordings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .recording-card {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 12px;
            overflow: hidden;
            transition: 0.3s;
            position: relative;
        }
        
        .recording-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(255,0,110,0.3);
        }
        
        .threat-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff006e;
            color: #0a0f1c;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 2;
            animation: pulse 2s infinite;
        }
        
        .thumbnail {
            height: 200px;
            background: #0a0f1c;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 2px solid #ff006e;
            position: relative;
            cursor: pointer;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .play-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
        }
        
        .thumbnail:hover .play-overlay {
            opacity: 1;
        }
        
        .play-overlay i {
            font-size: 3rem;
            color: #ff006e;
            filter: drop-shadow(0 0 10px #ff006e);
        }
        
        .duration-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            border: 1px solid #ff006e;
            color: #00ff9d;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .recording-info {
            padding: 15px;
        }
        
        .recording-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .recording-title {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
        }
        
        .recording-type {
            padding: 3px 10px;
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            border-radius: 20px;
            font-size: 0.7rem;
        }
        
        .recording-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid #ff006e40;
            border-bottom: 1px solid #ff006e40;
        }
        
        .meta-item {
            text-align: center;
        }
        
        .meta-value {
            color: #00ff9d;
            font-size: 1.1rem;
        }
        
        .meta-label {
            color: #a0aec0;
            font-size: 0.65rem;
            text-transform: uppercase;
        }
        
        .recognition-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 10px 0;
        }
        
        .tag {
            padding: 3px 10px;
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 20px;
            font-size: 0.7rem;
        }
        
        .recording-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .drone-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #00ff9d;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
            transform: scale(1.1);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 12px;
            padding: 30px;
            max-width: 900px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .video-player {
            width: 100%;
            background: #0a0f1c;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .video-player video {
            width: 100%;
            border-radius: 8px;
            max-height: 500px;
        }
        
        .modal-title {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .modal-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .modal-stat {
            text-align: center;
            background: #0a0f1c;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ff006e40;
        }
        
        .modal-stat-value {
            font-size: 1.5rem;
            color: #00ff9d;
        }
        
        .modal-stat-label {
            color: #a0aec0;
            font-size: 0.7rem;
        }
        
        .close-modal {
            padding: 12px 30px;
            background: #ff006e;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            margin-top: 20px;
            font-size: 1rem;
            transition: 0.3s;
        }
        
        .close-modal:hover {
            background: #00ff9d;
            transform: scale(1.05);
        }
        
        /* Analysis Results */
        .analysis-results {
            background: #0a0f1c;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .analysis-section {
            margin-bottom: 20px;
        }
        
        .analysis-section h3 {
            color: #ff006e;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .object-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .object-tag {
            padding: 5px 15px;
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .threat-tag {
            padding: 5px 15px;
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        /* Animations */
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
            z-index: 10001;
            animation: slideIn 0.3s ease;
            font-family: 'Share Tech Mono', monospace;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .notification.error {
            background: #ff006e;
            color: white;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Loading Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #ff006e20;
            border-top: 4px solid #ff006e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filter-bar {
                border-radius: 12px;
                flex-direction: column;
            }
            
            .search-box {
                width: 100%;
            }
            
            .modal-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-video"></i>
            <h1>DRONE RECORDINGS</h1>
        </div>
        <div class="user-info">
            <span class="user-badge">
                <i class="fas fa-user"></i> <?= htmlspecialchars($full_name) ?>
            </span>
            <a href="?module=home" class="back-btn">
                <i class="fas fa-arrow-left"></i> DASHBOARD
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $total_recordings ?></div>
            <div class="stat-label">TOTAL RECORDINGS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_snapshots ?></div>
            <div class="stat-label">SNAPSHOTS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= formatBytes($total_storage) ?></div>
            <div class="stat-label">STORAGE USED</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;"><?= $threat_detected ?></div>
            <div class="stat-label">THREATS DETECTED</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <button class="filter-btn active" onclick="filterRecordings('all', this)">ALL</button>
        <button class="filter-btn" onclick="filterRecordings('video', this)">VIDEOS</button>
        <button class="filter-btn" onclick="filterRecordings('snapshot', this)">SNAPSHOTS</button>
        <button class="filter-btn" onclick="filterRecordings('threat', this)">THREATS</button>
        <button class="filter-btn" onclick="filterRecordings('analyzed', this)">ANALYZED</button>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search recordings..." id="searchInput" onkeyup="searchRecordings()">
        </div>
    </div>

    <!-- Recordings Grid -->
    <div class="recordings-grid" id="recordingsGrid">
        <?php foreach ($recordings as $index => $rec): 
            $duration_formatted = gmdate("H:i:s", $rec['duration'] ?? 0);
            $threat_class = isset($rec['threat_detected']) && $rec['threat_detected'] ? 'threat-badge' : '';
            $type = $rec['recording_type'] ?? 'video';
            $uniqueId = $rec['id'] ?? ($index + 1);
        ?>
        <div class="recording-card" 
             data-id="<?= $uniqueId ?>"
             data-type="<?= $type ?>" 
             data-threat="<?= isset($rec['threat_detected']) && $rec['threat_detected'] ? 'true' : 'false' ?>" 
             data-analyzed="<?= isset($rec['event_count']) && $rec['event_count'] > 0 ? 'true' : 'false' ?>">
            
            <?php if (isset($rec['threat_detected']) && $rec['threat_detected']): ?>
            <div class="threat-badge">
                <i class="fas fa-exclamation-triangle"></i> THREAT DETECTED
            </div>
            <?php endif; ?>
            
            <div class="thumbnail" onclick="playRecording(<?= $uniqueId ?>)">
                <img src="https://via.placeholder.com/400x200/0a0f1c/ff006e?text=DRONE+<?= $rec['drone_name'] ?? 'FEED' ?>" alt="Thumbnail">
                <div class="play-overlay">
                    <i class="fas fa-play-circle"></i>
                </div>
                <?php if (isset($rec['duration'])): ?>
                <div class="duration-badge">
                    <i class="fas fa-clock"></i> <?= $duration_formatted ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="recording-info">
                <div class="recording-header">
                    <div class="recording-title">
                        <i class="fas fa-drone"></i> <?= htmlspecialchars($rec['drone_name'] ?? 'Unknown') ?>
                    </div>
                    <span class="recording-type">
                        <?= strtoupper($type) ?>
                    </span>
                </div>
                
                <div class="recording-meta">
                    <div class="meta-item">
                        <div class="meta-value"><?= $rec['resolution'] ?? '1080p' ?></div>
                        <div class="meta-label">RESOLUTION</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?= formatBytes($rec['file_size'] ?? 0) ?></div>
                        <div class="meta-label">SIZE</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?= $rec['snapshot_count'] ?? 0 ?></div>
                        <div class="meta-label">SNAPSHOTS</div>
                    </div>
                </div>
                
                <?php if (isset($rec['event_count']) && $rec['event_count'] > 0): ?>
                <div class="recognition-tags">
                    <span class="tag"><i class="fas fa-robot"></i> AI ANALYZED</span>
                    <?php if ($rec['event_count'] > 5): ?>
                    <span class="tag"><i class="fas fa-exclamation-triangle"></i> MULTIPLE EVENTS</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="recording-footer">
                    <div class="drone-info">
                        <i class="fas fa-calendar"></i>
                        <?= date('Y-m-d H:i', strtotime($rec['created_at'] ?? 'now')) ?>
                    </div>
                    <div class="action-buttons">
                        <button class="action-btn" onclick="playRecording(<?= $uniqueId ?>)" title="Play">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="action-btn" onclick="analyzeRecording(<?= $uniqueId ?>)" title="Analyze">
                            <i class="fas fa-brain"></i>
                        </button>
                        <button class="action-btn" onclick="downloadRecording(<?= $uniqueId ?>)" title="Download">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="action-btn" onclick="showRecordingDetails(<?= $uniqueId ?>)" title="Details">
                            <i class="fas fa-info"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Video Player Modal -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <h2 class="modal-title" id="modalTitle">Recording Playback</h2>
            <div class="video-player">
                <video controls id="videoPlayer" style="width: 100%;">
                    <source src="#" type="video/mp4">
                </video>
            </div>
            <div class="modal-stats" id="modalStats">
                <!-- Populated by JavaScript -->
            </div>
            <div style="text-align: center;">
                <button class="close-modal" onclick="closeModal()">CLOSE</button>
            </div>
        </div>
    </div>

    <!-- Analysis Modal -->
    <div class="modal" id="analysisModal">
        <div class="modal-content">
            <h2 class="modal-title">AI ANALYSIS RESULTS</h2>
            <div id="analysisResults" class="analysis-results">
                <!-- Populated by JavaScript -->
            </div>
            <div style="text-align: center;">
                <button class="close-modal" onclick="closeAnalysisModal()">CLOSE</button>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <h2 class="modal-title" id="detailsTitle">Recording Details</h2>
            <div id="detailsContent" class="analysis-results">
                <!-- Populated by JavaScript -->
            </div>
            <div style="text-align: center;">
                <button class="close-modal" onclick="closeDetailsModal()">CLOSE</button>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // GLOBAL FUNCTIONS - FIXED AND WORKING
        // ============================================

        // Current filter state
        let currentFilter = 'all';
        let searchTerm = '';

        // Filter recordings
        function filterRecordings(type, button) {
            currentFilter = type;
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
            
            applyFilters();
        }

        // Search recordings
        function searchRecordings() {
            searchTerm = document.getElementById('searchInput').value.toLowerCase();
            applyFilters();
        }

        // Apply all filters
        function applyFilters() {
            const cards = document.querySelectorAll('.recording-card');
            
            cards.forEach(card => {
                let show = true;
                
                // Filter by type
                if (currentFilter !== 'all') {
                    if (currentFilter === 'threat') {
                        show = card.dataset.threat === 'true';
                    } else if (currentFilter === 'analyzed') {
                        show = card.dataset.analyzed === 'true';
                    } else {
                        show = card.dataset.type === currentFilter;
                    }
                }
                
                // Filter by search term
                if (show && searchTerm) {
                    const title = card.querySelector('.recording-title').textContent.toLowerCase();
                    show = title.includes(searchTerm);
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        }

        // Play recording
        function playRecording(id) {
            const modal = document.getElementById('videoModal');
            const player = document.getElementById('videoPlayer');
            const title = document.getElementById('modalTitle');
            const stats = document.getElementById('modalStats');
            
            // Find recording data
            const recording = findRecordingById(id);
            
            title.textContent = `Recording Playback - Drone ${recording?.drone_name || 'Unknown'}`;
            
            // Set video source (simulated)
            player.innerHTML = '<source src="#" type="video/mp4">Your browser does not support video playback.';
            
            // Generate stats HTML
            stats.innerHTML = `
                <div class="modal-stat">
                    <div class="modal-stat-value">${recording?.resolution || '1080p'}</div>
                    <div class="modal-stat-label">RESOLUTION</div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-value">30</div>
                    <div class="modal-stat-label">FPS</div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-value">${formatBytes(recording?.file_size || 0)}</div>
                    <div class="modal-stat-label">FILE SIZE</div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-value">H.264</div>
                    <div class="modal-stat-label">CODEC</div>
                </div>
            `;
            
            modal.classList.add('active');
            
            // Try to play video (simulated)
            setTimeout(() => {
                showNotification('Loading video stream...');
            }, 500);
        }

        // Analyze recording
        function analyzeRecording(id) {
            const modal = document.getElementById('analysisModal');
            const results = document.getElementById('analysisResults');
            
            // Find recording data
            const recording = findRecordingById(id);
            
            // Simulate AI analysis
            const analysis = {
                objects: ['Drone', 'Vehicle', 'Person', 'Building', 'Aircraft'],
                threats: ['Unauthorized Access', 'Border Crossing', 'Suspicious Activity'],
                confidence: 94,
                duration: '2.3s',
                events: Math.floor(Math.random() * 10) + 3
            };
            
            results.innerHTML = `
                <div class="analysis-section">
                    <h3><i class="fas fa-cube"></i> DETECTED OBJECTS</h3>
                    <div class="object-tags">
                        ${analysis.objects.map(obj => `<span class="object-tag">${obj}</span>`).join('')}
                    </div>
                </div>
                
                <div class="analysis-section">
                    <h3><i class="fas fa-exclamation-triangle"></i> THREATS IDENTIFIED</h3>
                    <div class="object-tags">
                        ${analysis.threats.map(threat => `<span class="threat-tag">${threat}</span>`).join('')}
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; color: #00ff9d;">${analysis.confidence}%</div>
                        <div style="color: #a0aec0;">CONFIDENCE</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; color: #00ff9d;">${analysis.events}</div>
                        <div style="color: #a0aec0;">EVENTS</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; color: #00ff9d;">${analysis.duration}</div>
                        <div style="color: #a0aec0;">ANALYSIS TIME</div>
                    </div>
                </div>
            `;
            
            modal.classList.add('active');
            showNotification('AI Analysis complete');
        }

        // Download recording
        function downloadRecording(id) {
            const recording = findRecordingById(id);
            showNotification(`Downloading ${recording?.drone_name || 'recording'}...`);
            
            // Simulate download progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                if (progress >= 100) {
                    clearInterval(interval);
                    showNotification('Download complete');
                }
            }, 300);
        }

        // Show recording details
        function showRecordingDetails(id) {
            const modal = document.getElementById('detailsModal');
            const title = document.getElementById('detailsTitle');
            const content = document.getElementById('detailsContent');
            
            const recording = findRecordingById(id);
            
            title.textContent = `Details - ${recording?.drone_name || 'Recording'} ${recording?.id || ''}`;
            
            content.innerHTML = `
                <div style="display: grid; gap: 15px;">
                    <div><strong style="color: #ff006e;">Drone:</strong> <span style="color: #00ff9d;">${recording?.drone_name || 'Unknown'}</span></div>
                    <div><strong style="color: #ff006e;">Recording Date:</strong> <span style="color: #00ff9d;">${recording?.created_at || 'Unknown'}</span></div>
                    <div><strong style="color: #ff006e;">Duration:</strong> <span style="color: #00ff9d;">${recording?.duration ? gmdate(recording.duration) : '00:00:00'}</span></div>
                    <div><strong style="color: #ff006e;">Resolution:</strong> <span style="color: #00ff9d;">${recording?.resolution || '1080p'}</span></div>
                    <div><strong style="color: #ff006e;">File Size:</strong> <span style="color: #00ff9d;">${formatBytes(recording?.file_size || 0)}</span></div>
                    <div><strong style="color: #ff006e;">Snapshots:</strong> <span style="color: #00ff9d;">${recording?.snapshot_count || 0}</span></div>
                    <div><strong style="color: #ff006e;">Location:</strong> <span style="color: #00ff9d;">Sector ${Math.floor(Math.random() * 10) + 1}</span></div>
                    <div><strong style="color: #ff006e;">AI Events:</strong> <span style="color: #00ff9d;">${recording?.event_count || 0}</span></div>
                </div>
            `;
            
            modal.classList.add('active');
        }

        // Helper: Find recording by ID
        function findRecordingById(id) {
            // Try to find in our data
            const recordings = <?= json_encode($recordings) ?>;
            return recordings.find(r => r.id == id) || { drone_name: 'Unknown', resolution: '1080p', file_size: 0 };
        }

        // Helper: Format seconds to H:i:s
        function gmdate(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        // Helper: Format bytes
        function formatBytes(bytes, precision = 2) {
            if (bytes === 0) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const pow = Math.floor(Math.log(bytes) / Math.log(1024));
            return (bytes / Math.pow(1024, pow)).toFixed(precision) + ' ' + units[pow];
        }

        // Close modals
        function closeModal() {
            document.getElementById('videoModal').classList.remove('active');
            const player = document.getElementById('videoPlayer');
            player.pause();
        }

        function closeAnalysisModal() {
            document.getElementById('analysisModal').classList.remove('active');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }

        // Show notification
        function showNotification(message, isError = false) {
            const notification = document.createElement('div');
            notification.className = 'notification' + (isError ? ' error' : '');
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Recordings module initialized');
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                    closeAnalysisModal();
                    closeDetailsModal();
                }
            });
            
            // Add sample video source for demo
            const videoPlayer = document.getElementById('videoPlayer');
            if (videoPlayer) {
                videoPlayer.addEventListener('error', function() {
                    // Silent fail - demo mode
                });
            }
        });
    </script>
</body>
</html><?php
/**
 * UEDF SENTINEL v5.0 - Drone Recordings Library
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete media management system for drone footage and snapshots
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Get recordings from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    // Create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS recordings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            drone_id INT,
            drone_name VARCHAR(50),
            filename VARCHAR(255),
            file_path VARCHAR(500),
            thumbnail_path VARCHAR(500),
            duration INT,
            file_size BIGINT,
            resolution VARCHAR(20),
            fps INT,
            format VARCHAR(10),
            recording_type ENUM('video', 'snapshot', 'timelapse') DEFAULT 'video',
            location_lat DECIMAL(10,8),
            location_lng DECIMAL(11,8),
            altitude INT,
            camera_angle INT,
            weather_conditions TEXT,
            tags TEXT,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            recorded_at DATETIME,
            processed BOOLEAN DEFAULT FALSE,
            analyzed BOOLEAN DEFAULT FALSE,
            threat_detected BOOLEAN DEFAULT FALSE,
            confidence_score FLOAT,
            INDEX idx_drone (drone_id),
            INDEX idx_date (created_at),
            INDEX idx_type (recording_type)
        );
        
        CREATE TABLE IF NOT EXISTS snapshots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recording_id INT,
            timestamp DATETIME,
            file_path VARCHAR(500),
            thumbnail_path VARCHAR(500),
            metadata TEXT,
            analyzed BOOLEAN DEFAULT FALSE,
            detected_objects TEXT,
            threat_level ENUM('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'LOW',
            FOREIGN KEY (recording_id) REFERENCES recordings(id) ON DELETE CASCADE
        );
    ");
    
    // Get recordings
    $recordings = $pdo->query("
        SELECT r.*, 
               (SELECT COUNT(*) FROM snapshots WHERE recording_id = r.id) as snapshot_count
        FROM recordings r 
        ORDER BY r.created_at DESC 
        LIMIT 50
    ")->fetchAll();
    
    // Get statistics
    $total_recordings = $pdo->query("SELECT COUNT(*) FROM recordings")->fetchColumn() ?: 0;
    $total_snapshots = $pdo->query("SELECT COUNT(*) FROM snapshots")->fetchColumn() ?: 0;
    $total_storage = $pdo->query("SELECT SUM(file_size) FROM recordings")->fetchColumn() ?: 0;
    $threat_detected = $pdo->query("SELECT COUNT(*) FROM recordings WHERE threat_detected = TRUE")->fetchColumn() ?: 0;
    
} catch (Exception $e) {
    // Fallback data
    $recordings = [];
    $total_recordings = 24;
    $total_snapshots = 156;
    $total_storage = 1572864000; // 1.5 GB
    $threat_detected = 7;
}

// Sample recordings for fallback
$sample_recordings = [
    [
        'id' => 1,
        'drone_name' => 'DRONE-001',
        'filename' => 'border_patrol_20260217_1423.mp4',
        'duration' => 923,
        'file_size' => 245000000,
        'resolution' => '4K',
        'recording_type' => 'video',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'threat_detected' => true,
        'snapshot_count' => 12,
        'analyzed' => true,
        'tags' => 'border,patrol,security'
    ],
    [
        'id' => 2,
        'drone_name' => 'DRONE-003',
        'filename' => 'sector7_surveillance_20260217.mp4',
        'duration' => 1845,
        'file_size' => 500000000,
        'resolution' => '1080p',
        'recording_type' => 'video',
        'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
        'threat_detected' => false,
        'snapshot_count' => 24,
        'analyzed' => true,
        'tags' => 'surveillance,routine'
    ],
    [
        'id' => 3,
        'drone_name' => 'DRONE-002',
        'filename' => 'night_patrol_20260216_2345.mp4',
        'duration' => 2765,
        'file_size' => 750000000,
        'resolution' => '1080p',
        'recording_type' => 'video',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'threat_detected' => true,
        'snapshot_count' => 45,
        'analyzed' => true,
        'tags' => 'night,patrol,infrared'
    ],
    [
        'id' => 4,
        'drone_name' => 'DRONE-005',
        'filename' => 'training_exercise_20260216.mp4',
        'duration' => 3600,
        'file_size' => 1000000000,
        'resolution' => '4K',
        'recording_type' => 'video',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'threat_detected' => false,
        'snapshot_count' => 8,
        'analyzed' => false,
        'tags' => 'training,exercise'
    ],
    [
        'id' => 5,
        'drone_name' => 'DRONE-004',
        'filename' => 'maintenance_log_20260215.mp4',
        'duration' => 452,
        'file_size' => 120000000,
        'resolution' => '720p',
        'recording_type' => 'video',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'threat_detected' => false,
        'snapshot_count' => 5,
        'analyzed' => false,
        'tags' => 'maintenance,log'
    ]
];

$recordings = !empty($recordings) ? $recordings : $sample_recordings;

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - DRONE RECORDINGS</title>
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
        
        .archive-badge {
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
        
        .filter-bar {
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
        
        .search-box {
            flex: 1;
            display: flex;
            align-items: center;
            background: #0a0f1c;
            border: 1px solid <?= $accent ?>;
            border-radius: 30px;
            padding: 8px 15px;
            min-width: 200px;
        }
        
        .search-box i {
            color: <?= $accent ?>;
            margin-right: 8px;
        }
        
        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            color: #00ff9d;
            font-family: 'Share Tech Mono', monospace;
            outline: none;
            font-size: 0.9rem;
        }
        
        .filter-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.8rem;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .recordings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .recording-card {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            overflow: hidden;
            transition: 0.3s;
            position: relative;
        }
        
        .recording-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255,0,110,0.3);
        }
        
        .threat-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff006e;
            color: #0a0f1c;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            z-index: 2;
            animation: pulse 2s infinite;
        }
        
        .thumbnail {
            height: 180px;
            background: #0a0f1c;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 2px solid <?= $accent ?>;
            position: relative;
            cursor: pointer;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .play-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
        }
        
        .thumbnail:hover .play-overlay {
            opacity: 1;
        }
        
        .play-overlay i {
            font-size: 3rem;
            color: <?= $accent ?>;
            filter: drop-shadow(0 0 10px <?= $accent ?>);
        }
        
        .duration-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            border: 1px solid <?= $accent ?>;
            color: #00ff9d;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }
        
        .recording-info {
            padding: 15px;
        }
        
        .recording-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .recording-title {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
        }
        
        .recording-type {
            padding: 2px 10px;
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            border-radius: 20px;
            font-size: 0.65rem;
        }
        
        .recording-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 12px 0;
            padding: 10px 0;
            border-top: 1px solid <?= $accent ?>20;
            border-bottom: 1px solid <?= $accent ?>20;
        }
        
        .meta-item {
            text-align: center;
        }
        
        .meta-value {
            font-size: 1.1rem;
            color: #00ff9d;
        }
        
        .meta-label {
            font-size: 0.6rem;
            color: #a0aec0;
            text-transform: uppercase;
        }
        
        .tags {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            margin: 8px 0;
        }
        
        .tag {
            padding: 2px 8px;
            background: <?= $accent ?>20;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 12px;
            font-size: 0.6rem;
        }
        
        .recording-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .drone-info {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #00ff9d;
            font-size: 0.8rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .action-btn.download:hover {
            background: #00ff9d;
            border-color: #00ff9d;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 25px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .video-player {
            width: 100%;
            background: #0a0f1c;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .video-player video {
            width: 100%;
            border-radius: 8px;
            max-height: 400px;
        }
        
        .modal-title {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .modal-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .modal-stat {
            background: #0a0f1c;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }
        
        .modal-stat-value {
            font-size: 1.2rem;
            color: #00ff9d;
        }
        
        .modal-stat-label {
            font-size: 0.65rem;
            color: #a0aec0;
        }
        
        .close-modal {
            padding: 10px 25px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            margin-top: 15px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .page-btn {
            padding: 8px 12px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .page-btn:hover, .page-btn.active {
            background: <?= $accent ?>;
            color: #0a0f1c;
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
            z-index: 10001;
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
            
            .filter-bar {
                flex-direction: column;
            }
            
            .search-box {
                width: 100%;
            }
            
            .modal-stats {
                grid-template-columns: repeat(2, 1fr);
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
            
            .recording-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .recording-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-around;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-video"></i>
            <h1>DRONE RECORDINGS</h1>
            <span class="archive-badge">ARCHIVE</span>
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
            <div class="stat-value"><?= $total_recordings ?></div>
            <div class="stat-label">TOTAL RECORDINGS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_snapshots ?></div>
            <div class="stat-label">SNAPSHOTS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= formatBytes($total_storage) ?></div>
            <div class="stat-label">STORAGE USED</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;"><?= $threat_detected ?></div>
            <div class="stat-label">THREATS FOUND</div>
        </div>
    </div>

    <div class="filter-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search recordings..." onkeyup="searchRecordings()">
        </div>
        <button class="filter-btn active" onclick="filterRecordings('all', this)">ALL</button>
        <button class="filter-btn" onclick="filterRecordings('video', this)">VIDEOS</button>
        <button class="filter-btn" onclick="filterRecordings('snapshot', this)">SNAPSHOTS</button>
        <button class="filter-btn" onclick="filterRecordings('threat', this)">THREATS</button>
        <button class="filter-btn" onclick="filterRecordings('analyzed', this)">ANALYZED</button>
    </div>

    <div class="recordings-grid" id="recordingsGrid">
        <?php foreach ($recordings as $index => $rec): 
            $duration_formatted = formatDuration($rec['duration'] ?? 0);
            $type = $rec['recording_type'] ?? 'video';
            $uniqueId = $rec['id'] ?? ($index + 1);
        ?>
        <div class="recording-card" 
             data-id="<?= $uniqueId ?>"
             data-type="<?= $type ?>" 
             data-threat="<?= isset($rec['threat_detected']) && $rec['threat_detected'] ? 'true' : 'false' ?>" 
             data-analyzed="<?= isset($rec['analyzed']) && $rec['analyzed'] ? 'true' : 'false' ?>">
            
            <?php if (isset($rec['threat_detected']) && $rec['threat_detected']): ?>
            <div class="threat-badge">
                <i class="fas fa-exclamation-triangle"></i> THREAT
            </div>
            <?php endif; ?>
            
            <div class="thumbnail" onclick="playRecording(<?= $uniqueId ?>)">
                <img src="https://via.placeholder.com/400x200/0a0f1c/ff006e?text=DRONE+<?= urlencode($rec['drone_name'] ?? 'FEED') ?>" alt="Thumbnail">
                <div class="play-overlay">
                    <i class="fas fa-play-circle"></i>
                </div>
                <?php if (isset($rec['duration'])): ?>
                <div class="duration-badge">
                    <i class="fas fa-clock"></i> <?= $duration_formatted ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="recording-info">
                <div class="recording-header">
                    <div class="recording-title">
                        <i class="fas fa-drone"></i> <?= htmlspecialchars($rec['drone_name'] ?? 'Unknown') ?>
                    </div>
                    <span class="recording-type"><?= strtoupper($type) ?></span>
                </div>
                
                <div class="recording-meta">
                    <div class="meta-item">
                        <div class="meta-value"><?= $rec['resolution'] ?? '1080p' ?></div>
                        <div class="meta-label">RES</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?= formatBytes($rec['file_size'] ?? 0) ?></div>
                        <div class="meta-label">SIZE</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?= $rec['snapshot_count'] ?? 0 ?></div>
                        <div class="meta-label">SNAPS</div>
                    </div>
                </div>
                
                <?php if (isset($rec['tags'])): ?>
                <div class="tags">
                    <?php foreach (explode(',', $rec['tags']) as $tag): ?>
                    <span class="tag"><?= trim($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="recording-footer">
                    <div class="drone-info">
                        <i class="fas fa-calendar"></i>
                        <?= date('Y-m-d H:i', strtotime($rec['created_at'] ?? 'now')) ?>
                    </div>
                    <div class="action-buttons">
                        <button class="action-btn" onclick="playRecording(<?= $uniqueId ?>)" title="Play">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="action-btn" onclick="analyzeRecording(<?= $uniqueId ?>)" title="Analyze">
                            <i class="fas fa-brain"></i>
                        </button>
                        <button class="action-btn download" onclick="downloadRecording(<?= $uniqueId ?>)" title="Download">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="action-btn" onclick="showInfo(<?= $uniqueId ?>)" title="Info">
                            <i class="fas fa-info"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <button class="page-btn">4</button>
        <button class="page-btn">5</button>
        <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
    </div>

    <!-- Video Player Modal -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <h2 class="modal-title" id="modalTitle">Recording Playback</h2>
            <div class="video-player">
                <video controls id="videoPlayer" style="width: 100%;">
                    <source src="#" type="video/mp4">
                </video>
            </div>
            <div class="modal-stats" id="modalStats"></div>
            <div style="text-align: center;">
                <button class="close-modal" onclick="closeModal()">CLOSE</button>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        let searchTerm = '';

        function filterRecordings(type, btn) {
            currentFilter = type;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            applyFilters();
        }

        function searchRecordings() {
            searchTerm = document.getElementById('searchInput').value.toLowerCase();
            applyFilters();
        }

        function applyFilters() {
            const cards = document.querySelectorAll('.recording-card');
            
            cards.forEach(card => {
                let show = true;
                
                if (currentFilter !== 'all') {
                    if (currentFilter === 'threat') {
                        show = card.dataset.threat === 'true';
                    } else if (currentFilter === 'analyzed') {
                        show = card.dataset.analyzed === 'true';
                    } else {
                        show = card.dataset.type === currentFilter;
                    }
                }
                
                if (show && searchTerm) {
                    const title = card.querySelector('.recording-title').textContent.toLowerCase();
                    show = title.includes(searchTerm);
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        }

        function playRecording(id) {
            const modal = document.getElementById('videoModal');
            const player = document.getElementById('videoPlayer');
            
            modal.classList.add('active');
            showNotification(`🎬 Playing recording #${id}`);
            
            // Simulate video load
            setTimeout(() => {
                document.getElementById('modalStats').innerHTML = `
                    <div class="modal-stat"><div class="modal-stat-value">4K</div><div class="modal-stat-label">RESOLUTION</div></div>
                    <div class="modal-stat"><div class="modal-stat-value">30</div><div class="modal-stat-label">FPS</div></div>
                    <div class="modal-stat"><div class="modal-stat-value">H.264</div><div class="modal-stat-label">CODEC</div></div>
                    <div class="modal-stat"><div class="modal-stat-value">12 MB/s</div><div class="modal-stat-label">BITRATE</div></div>
                `;
            }, 500);
        }

        function analyzeRecording(id) {
            showNotification(`🧠 AI analyzing recording #${id}...`);
            setTimeout(() => {
                showNotification('✅ Analysis complete - No threats detected');
            }, 2000);
        }

        function downloadRecording(id) {
            showNotification(`⬇️ Downloading recording #${id}...`);
            setTimeout(() => {
                showNotification('✅ Download complete');
            }, 2000);
        }

        function showInfo(id) {
            const card = document.querySelector(`[data-id="${id}"]`);
            if (card) {
                const title = card.querySelector('.recording-title').textContent;
                showNotification(`ℹ️ ${title} - ID: ${id}`);
            }
        }

        function closeModal() {
            document.getElementById('videoModal').classList.remove('active');
            document.getElementById('videoPlayer').pause();
        }

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        });
    </script>
</body>
</html>
