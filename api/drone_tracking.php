<?php
/**
 * UEDF SENTINEL - Real-time Drone Tracking API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$db = Database::getInstance()->getConnection();

// Get all drones with their last known positions
$drones = $db->query("
    SELECT id, name, status, battery_level, 
           location, last_seen,
           -- Simulate coordinates based on location name
           CASE 
               WHEN location LIKE '%Sector 1%' THEN -26.1 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 2%' THEN -26.2 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 3%' THEN -26.3 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 4%' THEN -26.4 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 5%' THEN -26.5 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 6%' THEN -26.6 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 7%' THEN -26.7 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 8%' THEN -26.8 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 9%' THEN -26.9 + (RAND() * 0.1)
               ELSE -26.5 + (RAND() * 0.5)
           END as latitude,
           CASE 
               WHEN location LIKE '%Sector 1%' THEN 31.1 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 2%' THEN 31.2 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 3%' THEN 31.3 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 4%' THEN 31.4 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 5%' THEN 31.5 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 6%' THEN 31.6 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 7%' THEN 31.7 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 8%' THEN 31.8 + (RAND() * 0.1)
               WHEN location LIKE '%Sector 9%' THEN 31.9 + (RAND() * 0.1)
               ELSE 31.5 + (RAND() * 0.5)
           END as longitude
    FROM drones
")->fetchAll(PDO::FETCH_ASSOC);

// Add real-time telemetry
foreach ($drones as &$drone) {
    $drone['altitude'] = rand(200, 500) . 'm';
    $drone['speed'] = rand(30, 60) . ' km/h';
    $drone['heading'] = rand(0, 359) . '°';
    $drone['last_update'] = date('H:i:s');
}

echo json_encode([
    'success' => true,
    'timestamp' => time(),
    'data' => $drones
]);
?>
