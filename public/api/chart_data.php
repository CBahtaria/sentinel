<?php
// api_chart_data.php - Real-time chart data for UEDF Sentinel
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../src/db.php';

$response = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'drones' => [],
    'threats' => [],
    'missions' => [],
    'battery' => [],
    'timeline' => []
];

try {
    // 1. Drone status distribution (Pie Chart)
    $result = $conn->query("SELECT status, COUNT(*) as count FROM drones GROUP BY status");
    $drone_status = [];
    while ($row = $result->fetch_assoc()) {
        $drone_status[$row['status']] = (int)$row['count'];
    }
    $response['drones'] = [
        'labels' => array_keys($drone_status),
        'data' => array_values($drone_status),
        'colors' => [
            'ACTIVE' => '#00ff9d',
            'STANDBY' => '#ffbe0b',
            'MAINTENANCE' => '#ff006e',
            'DEPLOYED' => '#4cc9f0'
        ]
    ];
    
    // 2. Threat severity distribution (Bar Chart)
    $result = $conn->query("SELECT severity, COUNT(*) as count FROM threats WHERE status = 'ACTIVE' GROUP BY severity");
    $threat_severity = [];
    while ($row = $result->fetch_assoc()) {
        $threat_severity[$row['severity']] = (int)$row['count'];
    }
    $response['threats'] = [
        'labels' => array_keys($threat_severity),
        'data' => array_values($threat_severity),
        'colors' => [
            'CRITICAL' => '#ff006e',
            'HIGH' => '#ff8c00',
            'MEDIUM' => '#ffbe0b',
            'LOW' => '#4cc9f0'
        ]
    ];
    
    // 3. Mission status (Doughnut Chart)
    $result = $conn->query("SELECT status, COUNT(*) as count FROM missions GROUP BY status");
    $mission_status = [];
    while ($row = $result->fetch_assoc()) {
        $mission_status[$row['status']] = (int)$row['count'];
    }
    $response['missions'] = [
        'labels' => array_keys($mission_status),
        'data' => array_values($mission_status),
        'colors' => [
            'active' => '#00ff9d',
            'completed' => '#4cc9f0',
            'scheduled' => '#ffbe0b',
            'aborted' => '#ff006e'
        ]
    ];
    
    // 4. Drone battery levels (Horizontal Bar)
    $result = $conn->query("SELECT name, battery_level FROM drones ORDER BY battery_level DESC LIMIT 10");
    $battery_data = [];
    $battery_labels = [];
    while ($row = $result->fetch_assoc()) {
        $battery_labels[] = $row['name'];
        $battery_data[] = (int)$row['battery_level'];
    }
    $response['battery'] = [
        'labels' => $battery_labels,
        'data' => $battery_data
    ];
    
    // 5. Timeline data - threats over last 24 hours
    $result = $conn->query("SELECT 
        HOUR(detected_at) as hour,
        COUNT(*) as count 
        FROM threats 
        WHERE detected_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY HOUR(detected_at)
        ORDER BY hour");
    
    $hours = [];
    $counts = array_fill(0, 24, 0);
    while ($row = $result->fetch_assoc()) {
        $counts[(int)$row['hour']] = (int)$row['count'];
    }
    
    for ($i = 0; $i < 24; $i++) {
        $hours[] = sprintf("%02d:00", $i);
    }
    
    $response['timeline'] = [
        'labels' => $hours,
        'data' => $counts
    ];
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>