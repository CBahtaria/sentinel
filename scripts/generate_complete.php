<?php
// This script generates the complete home.php file
$code = '<?php
require_once __DIR__ . \'/../includes/session.php\';
/**
 * BARTARIA DEFENSE SYSTEM v5.0 - COMPLETE COMMAND DASHBOARD
 * Named after Charles Bartaria - Commander of Bartarian Defence
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    
}

// Check login
if (!isset($_SESSION[\'user_id\'])) {
    header(\'Location: ?module=login\');
    exit;
}

// Get user data from session
$user_id = $_SESSION[\'user_id\'];
$username = $_SESSION[\'username\'] ?? \'COMMANDER\';
$full_name = $_SESSION[\'full_name\'] ?? \'Charles Bartaria\';
$role = $_SESSION[\'role\'] ?? \'commander\';
$two_factor_enabled = $_SESSION[\'two_factor_enabled\'] ?? false;

// Set role-based colors
$role_colors = [
    \'commander\' => \'#ff006e\',
    \'operator\' => \'#ffbe0b\',
    \'analyst\' => \'#4cc9f0\',
    \'viewer\' => \'#a0aec0\'
];
$access_color = $role_colors[$role] ?? \'#00ff9d\';

// Get statistics from database
try {
    $pdo = new PDO(\'mysql:host=localhost;dbname=bartarian_defence\', \'root\', \'\');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Main stats
    $node_count = $pdo->query("SELECT COUNT(*) FROM nodes")->fetchColumn() ?: 15;
    $threat_count = $pdo->query("SELECT COUNT(*) FROM threats WHERE status = \'ACTIVE\'")->fetchColumn() ?: 5;
    $drone_count = $pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn() ?: 15;
    $active_drones = $pdo->query("SELECT COUNT(*) FROM drones WHERE status = \'ACTIVE\'")->fetchColumn() ?: 8;
    $critical_threats = $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = \'CRITICAL\' AND status = \'ACTIVE\'")->fetchColumn() ?: 2;
    
    // Drone status
    $drone_status = [\'ACTIVE\' => 0, \'STANDBY\' => 0, \'MAINTENANCE\' => 0];
    $status_result = $pdo->query("SELECT status, COUNT(*) as count FROM drones GROUP BY status");
    while ($row = $status_result->fetch(PDO::FETCH_ASSOC)) {
        $drone_status[$row[\'status\']] = $row[\'count\'];
    }
    
    // Recent threats
    $recent_threats = $pdo->query("SELECT * FROM threats WHERE status = \'ACTIVE\' ORDER BY detected_at DESC LIMIT 5")->fetchAll();
    if (empty($recent_threats)) {
        $recent_threats = [
            [\'type\' => \'Unauthorized Access Attempt\', \'severity\' => \'CRITICAL\', \'location\' => \'Sector 4\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-2 minutes\'))],
            [\'type\' => \'Drone Intrusion Detected\', \'severity\' => \'HIGH\', \'location\' => \'Sector 7\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-5 minutes\'))],
            [\'type\' => \'Suspicious Network Activity\', \'severity\' => \'MEDIUM\', \'location\' => \'Sector 2\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-12 minutes\'))],
            [\'type\' => \'Perimeter Breach Attempt\', \'severity\' => \'CRITICAL\', \'location\' => \'Sector 9\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-15 minutes\'))],
            [\'type\' => \'Unusual Weather Pattern\', \'severity\' => \'LOW\', \'location\' => \'Sector 1\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-22 minutes\'))]
        ];
    }
    
    // Drone telemetry
    $drone_telemetry = $pdo->query("SELECT id, name, status, battery_level, altitude, speed FROM drones WHERE status = \'ACTIVE\' LIMIT 4")->fetchAll();
    if (empty($drone_telemetry)) {
        $drone_telemetry = [
            [\'id\' => 1, \'name\' => \'BARTARIA-1\', \'status\' => \'ACTIVE\', \'battery_level\' => 95, \'altitude\' => 150, \'speed\' => 12],
            [\'id\' => 2, \'name\' => \'BARTARIA-2\', \'status\' => \'ACTIVE\', \'battery_level\' => 87, \'altitude\' => 200, \'speed\' => 15],
            [\'id\' => 3, \'name\' => \'BARTARIA-3\', \'status\' => \'ACTIVE\', \'battery_level\' => 72, \'altitude\' => 120, \'speed\' => 10],
            [\'id\' => 4, \'name\' => \'BARTARIA-5\', \'status\' => \'ACTIVE\', \'battery_level\' => 88, \'altitude\' => 180, \'speed\' => 14]
        ];
    }
    
    // System health
    $system_health = [
        \'cpu\' => rand(25, 65),
        \'memory\' => rand(30, 70),
        \'storage\' => rand(40, 80),
        \'network\' => rand(85, 99)
    ];
    
    // Audit count
    $audit_count = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(timestamp) = CURDATE()")->fetchColumn() ?: 128;
    
    // Get active recordings
    $active_recordings = $pdo->query("SELECT COUNT(*) FROM drone_video_streams WHERE is_recording = TRUE")->fetchColumn() ?: 2;
    
} catch (Exception $e) {
    // Fallback values
    $node_count = 15;
    $threat_count = 5;
    $drone_count = 15;
    $active_drones = 8;
    $critical_threats = 2;
    $drone_status = [\'ACTIVE\' => 8, \'STANDBY\' => 4, \'MAINTENANCE\' => 3];
    $active_recordings = 2;
    $audit_count = 128;
    $system_health = [\'cpu\' => 45, \'memory\' => 62, \'storage\' => 54, \'network\' => 98];
    
    $recent_threats = [
        [\'type\' => \'Unauthorized Access Attempt\', \'severity\' => \'CRITICAL\', \'location\' => \'Sector 4\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-2 minutes\'))],
        [\'type\' => \'Drone Intrusion Detected\', \'severity\' => \'HIGH\', \'location\' => \'Sector 7\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-5 minutes\'))],
        [\'type\' => \'Suspicious Network Activity\', \'severity\' => \'MEDIUM\', \'location\' => \'Sector 2\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-12 minutes\'))],
        [\'type\' => \'Perimeter Breach Attempt\', \'severity\' => \'CRITICAL\', \'location\' => \'Sector 9\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-15 minutes\'))],
        [\'type\' => \'Unusual Weather Pattern\', \'severity\' => \'LOW\', \'location\' => \'Sector 1\', \'detected_at\' => date(\'Y-m-d H:i:s\', strtotime(\'-22 minutes\'))]
    ];
    
    $drone_telemetry = [
        [\'id\' => 1, \'name\' => \'BARTARIA-1\', \'status\' => \'ACTIVE\', \'battery_level\' => 95, \'altitude\' => 150, \'speed\' => 12],
        [\'id\' => 2, \'name\' => \'BARTARIA-2\', \'status\' => \'ACTIVE\', \'battery_level\' => 87, \'altitude\' => 200, \'speed\' => 15],
        [\'id\' => 3, \'name\' => \'BARTARIA-3\', \'status\' => \'ACTIVE\', \'battery_level\' => 72, \'altitude\' => 120, \'speed\' => 10],
        [\'id\' => 4, \'name\' => \'BARTARIA-5\', \'status\' => \'ACTIVE\', \'battery_level\' => 88, \'altitude\' => 180, \'speed\' => 14]
    ];
}

// Complete modules list
$modules = [
    \'commander\' => [
        [\'icon\' => \'fa-crown\', \'title\' => \'COMMAND CENTER\', \'desc\' => \'Full tactical control and unit deployment\', \'link\' => \'?module=dashboard\', \'badge\' => \'COMMANDER\'],
        [\'icon\' => \'fa-robot\', \'title\' => \'BARTARIA AI\', \'desc\' => \'Advanced AI command processing\', \'link\' => \'?module=ai-assistant\', \'badge\' => \'v2.0\'],
        [\'icon\' => \'fa-heartbeat\', \'title\' => \'SYSTEM HEALTH\', \'desc\' => \'Monitor system performance and status\', \'link\' => \'?module=health\', \'badge\' => \'v4.0\'],
        [\'icon\' => \'fa-satellite-dish\', \'title\' => \'DRONE TRACKING\', \'desc\' => \'Real-time drone positions and telemetry\', \'link\' => \'?module=drone_map\', \'badge\' => \'LIVE\'],
        [\'icon\' => \'fa-comments\', \'title\' => \'TEAM CHAT\', \'desc\' => \'Secure messaging between operators\', \'link\' => \'?module=chat\', \'badge\' => \'ENCRYPTED\'],
        [\'icon\' => \'fa-map-marked-alt\', \'title\' => \'MISSION PLANNER\', \'desc\' => \'Plan and schedule drone patrol routes\', \'link\' => \'?module=mission_planner\', \'badge\' => \'TACTICAL\'],
        [\'icon\' => \'fa-drone\', \'title\' => \'BARTARIA FLEET\', \'desc\' => \'Manage all surveillance drones\', \'link\' => \'?module=drones\', \'badge\' => "$active_drones ACTIVE"],
        [\'icon\' => \'fa-map\', \'title\' => \'TACTICAL MAP\', \'desc\' => \'Interactive map with all nodes\', \'link\' => \'?module=map\', \'badge\' => "$node_count NODES"],
        [\'icon\' => \'fa-brain\', \'title\' => \'THREAT MONITOR\', \'desc\' => "$threat_count active threats", \'link\' => \'?module=concurrency\', \'badge\' => \'REAL-TIME\'],
        [\'icon\' => \'fa-history\', \'title\' => \'AUDIT LOGS\', \'desc\' => \'Complete activity trail\', \'link\' => \'?module=audit\', \'badge\' => \'CLASSIFIED\'],
        [\'icon\' => \'fa-chart-line\', \'title\' => \'ANALYTICS\', \'desc\' => \'Threat intelligence and patterns\', \'link\' => \'?module=analytics\', \'badge\' => \'PREDICTIVE\'],
        [\'icon\' => \'fa-bell\', \'title\' => \'NOTIFICATIONS\', \'desc\' => \'System alerts and warnings\', \'link\' => \'?module=notifications\', \'badge\' => \'LIVE\'],
        [\'icon\' => \'fa-cog\', \'title\' => \'ADMIN PANEL\', \'desc\' => \'System configuration\', \'link\' => \'?module=admin\', \'badge\' => \'RESTRICTED\'],
        [\'icon\' => \'fa-shield-alt\', \'title\' => \'SECURITY\', \'desc\' => \'Security settings and logs\', \'link\' => \'?module=security\', \'badge\' => \'HARDENED\'],
        [\'icon\' => \'fa-video\', \'title\' => \'RECORDINGS\', \'desc\' => \'Drone footage archive\', \'link\' => \'?module=recordings\', \'badge\' => \'ARCHIVE\'],
        [\'icon\' => \'fa-file-alt\', \'title\' => \'REPORTS\', \'desc\' => \'Generate mission reports\', \'link\' => \'?module=reports\', \'badge\' => \'PDF\'],
        [\'icon\' => \'fa-sliders-h\', \'title\' => \'SETTINGS\', \'desc\' => \'User preferences\', \'link\' => \'settings_page.php\', \'badge\' => \'USER\']
    ]
];

$display_modules = $modules[$role] ?? $modules[\'commander\'];

// Default widgets
$default_widgets = [
    [\'id\' => \'weather\', \'title\' => \'WEATHER\', \'icon\' => \'fa-cloud-sun\', \'visible\' => true, \'order\' => 1],
    [\'id\' => \'threats\', \'title\' => \'THREAT MONITOR\', \'icon\' => \'fa-skull\', \'visible\' => true, \'order\' => 2],
    [\'id\' => \'drones\', \'title\' => \'DRONE STATUS\', \'icon\' => \'fa-drone\', \'visible\' => true, \'order\' => 3],
    [\'id\' => \'system\', \'title\' => \'SYSTEM HEALTH\', \'icon\' => \'fa-heartbeat\', \'visible\' => true, \'order\' => 4],
    [\'id\' => \'news\', \'title\' => \'INTEL FEED\', \'icon\' => \'fa-newspaper\', \'visible\' => true, \'order\' => 5],
    [\'id\' => \'actions\', \'title\' => \'QUICK ACTIONS\', \'icon\' => \'fa-bolt\', \'visible\' => true, \'order\' => 6]
];

// Get user\'s widget layout
$widgets = $default_widgets;
if (isset($_COOKIE["widget_layout_$user_id"])) {
    $saved_widgets = json_decode($_COOKIE["widget_layout_$user_id"], true);
    if (is_array($saved_widgets)) {
        $widgets = $saved_widgets;
    }
}

// Handle AJAX requests
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && isset($_POST[\'action\'])) {
    header(\'Content-Type: application/json\');
    
    if ($_POST[\'action\'] === \'save_layout\') {
        $layout_data = json_decode($_POST[\'layout\'], true);
        if (is_array($layout_data)) {
            setcookie("widget_layout_$user_id", json_encode($layout_data), time() + (86400 * 30), \'/\');
            echo json_encode([\'success\' => true]);
        } else {
            echo json_encode([\'success\' => false, \'error\' => \'Invalid layout data\']);
        }
        exit;
    }
    
    if ($_POST[\'action\'] === \'toggle_widget\') {
        $widget_id = $_POST[\'widget_id\'];
        $visible = $_POST[\'visible\'] === \'true\';
        
        foreach ($widgets as &$widget) {
            if ($widget[\'id\'] === $widget_id) {
                $widget[\'visible\'] = $visible;
                break;
            }
        }
        
        setcookie("widget_layout_$user_id", json_encode($widgets), time() + (86400 * 30), \'/\');
        echo json_encode([\'success\' => true]);
        exit;
    }
    
    if ($_POST[\'action\'] === \'reset_layout\') {
        setcookie("widget_layout_$user_id", json_encode($default_widgets), time() + (86400 * 30), \'/\');
        echo json_encode([\'success\' => true]);
        exit;
    }
}

// Sort widgets
usort($widgets, function($a, $b) {
    return ($a[\'order\'] ?? 999) - ($b[\'order\'] ?? 999);
});

// Weather data
function getWeatherData() {
    $api_key = \'14d7edb4684575e9c0d795bd06b51d4b\';
    $weather_data = @file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Mbabane,sz&units=metric&appid={$api_key}");
    
    if ($weather_data) {
        $weather = json_decode($weather_data, true);
        if ($weather && isset($weather[\'main\'])) {
            return [
                \'temp\' => round($weather[\'main\'][\'temp\']),
                \'feels_like\' => round($weather[\'main\'][\'feels_like\']),
                \'condition\' => ucfirst($weather[\'weather\'][0][\'description\']),
                \'humidity\' => $weather[\'main\'][\'humidity\'],
                \'wind\' => round($weather[\'wind\'][\'speed\'] * 3.6),
                \'icon\' => $weather[\'weather\'][0][\'icon\'],
                \'city\' => $weather[\'name\']
            ];
        }
    }
    
    return [
        \'temp\' => 23,
        \'feels_like\' => 22,
        \'condition\' => \'Partly Cloudy\',
        \'humidity\' => 65,
        \'wind\' => 12,
        \'icon\' => \'02d\',
        \'city\' => \'Mbabane\'
    ];
}

$weather = getWeatherData();
?>';

// Add HTML and JavaScript
$code .= '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BARTARIA DEFENSE v5.0 - COMMAND DASHBOARD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            min-height: 100vh;
            padding: 20px;
            font-family: \'Share Tech Mono\', monospace;
            background-image: radial-gradient(circle at 10% 20%, rgba(255,0,110,0.05) 0%, transparent 20%),
                              radial-gradient(circle at 90% 80%, rgba(0,255,157,0.05) 0%, transparent 20%);
        }
        
        /* Header */
        .header {
            background: rgba(10,15,28,0.95);
            border: 2px solid <?= $access_color ?>;
            padding: 20px 30px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(255,0,110,0.2);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: <?= $access_color ?>;
            filter: drop-shadow(0 0 10px <?= $access_color ?>);
            animation: pulse 2s infinite;
        }
        
        .logo h1 {
            font-family: \'Orbitron\', sans-serif;
            font-size: 1.8rem;
            color: <?= $access_color ?>;
            text-shadow: 0 0 10px <?= $access_color ?>;
            letter-spacing: 2px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .user-badge {
            padding: 8px 20px;
            background: <?= $access_color ?>20;
            border: 1px solid <?= $access_color ?>;
            color: <?= $access_color ?>;
            border-radius: 30px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .security-badge {
            padding: 4px 10px;
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .logout-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        
        .logout-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
            box-shadow: 0 0 15px #ff006e;
        }
        
        /* Quick Access Bar */
        .quick-access-bar {
            background: rgba(21,31,44,0.9);
            border: 1px solid <?= $access_color ?>;
            border-radius: 50px;
            padding: 12px 25px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            backdrop-filter: blur(10px);
        }
        
        .quick-links {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .quick-label {
            color: <?= $access_color ?>;
            font-family: "Orbitron", sans-serif;
            font-size: 0.9rem;
        }
        
        .quick-link {
            color: #a0aec0;
            text-decoration: none;
            padding: 5px 15px;
            border-radius: 30px;
            transition: 0.3s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .quick-link:hover {
            color: <?= $access_color ?>;
            background: rgba(255,255,255,0.1);
        }
        
        .quick-link.ai-glow {
            color: #00ff9d;
            border: 1px solid #ff006e;
            animation: glow 2s ease-in-out infinite;
        }
        
        .system-time {
            color: #00ff9d;
            font-family: "Share Tech Mono", monospace;
            font-size: 1.3rem;
            letter-spacing: 2px;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px #ff006e; }
            50% { box-shadow: 0 0 20px #00ff9d; }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
            border: 1px solid <?= $access_color ?>;
            padding: 25px;
            text-align: center;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255,0,110,0.3);
        }
        
        .stat-card::before {
            content: \'\';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, <?= $access_color ?>15, transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .stat-value {
            font-size: 2.8rem;
            color: <?= $access_color ?>;
            font-family: \'Orbitron\', sans-serif;
            position: relative;
        }
        
        .stat-label {
            color: #a0aec0;
            font-size: 0.85rem;
            text-transform: uppercase;
            position: relative;
        }
        
        /* Feature Tabs */
        .feature-tabs {
            display: flex;
            gap: 10px;
            margin: 25px 0;
            flex-wrap: wrap;
            background: #151f2c;
            padding: 15px;
            border-radius: 50px;
            border: 1px solid <?= $access_color ?>;
        }
        
        .feature-tab {
            padding: 10px 25px;
            background: transparent;
            border: 1px solid <?= $access_color ?>;
            color: <?= $access_color ?>;
            border-radius: 30px;
            cursor: pointer;
            font-family: \'Orbitron\', sans-serif;
            font-size: 0.9rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .feature-tab:hover, .feature-tab.active {
            background: <?= $access_color ?>;
            color: #0a0f1c;
            box-shadow: 0 0 20px <?= $access_color ?>;
        }
        
        /* Feature Panels */
        .feature-panel {
            display: none;
            margin: 20px 0;
        }
        
        .feature-panel.active {
            display: block;
        }
        
        /* Weather Panel */
        .weather-panel {
            background: #151f2c;
            border: 2px solid <?= $access_color ?>;
            border-radius: 12px;
            padding: 25px;
        }
        
        .weather-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .weather-main {
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .weather-temp-large {
            font-size: 4rem;
            color: #00ff9d;
            font-family: \'Orbitron\', sans-serif;
        }
        
        .weather-condition {
            font-size: 1.5rem;
            color: <?= $access_color ?>;
        }
        
        .weather-details-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 25px 0;
        }
        
        .weather-detail-card {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid <?= $access_color ?>40;
        }
        
        .weather-detail-card i {
            font-size: 1.5rem;
            color: <?= $access_color ?>;
            margin-bottom: 10px;
        }
        
        .weather-detail-value {
            font-size: 1.3rem;
            color: #00ff9d;
        }
        
        .radar-container {
            background: #0a0f1c;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .radar-grid {
            display: grid;
            grid-template-columns: repeat(20, 1fr);
            gap: 2px;
            height: 150px;
        }
        
        .radar-cell {
            background: <?= $access_color ?>10;
            border-radius: 2px;
            transition: 0.3s;
        }
        
        .radar-cell.active {
            background: <?= $access_color ?>;
            animation: radarPulse 2s infinite;
        }
        
        @keyframes radarPulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        
        /* Threat Panel */
        .threat-panel {
            background: #151f2c;
            border: 2px solid <?= $access_color ?>;
            border-radius: 12px;
            padding: 25px;
        }
        
        .threat-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .threat-item {
            padding: 15px;
            border-bottom: 1px solid <?= $access_color ?>40;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .threat-item:last-child {
            border-bottom: none;
        }
        
        .threat-severity {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .severity-critical { background: #ff006e40; color: #ff006e; border: 1px solid #ff006e; }
        .severity-high { background: #ff8c0040; color: #ff8c00; border: 1px solid #ff8c00; }
        .severity-medium { background: #ffbe0b40; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .severity-low { background: #4cc9f040; color: #4cc9f0; border: 1px solid #4cc9f0; }
        
        /* Drone Panel */
        .drone-panel {
            background: #151f2c;
            border: 2px solid <?= $access_color ?>;
            border-radius: 12px;
            padding: 25px;
        }
        
        .drone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .drone-card {
            background: #0a0f1c;
            border: 1px solid <?= $access_color ?>;
            border-radius: 8px;
            padding: 15px;
        }
        
        .drone-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .drone-name {
            color: <?= $access_color ?>;
            font-family: \'Orbitron\', sans-serif;
        }
        
        .drone-status {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
        }
        
        .status-active { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .status-standby { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .status-maintenance { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        
        .battery-bar {
            height: 6px;
            background: #151f2c;
            border-radius: 3px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .battery-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #00ff9d);
            border-radius: 3px;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .grid-card {
            background: #151f2c;
            border: 1px solid <?= $access_color ?>;
            border-radius: 10px;
            padding: 15px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            color: <?= $access_color ?>;
            font-family: \'Orbitron\', sans-serif;
            font-size: 0.9rem;
        }
        
        .card-header i {
            font-size: 1.2rem;
        }
        
        /* Threat List in Dashboard */
        .dashboard-threat-list {
            max-height: 180px;
            overflow-y: auto;
        }
        
        .dashboard-threat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid <?= $access_color ?>20;
            font-size: 0.8rem;
        }
        
        .dashboard-threat-item:last-child {
            border-bottom: none;
        }
        
        /* Drone Telemetry */
        .drone-telemetry {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .drone-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px;
            background: #0a0f1c;
            border-radius: 6px;
        }
        
        .drone-icon {
            width: 28px;
            height: 28px;
            background: <?= $access_color ?>20;
            border: 1px solid <?= $access_color ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: <?= $access_color ?>;
        }
        
        .drone-info {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .drone-name {
            font-size: 0.8rem;
            color: <?= $access_color ?>;
        }
        
        .drone-stats {
            display: flex;
            gap: 10px;
            font-size: 0.65rem;
            color: #a0aec0;
        }
        
        .battery-bar-small {
            width: 50px;
            height: 4px;
            background: #0a0f1c;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .battery-fill-small {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #00ff9d);
        }
        
        /* System Health */
        .health-metrics {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .metric {
            text-align: center;
        }
        
        .metric-value {
            font-size: 1.2rem;
            color: #00ff9d;
        }
        
        .metric-label {
            font-size: 0.65rem;
            color: #a0aec0;
        }
        
        /* Quick Actions */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        
        .quick-action-btn {
            text-decoration: none;
            color: #e0e0e0;
            background: #0a0f1c;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
            font-size: 0.75rem;
            border: 1px solid <?= $access_color ?>;
            transition: 0.3s;
        }
        
        .quick-action-btn:hover {
            background: <?= $access_color ?>;
            color: #0a0f1c;
        }
        
        /* Module Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0 40px;
        }
        
        .module-card {
            background: #151f2c;
            border: 1px solid <?= $access_color ?>;
            padding: 20px;
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
            border-radius: 12px;
        }
        
        .module-card:hover {
            border-color: #00ff9d;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255,0,110,0.3);
        }
        
        .module-icon {
            font-size: 2rem;
            color: <?= $access_color ?>;
            margin-bottom: 15px;
        }
        
        .module-title {
            font-family: \'Orbitron\', sans-serif;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .module-desc {
            color: #a0aec0;
            font-size: 0.85rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .module-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid <?= $access_color ?>40;
            padding-top: 12px;
        }
        
        .module-badge {
            font-size: 0.7rem;
            padding: 3px 10px;
            border: 1px solid <?= $access_color ?>;
            color: <?= $access_color ?>;
            border-radius: 20px;
        }
        
        /* Floating AI Button */
        .ai-float-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #ff006e, #00ff9d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 0 30px rgba(255,0,110,0.5);
            z-index: 9999;
            transition: 0.3s;
            animation: float 3s ease-in-out infinite;
            border: 2px solid white;
        }
        
        .ai-float-button:hover {
            transform: scale(1.1);
            box-shadow: 0 0 50px rgba(0,255,157,0.8);
        }
        
        .ai-float-button i {
            color: white;
            font-size: 30px;
        }
        
        .ai-tooltip {
            position: absolute;
            right: 80px;
            background: #151f2c;
            color: #00ff9d;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            white-space: nowrap;
            border: 1px solid #ff006e;
            opacity: 0;
            transition: 0.3s;
            pointer-events: none;
        }
        
        .ai-float-button:hover .ai-tooltip {
            opacity: 1;
            right: 85px;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .ai-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(255,0,110,0.4);
            animation: pulse 2s infinite;
            z-index: -1;
        }
        
        /* Widget Settings Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #151f2c;
            border: 2px solid <?= $access_color ?>;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-title {
            font-family: \'Orbitron\', sans-serif;
            color: <?= $access_color ?>;
            margin-bottom: 25px;
            font-size: 1.5rem;
        }
        
        .widget-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .widget-list-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            background: #0a0f1c;
            border-radius: 8px;
            border: 1px solid <?= $access_color ?>40;
        }
        
        .widget-list-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: <?= $access_color ?>;
            cursor: pointer;
        }
        
        .widget-list-item i {
            font-size: 1.2rem;
            color: <?= $access_color ?>;
            width: 25px;
        }
        
        .widget-list-item span {
            flex: 1;
            font-family: \'Orbitron\', sans-serif;
            font-size: 1rem;
        }
        
        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            color: #4a5568;
            padding: 25px;
            border-top: 1px solid <?= $access_color ?>40;
            margin-top: 20px;
        }
        
        /* Sortable */
        .sortable-ghost {
            opacity: 0.4;
            background: <?= $access_color ?>20;
            border: 2px dashed <?= $access_color ?>;
        }
        
        .sortable-drag {
            opacity: 0.8;
            transform: rotate(2deg);
        }
        
        .widget-item.hidden {
            display: none;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #00ff9d;
            color: #0a0f1c;
            padding: 12px 25px;
            border-radius: 30px;
            font-family: \'Orbitron\', sans-serif;
            font-size: 0.9rem;
            z-index: 10001;
            animation: slideIn 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0a0f1c;
        }
        
        ::-webkit-scrollbar-thumb {
            background: <?= $access_color ?>;
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #00ff9d;
        }
        
        /* WebSocket Status Indicator */
        .ws-status {
            position: fixed;
            bottom: 100px;
            right: 30px;
            background: #151f2c;
            border: 2px solid #ff006e;
            color: #ff006e;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            box-shadow: 0 0 20px rgba(255,0,110,0.3);
            font-family: \'Share Tech Mono\', monospace;
        }
        
        .ws-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .ws-indicator.connected {
            background: #00ff9d;
            animation: pulse 2s infinite;
        }
        
        .ws-indicator.disconnected {
            background: #ff006e;
        }
        
        .ws-indicator.connecting {
            background: #ffbe0b;
            animation: blink 1s infinite;
        }
        
        .ws-indicator.error {
            background: #ff006e;
            animation: blink 0.5s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .update-flash {
            animation: flash 0.5s ease;
        }
        
        @keyframes flash {
            0%, 100% { background: #151f2c; }
            50% { background: <?= $access_color ?>40; }
        }
        
        /* Animation for value changes */
        .value-changed {
            animation: valuePulse 0.5s ease;
        }
        
        @keyframes valuePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); color: #00ff9d; }
        }
        
        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .modules-grid { grid-template-columns: repeat(4, 1fr); }
        }
        
        @media (max-width: 992px) {
            .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
            .modules-grid { grid-template-columns: repeat(3, 1fr); }
            .weather-details-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-grid { grid-template-columns: 1fr; }
            .modules-grid { grid-template-columns: repeat(2, 1fr); }
            .quick-access-bar { flex-direction: column; align-items: flex-start; }
            .quick-links { justify-content: center; width: 100%; }
            .system-time { align-self: flex-end; }
            .feature-tabs { border-radius: 12px; }
            .feature-tab { width: 100%; justify-content: center; }
            .weather-details-grid { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .modules-grid { grid-template-columns: 1fr; }
            .header { flex-direction: column; text-align: center; }
            .user-info { justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Notification Container -->
    <div id="notification" style="display: none;"></div>

    <div class="header">
        <div class="logo">
            <i class="fas fa-shield-halved"></i>
            <h1>BARTARIA DEFENSE</h1>
        </div>
        <div class="user-info">
            <?php if($two_factor_enabled): ?>
            <span class="security-badge"><i class="fas fa-shield-alt"></i> 2FA</span>
            <?php endif; ?>
            <span class="user-badge">
                <i class="fas fa-crown"></i> <?= htmlspecialchars($full_name) ?>
            </span>
            <a href="?module=logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i> EXIT</a>
        </div>
    </div>

    <!-- Quick Access Bar -->
    <div class="quick-access-bar">
        <div class="quick-links">
            <span class="quick-label"><i class="fas fa-bolt"></i> BARTARIA-QUICK:</span>
            <a href="?module=drones" class="quick-link"><i class="fas fa-drone"></i> FLEET</a>
            <a href="?module=map" class="quick-link"><i class="fas fa-map"></i> MAP</a>
            <a href="?module=concurrency" class="quick-link"><i class="fas fa-brain"></i> THREATS</a>
            <a href="?module=ai-assistant" class="quick-link ai-glow"><i class="fas fa-robot"></i> BARTARIA-AI</a>
            <a href="?module=health" class="quick-link"><i class="fas fa-heartbeat"></i> HEALTH</a>
            <a href="?module=drone_map" class="quick-link"><i class="fas fa-satellite-dish"></i> TRACKING</a>
            <a href="?module=chat" class="quick-link"><i class="fas fa-comments"></i> CHAT</a>
            <a href="?module=mission_planner" class="quick-link"><i class="fas fa-map-marked-alt"></i> MISSIONS</a>
        </div>
        <div class="system-time" id="live-time"><?= date('H:i:s') ?></div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" id="node-count"><?= $node_count ?></div>
            <div class="stat-label">ACTIVE NODES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="threat-count" style="color: <?= $critical_threats > 0 ? '#ff006e' : $access_color ?>">
                <?= $threat_count ?>
            </div>
            <div class="stat-label">ACTIVE THREATS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="drone-count"><?= $drone_count ?></div>
            <div class="stat-label">TOTAL DRONES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="active-drones" style="color: #00ff9d;"><?= $active_drones ?></div>
            <div class="stat-label">DRONES ACTIVE</div>
            <small style="color: #4cc9f0;" id="stat-recordings"><?= $active_recordings ?> REC</small>
        </div>
    </div>

    <!-- Feature Tabs -->
    <div class="feature-tabs">
        <div class="feature-tab active" onclick="switchFeature(\'weather\', this)">
            <i class="fas fa-cloud-sun"></i> WEATHER RADAR
        </div>
        <div class="feature-tab" onclick="switchFeature(\'threats\', this)">
            <i class="fas fa-exclamation-triangle"></i> THREAT MONITOR
        </div>
        <div class="feature-tab" onclick="switchFeature(\'drones\', this)">
            <i class="fas fa-drone"></i> DRONE FLEET
        </div>
        <div class="feature-tab" onclick="switchFeature(\'analytics\', this)">
            <i class="fas fa-chart-line"></i> ANALYTICS
        </div>
    </div>

    <!-- Weather Panel -->
    <div id="weather-panel" class="feature-panel active">
        <div class="weather-panel">
            <div class="weather-header">
                <h2><i class="fas fa-cloud-sun" style="color: <?= $access_color ?>;"></i> LIVE WEATHER RADAR</h2>
                <span style="color: #00ff9d;" id="weather-time"><?= date('H:i:s') ?> | UPDATING</span>
            </div>
            
            <div class="weather-main">
                <div>
                    <div class="weather-temp-large" id="weather-temp"><?= $weather['temp'] ?>°C</div>
                    <div class="weather-condition" id="weather-condition"><?= $weather['condition'] ?></div>
                </div>
                <div>
                    <i class="fas fa-map-marker-alt" style="color: <?= $access_color ?>;"></i>
                    <span id="weather-city"><?= $weather['city'] ?></span>, Eswatini
                </div>
            </div>
            
            <div class="weather-details-grid">
                <div class="weather-detail-card">
                    <i class="fas fa-temperature-high"></i>
                    <div class="weather-detail-value" id="weather-feels"><?= $weather['feels_like'] ?>°C</div>
                    <div>FEELS LIKE</div>
                </div>
                <div class="weather-detail-card">
                    <i class="fas fa-tint"></i>
                    <div class="weather-detail-value" id="weather-humidity"><?= $weather['humidity'] ?>%</div>
                    <div>HUMIDITY</div>
                </div>
                <div class="weather-detail-card">
                    <i class="fas fa-wind"></i>
                    <div class="weather-detail-value" id="weather-wind"><?= $weather['wind'] ?> km/h</div>
                    <div>WIND</div>
                </div>
                <div class="weather-detail-card">
                    <i class="fas fa-compress"></i>
                    <div class="weather-detail-value">1013 hPa</div>
                    <div>PRESSURE</div>
                </div>
            </div>
            
            <div class="radar-container">
                <h3 style="margin-bottom: 15px;">PRECIPITATION RADAR</h3>
                <div class="radar-grid" id="radarGrid"></div>
                <div style="margin-top: 15px; display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
                    <span><i class="fas fa-circle" style="color: #ff006e;"></i> HEAVY</span>
                    <span><i class="fas fa-circle" style="color: #ffbe0b;"></i> MODERATE</span>
                    <span><i class="fas fa-circle" style="color: #00ff9d;"></i> LIGHT</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Threat Panel -->
    <div id="threats-panel" class="feature-panel">
        <div class="threat-panel">
            <h2 style="margin-bottom: 20px;"><i class="fas fa-exclamation-triangle" style="color: <?= $access_color ?>;"></i> ACTIVE THREAT MONITOR</h2>
            
            <div class="threat-list" id="threat-list">
                <?php foreach ($recent_threats as $threat): 
                    $severity = $threat['severity'] ?? 'MEDIUM';
                    $severity_class = 'severity-' . strtolower($severity);
                    $time = date('H:i', strtotime($threat['detected_at'] ?? 'now'));
                ?>
                <div class="threat-item">
                    <div>
                        <strong><?= htmlspecialchars($threat['type'] ?? 'Unknown Threat') ?></strong>
                        <div style="font-size: 0.8rem; color: #a0aec0;"><?= $threat['location'] ?? 'Unknown' ?> • <?= $time ?></div>
                    </div>
                    <span class="threat-severity <?= $severity_class ?>"><?= $severity ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Drone Panel -->
    <div id="drones-panel" class="feature-panel">
        <div class="drone-panel">
            <h2 style="margin-bottom: 20px;"><i class="fas fa-drone" style="color: <?= $access_color ?>;"></i> BARTARIA FLEET STATUS</h2>
            
            <div class="drone-grid" id="drone-grid">
                <?php 
                $drone_list = [
                    ['name' => 'BARTARIA-1', 'status' => 'ACTIVE', 'battery' => 95],
                    ['name' => 'BARTARIA-2', 'status' => 'ACTIVE', 'battery' => 87],
                    ['name' => 'BARTARIA-3', 'status' => 'STANDBY', 'battery' => 100],
                    ['name' => 'BARTARIA-4', 'status' => 'MAINTENANCE', 'battery' => 45],
                    ['name' => 'BARTARIA-5', 'status' => 'ACTIVE', 'battery' => 72],
                    ['name' => 'BARTARIA-6', 'status' => 'ACTIVE', 'battery' => 88]
                ];
                
                foreach ($drone_list as $drone): 
                    $status = $drone['status'];
                    $battery = $drone['battery'];
                    $status_class = 'status-' . strtolower($status);
                ?>
                <div class="drone-card">
                    <div class="drone-header">
                        <span class="drone-name"><i class="fas fa-drone"></i> <?= $drone['name'] ?></span>
                        <span class="drone-status <?= $status_class ?>"><?= $status ?></span>
                    </div>
                    <div class="battery-bar">
                        <div class="battery-fill" style="width: <?= $battery ?>%"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                        <span style="color: #a0aec0;">Battery</span>
                        <span style="color: #00ff9d;"><?= $battery ?>%</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Analytics Panel -->
    <div id="analytics-panel" class="feature-panel">
        <div class="analytics-panel">
            <h2 style="margin-bottom: 20px;"><i class="fas fa-chart-line" style="color: <?= $access_color ?>;"></i> BARTARIA ANALYTICS</h2>
            
            <div style="background: #0a0f1c; padding: 20px; border-radius: 8px;">
                <canvas id="analyticsChart" style="width:100%; height:300px;"></canvas>
            </div>
            
            <div class="analytics-stats">
                <div class="analytics-stat">
                    <div class="analytics-stat-value">+23%</div>
                    <div class="analytics-stat-label">Threat Increase</div>
                </div>
                <div class="analytics-stat">
                    <div class="analytics-stat-value">87%</div>
                    <div class="analytics-stat-label">System Uptime</div>
                </div>
                <div class="analytics-stat">
                    <div class="analytics-stat-value" id="stat-audit-count"><?= number_format($audit_count) ?></div>
                    <div class="analytics-stat-label">Events Today</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid -->
    <h2 style="margin: 25px 0 10px; font-family: \'Orbitron\'; font-size: 1.2rem;">SYSTEM OVERVIEW</h2>
    
    <div class="dashboard-grid">
        <!-- Weather Card -->
        <div class="grid-card">
            <div class="card-header"><span><i class="fas fa-cloud-sun"></i> WEATHER</span><span id="card-time"><?= date('H:i') ?></span></div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <i class="fas fa-sun" style="font-size: 2rem; color: #ffbe0b;"></i>
                <div>
                    <div style="font-size: 1.8rem; color: #00ff9d;" id="card-temp"><?= $weather['temp'] ?>°C</div>
                    <div style="font-size: 0.7rem; color: #a0aec0;" id="card-condition"><?= $weather['condition'] ?></div>
                </div>
                <div style="margin-left: auto;">
                    <div><i class="fas fa-wind"></i> <span id="card-wind"><?= $weather['wind'] ?></span> km/h</div>
                    <div><i class="fas fa-tint"></i> <span id="card-humidity"><?= $weather['humidity'] ?></span>%</div>
                </div>
            </div>
        </div>

        <!-- Threat Summary Card -->
        <div class="grid-card">
            <div class="card-header"><span><i class="fas fa-exclamation-triangle"></i> THREATS</span><span id="card-threat-count"><?= $threat_count ?> ACTIVE</span></div>
            <div class="dashboard-threat-list" id="dashboard-threat-list">
                <?php foreach (array_slice($recent_threats, 0, 3) as $threat): 
                    $sev = strtolower($threat['severity'] ?? 'medium');
                ?>
                <div class="dashboard-threat-item">
                    <span><?= substr(htmlspecialchars($threat['type'] ?? 'Unknown'), 0, 20) ?>...</span>
                    <span class="threat-severity severity-<?= $sev ?>" style="padding: 2px 6px; font-size: 0.6rem;"><?= $threat['severity'] ?? 'MEDIUM' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Drone Status Card -->
        <div class="grid-card">
            <div class="card-header"><span><i class="fas fa-drone"></i> DRONES</span><span id="card-drone-active"><?= $drone_status['ACTIVE'] ?? 8 ?> ACTIVE</span></div>
            <div class="drone-telemetry" id="drone-telemetry">
                <?php foreach (array_slice($drone_telemetry, 0, 3) as $drone): ?>
                <div class="drone-row">
                    <div class="drone-icon"><i class="fas fa-drone"></i></div>
                    <div class="drone-info">
                        <span class="drone-name"><?= $drone['name'] ?></span>
                        <div class="drone-stats">
                            <span><i class="fas fa-bolt"></i> <span class="drone-battery"><?= $drone['battery_level'] ?></span>%</span>
                            <span><i class="fas fa-arrow-up"></i> <?= $drone['altitude'] ?>m</span>
                        </div>
                    </div>
                    <div class="battery-bar-small"><div class="battery-fill-small" style="width: <?= $drone['battery_level'] ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- System Health Card -->
        <div class="grid-card">
            <div class="card-header"><span><i class="fas fa-heartbeat"></i> SYSTEM</span><span>ONLINE</span></div>
            <div class="health-metrics">
                <div class="metric"><div class="metric-value" id="sys-cpu"><?= $system_health['cpu'] ?>%</div><div class="metric-label">CPU</div></div>
                <div class="metric"><div class="metric-value" id="sys-memory"><?= $system_health['memory'] ?>%</div><div class="metric-label">RAM</div></div>
                <div class="metric"><div class="metric-value" id="sys-storage"><?= $system_health['storage'] ?>%</div><div class="metric-label">STORAGE</div></div>
                <div class="metric"><div class="metric-value" id="sys-network"><?= $system_health['network'] ?>%</div><div class="metric-label">NET</div></div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="grid-card">
            <div class="card-header"><span><i class="fas fa-bolt"></i> ACTIONS</span></div>
            <div class="quick-actions-grid">
                <a href="?module=drone-control" class="quick-action-btn"><i class="fas fa-play"></i> Launch</a>
                <a href="?module=recordings" class="quick-action-btn"><i class="fas fa-video"></i> Recordings</a>
                <a href="?module=threats" class="quick-action-btn"><i class="fas fa-search"></i> Scan</a>
                <a href="?module=reports" class="quick-action-btn"><i class="fas fa-chart-line"></i> Report</a>
            </div>
        </div>

        <!-- Audit Summary Card -->
        <div class="grid-card">
            <div class="card-header"><span><i class="fas fa-history"></i> AUDIT</span><span id="card-audit"><?= number_format($audit_count) ?> TODAY</span></div>
            <div style="font-size: 0.75rem; color: #a0aec0; margin-bottom: 8px;">Last Events:</div>
            <div style="font-size: 0.7rem;" id="audit-events">
                <div><i class="fas fa-circle" style="color: #00ff9d; font-size: 0.4rem;"></i> Login: <?= date('H:i', strtotime('-2 min')) ?></div>
                <div><i class="fas fa-circle" style="color: #ff006e; font-size: 0.4rem;"></i> Threat: <?= date('H:i', strtotime('-5 min')) ?></div>
                <div><i class="fas fa-circle" style="color: #ffbe0b; font-size: 0.4rem;"></i> Drone: <?= date('H:i', strtotime('-8 min')) ?></div>
            </div>
        </div>
    </div>

    <!-- Command Modules -->
    <h2 style="margin: 25px 0 10px; font-family: \'Orbitron\'; font-size: 1.2rem;">BARTARIA COMMAND MODULES</h2>
    
    <div class="modules-grid">
        <?php foreach ($display_modules as $module): ?>
        <a href="<?= $module['link'] ?>" class="module-card">
            <div class="module-icon"><i class="fas <?= $module['icon'] ?>"></i></div>
            <div class="module-title"><?= $module['title'] ?></div>
            <div class="module-desc"><?= $module['desc'] ?></div>
            <div class="module-footer">
                <span class="module-badge"><?= $module['badge'] ?></span>
                <span>ACCESS →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Widget Settings Modal -->
    <div class="modal" id="widgetModal">
        <div class="modal-content">
            <h2 class="modal-title">WIDGET SETTINGS</h2>
            <div class="widget-list">
                <?php foreach ($widgets as $widget): ?>
                    <label class="widget-list-item">
                        <input type="checkbox" class="widget-checkbox" 
                               data-widget-id="<?= $widget['id'] ?>"
                               <?= $widget['visible'] ? 'checked' : '' ?>>
                        <i class="fas <?= $widget['icon'] ?>"></i>
                        <span><?= $widget['title'] ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="closeModal()">CANCEL</button>
                <button class="btn btn-primary" onclick="applyWidgetChanges()">APPLY</button>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>© <?= date('Y') ?> BARTARIA DEFENSE - Named after Charles Bartaria</p>
        <p>UMBUTFO ESWATINI DEFENCE FORCE | CLASSIFICATION: TOP SECRET</p>
    </div>

    <!-- Floating AI Assistant Button -->
    <div class="ai-float-button" onclick="window.location.href=\'?module=ai-assistant\'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot"></i>
        <span class="ai-tooltip">Ask Bartaria AI</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        // Initialize Sortable
        const grid = document.getElementById(\'widget-grid\');
        let sortable = null;
        
        if (grid) {
            sortable = new Sortable(grid, {
                animation: 200,
                ghostClass: \'sortable-ghost\',
                dragClass: \'sortable-drag\',
                handle: \'.fa-grip-vertical\',
                onEnd: function() {
                    showNotification(\'Layout changed - click SAVE LAYOUT to keep changes\');
                }
            });
        }

        // Update time
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString();
            const timeElement = document.getElementById(\'live-time\');
            if (timeElement) timeElement.textContent = timeStr;
            document.getElementById(\'weather-time\').textContent = now.toLocaleTimeString() + \' | UPDATING\';
            document.getElementById(\'card-time\').textContent = now.getHours().toString().padStart(2,\'0\') + \':\' + now.getMinutes().toString().padStart(2,\'0\');
        }
        setInterval(updateTime, 1000);

        // Feature switching
        function switchFeature(feature, element) {
            document.querySelectorAll(\'.feature-tab\').forEach(tab => {
                tab.classList.remove(\'active\');
            });
            element.classList.add(\'active\');
            
            document.querySelectorAll(\'.feature-panel\').forEach(panel => {
                panel.classList.remove(\'active\');
            });
            document.getElementById(feature + \'-panel\').classList.add(\'active\');
        }

        // Generate radar
        function generateRadar() {
            const radar = document.getElementById(\'radarGrid\');
            if (!radar) return;
            
            radar.innerHTML = \'\';
            for (let i = 0; i < 200; i++) {
                const cell = document.createElement(\'div\');
                cell.className = \'radar-cell\';
                if (Math.random() > 0.7) {
                    cell.classList.add(\'active\');
                }
                radar.appendChild(cell);
            }
        }
        generateRadar();
        setInterval(generateRadar, 5000);

        // Analytics chart
        const ctx = document.getElementById(\'analyticsChart\')?.getContext(\'2d\');
        if (ctx) {
            new Chart(ctx, {
                type: \'line\',
                data: {
                    labels: [\'00:00\', \'04:00\', \'08:00\', \'12:00\', \'16:00\', \'20:00\', \'Now\'],
                    datasets: [{
                        label: \'Threat Level\',
                        data: [12, 19, 15, 25, 32, 28, 35],
                        borderColor: \'#ff006e\',
                        backgroundColor: \'rgba(255,0,110,0.1)\',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            labels: { 
                                color: \'#00ff9d\',
                                font: { family: \'Share Tech Mono\' }
                            } 
                        }
                    },
                    scales: {
                        y: { 
                            grid: { color: \'#ff006e20\' }, 
                            ticks: { color: \'#00ff9d\' } 
                        },
                        x: { 
                            grid: { color: \'#ff006e20\' }, 
                            ticks: { color: \'#00ff9d\' } 
                        }
                    }
                }
            });
        }

        // Show notification
        function showNotification(message, isSuccess = true) {
            const notification = document.createElement(\'div\');
            notification.className = \'notification\';
            notification.style.background = isSuccess ? \'#00ff9d\' : \'#ff006e\';
            notification.style.color = \'#0a0f1c\';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Save layout
        function saveLayout() {
            const widgets = [];
            document.querySelectorAll(\'.widget-item\').forEach(item => {
                widgets.push({
                    id: item.dataset.widgetId,
                    visible: true
                });
            });
            
            // Get current widget order
            const order = widgets.map(w => w.id);
            
            // Update widget order in existing widgets array
            const currentWidgets = <?= json_encode($widgets) ?>;
            const updatedWidgets = [];
            
            // First add visible widgets in new order
            order.forEach(widgetId => {
                const widget = currentWidgets.find(w => w.id === widgetId);
                if (widget) {
                    updatedWidgets.push({
                        ...widget,
                        visible: true
                    });
                }
            });
            
            // Then add hidden widgets
            currentWidgets.forEach(widget => {
                if (!order.includes(widget.id) || !widget.visible) {
                    updatedWidgets.push({
                        ...widget,
                        visible: false
                    });
                }
            });
            
            // Send to server
            fetch(window.location.href, {
                method: \'POST\',
                headers: {
                    \'Content-Type\': \'application/x-www-form-urlencoded\',
                },
                body: \'action=save_layout&layout=\' + encodeURIComponent(JSON.stringify(updatedWidgets))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(\'Layout saved successfully!\');
                } else {
                    showNotification(\'Error saving layout\', false);
                }
            })
            .catch(error => {
                showNotification(\'Error saving layout\', false);
            });
        }

        // Reset layout
        function resetLayout() {
            if (confirm(\'Reset dashboard to default layout?\')) {
                fetch(window.location.href, {
                    method: \'POST\',
                    headers: {
                        \'Content-Type\': \'application/x-www-form-urlencoded\',
                    },
                    body: \'action=reset_layout\'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(\'Layout reset successfully!\');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                });
            }
        }

        // Toggle widget visibility
        function toggleWidget(widgetId, visible) {
            const widget = document.querySelector(`[data-widget-id="${widgetId}"]`);
            if (widget) {
                widget.style.display = visible ? \'block\' : \'none\';
            }
            
            fetch(window.location.href, {
                method: \'POST\',
                headers: {
                    \'Content-Type\': \'application/x-www-form-urlencoded\',
                },
                body: `action=toggle_widget&widget_id=${widgetId}&visible=${visible}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(visible ? \'Widget shown\' : \'Widget hidden\');
                }
            });
        }

        // Show widget settings modal
        function showWidgetSettings() {
            document.getElementById(\'widgetModal\').classList.add(\'active\');
        }

        // Close modal
        function closeModal() {
            document.getElementById(\'widgetModal\').classList.remove(\'active\');
        }

        // Apply widget changes
        function applyWidgetChanges() {
            const checkboxes = document.querySelectorAll(\'.widget-checkbox\');
            const currentWidgets = <?= json_encode($widgets) ?>;
            const updatedWidgets = [];
            
            checkboxes.forEach(checkbox => {
                const widgetId = checkbox.dataset.widgetId;
                const isVisible = checkbox.checked;
                const widget = currentWidgets.find(w => w.id === widgetId);
                
                if (widget) {
                    updatedWidgets.push({
                        ...widget,
                        visible: isVisible
                    });
                    
                    // Update UI
                    const widgetElement = document.querySelector(`[data-widget-id="${widgetId}"]`);
                    if (widgetElement) {
                        widgetElement.style.display = isVisible ? \'block\' : \'none\';
                    }
                }
            });
            
            // Send to server
            fetch(window.location.href, {
                method: \'POST\',
                headers: {
                    \'Content-Type\': \'application/x-www-form-urlencoded\',
                },
                body: \'action=save_layout&layout=\' + encodeURIComponent(JSON.stringify(updatedWidgets))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(\'Widget settings updated!\');
                    closeModal();
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById(\'widgetModal\');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Keyboard shortcuts
        document.addEventListener(\'keydown\', function(e) {
            if (e.altKey && e.key === \'a\') window.location.href = \'?module=ai-assistant\';
            if (e.altKey && e.key === \'d\') window.location.href = \'?module=drone-control\';
            if (e.altKey && e.key === \'r\') window.location.href = \'?module=recordings\';
            if (e.altKey && e.key === \'p\') window.location.href = \'?module=predictive\';
            if (e.altKey && e.key === \'t\') window.location.href = \'?module=threat-monitor\';
        });
    </script>

<!-- Enhanced WebSocket Manager -->
<script>
class SentinelWebSocketManager {
    constructor(userData) {
        this.ws = null;
        this.userData = userData || { user_id: \'\', username: \'guest\', role: \'viewer\' };
        this.status = document.querySelector(\'.ws-status\') || this.createStatusElement();
        this.reconnectAttempts = 0;
        this.maxReconnect = 10;
        this.messageHandlers = {};
        this.channels = new Set();
        this.heartbeatInterval = null;
        
        this.connect();
    }
    
    createStatusElement() {
        const el = document.createElement(\'div\');
        el.className = \'ws-status\';
        el.innerHTML = \'<span class="ws-indicator connecting"></span> CONNECTING...\';
        document.body.appendChild(el);
        return el;
    }
    
    connect() {
        try {
            this.ws = new WebSocket(\'ws://172.20.10.3:8081\');
            
            this.ws.onopen = () => {
                this.updateStatus(\'connected\', \'LIVE\');
                this.reconnectAttempts = 0;
                
                this.send(\'auth\', {
                    user_id: this.userData.user_id,
                    username: this.userData.username,
                    role: this.userData.role
                });
                
                if (this.channels.size > 0) {
                    this.send(\'subscribe\', { channels: Array.from(this.channels) });
                }
                
                this.startHeartbeat();
                this.trigger(\'connected\');
            };
            
            this.ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            };
            
            this.ws.onclose = () => {
                this.updateStatus(\'disconnected\', \'OFFLINE\');
                this.stopHeartbeat();
                this.trigger(\'disconnected\');
                
                if (this.reconnectAttempts < this.maxReconnect) {
                    this.reconnectAttempts++;
                    setTimeout(() => this.connect(), 5000);
                }
            };
            
            this.ws.onerror = (error) => {
                console.error(\'WebSocket error:\', error);
                this.updateStatus(\'error\', \'ERROR\');
                this.trigger(\'error\', error);
            };
            
        } catch (error) {
            console.error(\'Connection failed:\', error);
            this.updateStatus(\'error\', \'FAILED\');
        }
    }
    
    updateStatus(state, text) {
        if (!this.status) return;
        
        const colors = {
            connected: \'#00ff9d\',
            disconnected: \'#ff006e\',
            error: \'#ff006e\',
            connecting: \'#ffbe0b\'
        };
        
        this.status.innerHTML = `<span class="ws-indicator ${state}"></span> ${text}`;
        this.status.style.borderColor = colors[state] || \'#ff006e\';
        this.status.style.color = colors[state] || \'#ff006e\';
    }
    
    send(type, payload = {}) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({ type, payload }));
        }
    }
    
    handleMessage(data) {
        this.updateStatus(\'connected\', \'UPDATED\');
        setTimeout(() => this.updateStatus(\'connected\', \'LIVE\'), 1000);
        
        switch(data.type) {
            case \'welcome\':
                console.log(\'✅ Connected to server at\', data.formatted_time);
                break;
            case \'auth_success\':
                console.log(\'✅ Authenticated:\', data.message);
                this.showNotification(\'✅ Connected\', `Welcome ${this.userData.username}`, \'success\');
                break;
            case \'drone_command_response\':
                this.showNotification(\'🚁 Drone Command\', `Command ${data.command} sent to drone ${data.drone_id}`, \'info\');
                break;
            case \'threat_alert\':
                this.showNotification(\'⚠️ \' + data.title, data.message, \'alert\');
                break;
            case \'new_message\':
                // Handle new chat message
                if (typeof window.handleNewChatMessage === \'function\') {
                    window.handleNewChatMessage(data.data);
                }
                break;
        }
        
        if (this.messageHandlers[data.type]) {
            this.messageHandlers[data.type].forEach(handler => handler(data));
        }
    }
    
    subscribe(channels) {
        if (!Array.isArray(channels)) channels = [channels];
        channels.forEach(c => this.channels.add(c));
        this.send(\'subscribe\', { channels });
    }
    
    on(messageType, handler) {
        if (!this.messageHandlers[messageType]) {
            this.messageHandlers[messageType] = [];
        }
        this.messageHandlers[messageType].push(handler);
    }
    
    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => this.send(\'heartbeat\'), 30000);
    }
    
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }
    
    showNotification(title, message, type = \'info\') {
        const colors = { alert: \'#ff006e\', info: \'#00ff9d\', success: \'#00ff9d\', warning: \'#ffbe0b\' };
        
        const notif = document.createElement(\'div\');
        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #151f2c;
            border: 2px solid ${colors[type]};
            color: #00ff9d;
            padding: 15px 25px;
            border-radius: 8px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
            box-shadow: 0 0 30px rgba(255,0,110,0.3);
            cursor: pointer;
            font-family: \'Share Tech Mono\', monospace;
        `;
        notif.innerHTML = `<strong style="color: ${colors[type]};">${title}</strong><p style="margin-top:5px;margin-bottom:0;">${message}</p>`;
        notif.onclick = () => notif.remove();
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 5000);
    }
    
    trigger(event, data) {
        window.dispatchEvent(new CustomEvent(`websocket:${event}`, { detail: data }));
    }
}

// Initialize WebSocket manager
document.addEventListener(\'DOMContentLoaded\', () => {
    const userData = {
        user_id: document.querySelector(\'meta[name="user-id"]\')?.getAttribute(\'content\') || \'\',
        username: document.querySelector(\'meta[name="username"]\')?.getAttribute(\'content\') || \'guest\',
        role: document.querySelector(\'meta[name="user-role"]\')?.getAttribute(\'content\') || \'viewer\'
    };
    
    window.wsManager = new SentinelWebSocketManager(userData);
    
    window.wsManager.on(\'auth_success\', () => {
        window.wsManager.subscribe([\'drones\', \'threats\', \'alerts\', \'system\', \'chat\']);
    });
});

// Real-time Dashboard Updates
function updateDashboard() {
    fetch(\'../api_get_stats.php\')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update drone stats
                updateStat(\'node-count\', data.drones.total);
                updateStat(\'drone-count\', data.drones.total);
                updateStat(\'active-drones\', data.drones.active);
                updateStat(\'drone-active-count\', data.drones.active);
                updateStat(\'drone-standby-count\', data.drones.standby);
                updateStat(\'drone-maintenance-count\', data.drones.maintenance);
                
                // Update threat stats
                updateStat(\'threat-count\', data.threats.total);
                
                // Update system health
                updateProgressBar(\'cpu\', data.system.cpu);
                updateProgressBar(\'memory\', data.system.memory);
                updateProgressBar(\'disk\', data.system.disk);
                
                // Update WebSocket status
                const wsIndicator = document.querySelector(\'.ws-indicator\');
                if (wsIndicator) {
                    if (data.system.websocket) {
                        wsIndicator.className = \'ws-indicator connected\';
                        if (wsIndicator.nextSibling) wsIndicator.nextSibling.textContent = \'LIVE\';
                    } else {
                        wsIndicator.className = \'ws-indicator disconnected\';
                        if (wsIndicator.nextSibling) wsIndicator.nextSibling.textContent = \'OFFLINE\';
                    }
                }
                
                // Update recent alerts
                if (data.recent_alerts && data.recent_alerts.length > 0) {
                    updateAlerts(data.recent_alerts);
                }
                
                // Flash effect to show update
                document.body.classList.add(\'update-flash\');
                setTimeout(() => {
                    document.body.classList.remove(\'update-flash\');
                }, 200);
            }
        })
        .catch(error => console.error(\'Error updating dashboard:\', error));
}

function updateStat(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        // Add animation if value changed
        if (element.textContent != value) {
            element.classList.add(\'value-changed\');
            setTimeout(() => element.classList.remove(\'value-changed\'), 500);
        }
        element.textContent = value;
    }
}

function updateProgressBar(type, value) {
    const bar = document.getElementById(type + \'-bar\');
    const valueEl = document.getElementById(type + \'-value\');
    if (bar) bar.style.width = value + \'%\';
    if (valueEl) {
        valueEl.textContent = value + \'%\';
        // Change color based on value
        if (value > 80) valueEl.style.color = \'#ff006e\';
        else if (value > 60) valueEl.style.color = \'#ffbe0b\';
        else valueEl.style.color = \'#00ff9d\';
    }
}

function updateAlerts(alerts) {
    const alertList = document.getElementById(\'threat-list\');
    if (!alertList) return;
    
    if (alerts.length > 0) {
        let html = \'\';
        alerts.forEach(alert => {
            let severityClass = \'severity-medium\';
            if (alert.severity === \'CRITICAL\') severityClass = \'severity-critical\';
            else if (alert.severity === \'HIGH\') severityClass = \'severity-high\';
            
            html += `
                <div class="threat-item">
                    <span>${alert.description ? alert.description.substring(0, 30) : \'Unknown threat\'}...</span>
                    <span class="threat-severity ${severityClass}">${alert.severity || \'UNKNOWN\'}</span>
                </div>
            `;
        });
        alertList.innerHTML = html;
    }
}

// Update every 10 seconds
setInterval(updateDashboard, 10000);

// Initial update
setTimeout(updateDashboard, 1000);
</script>
</body>
</html>
';

// Save the complete file
file_put_contents('C:/xampp/htdocs/sentinel/modules/home.php', $code);
echo "Complete home.php file generated successfully!\n";
echo "File size: " . filesize('C:/xampp/htdocs/sentinel/modules/home.php') . " bytes\n";
?>