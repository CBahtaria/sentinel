<?php
/**
 * UEDF SENTINEL - Missions API
 */

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? '';

switch($action) {
    case 'save':
        saveMission();
        break;
    case 'get':
        getMission();
        break;
    case 'list':
        listMissions();
        break;
    case 'delete':
        deleteMission();
        break;
    case 'log':
        addLog();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function saveMission() {
    global $db;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $type = $data['type'] ?? 'patrol';
    $drone_id = $data['drone_id'] ?? null;
    $altitude = $data['altitude'] ?? 100;
    $speed = $data['speed'] ?? 30;
    $description = $data['description'] ?? '';
    $waypoints = $data['waypoints'] ?? [];
    
    if (empty($name) || empty($waypoints)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and waypoints required']);
        return;
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO missions (name, type, description, waypoints, altitude, speed, created_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')
        ");
        
        $stmt->execute([
            $name,
            $type,
            $description,
            json_encode($waypoints),
            $altitude,
            $speed,
            $_SESSION['user_id']
        ]);
        
        $missionId = $db->lastInsertId();
        
        // Also save individual waypoints
        $stmt2 = $db->prepare("
            INSERT INTO mission_waypoints (mission_id, sequence, latitude, longitude, altitude, speed)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($waypoints as $index => $wp) {
            $stmt2->execute([
                $missionId,
                $index + 1,
                $wp['lat'],
                $wp['lng'],
                $wp['alt'] ?? $altitude,
                $wp['speed'] ?? $speed
            ]);
        }
        
        echo json_encode(['success' => true, 'mission_id' => $missionId]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getMission() {
    global $db;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $db->prepare("SELECT * FROM missions WHERE id = ?");
    $stmt->execute([$id]);
    $mission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mission) {
        $mission['waypoints'] = json_decode($mission['waypoints'], true);
        echo json_encode(['success' => true, 'mission' => $mission]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Mission not found']);
    }
}

function listMissions() {
    global $db;
    
    $stmt = $db->query("
        SELECT m.*, d.name as drone_name,
        (SELECT COUNT(*) FROM mission_logs WHERE mission_id = m.id) as log_count
        FROM missions m
        LEFT JOIN drones d ON m.assigned_drone_id = d.id
        ORDER BY m.created_at DESC
    ");
    
    $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'missions' => $missions]);
}

function deleteMission() {
    global $db;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $db->prepare("DELETE FROM missions WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
}

function addLog() {
    global $db;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $db->prepare("
        INSERT INTO mission_logs (mission_id, drone_id, log_type, message, position_lat, position_lng, altitude, battery)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['mission_id'],
        $data['drone_id'] ?? null,
        $data['type'] ?? 'event',
        $data['message'] ?? '',
        $data['lat'] ?? null,
        $data['lng'] ?? null,
        $data['altitude'] ?? null,
        $data['battery'] ?? null
    ]);
    
    echo json_encode(['success' => true]);
}
?>
