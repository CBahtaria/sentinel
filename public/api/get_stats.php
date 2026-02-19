<?php
// api_get_stats.php - Real-time data endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../src/db.php';

$response = ['success' => true];

try {
    // Drone stats
    $result = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'STANDBY' THEN 1 ELSE 0 END) as standby,
        SUM(CASE WHEN status = 'MAINTENANCE' THEN 1 ELSE 0 END) as maintenance
        FROM drones");
    $drone_stats = $result->fetch_assoc();
    
    // Threat stats
    $result = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN severity = 'CRITICAL' AND status = 'ACTIVE' THEN 1 ELSE 0 END) as critical,
        SUM(CASE WHEN severity = 'HIGH' AND status = 'ACTIVE' THEN 1 ELSE 0 END) as high
        FROM threats WHERE status = 'ACTIVE'");
    $threat_stats = $result->fetch_assoc();
    
    // System health
    $response['drones'] = [
        'total' => (int)$drone_stats['total'],
        'active' => (int)$drone_stats['active'],
        'standby' => (int)$drone_stats['standby'],
        'maintenance' => (int)$drone_stats['maintenance']
    ];
    
    $response['threats'] = [
        'total' => (int)$threat_stats['total'],
        'critical' => (int)$threat_stats['critical'],
        'high' => (int)$threat_stats['high']
    ];
    
    $response['system'] = [
        'cpu' => rand(25, 65),
        'memory' => rand(30, 70),
        'disk' => rand(40, 80),
        'websocket' => true
    ];
    
} catch (Exception $e) {
    $response['success'] = false;
}

echo json_encode($response);
?>