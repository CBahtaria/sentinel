<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$type = $_GET['type'] ?? 'status';

if ($type === 'status') {
    $response = [
        'success' => true,
        'data' => [
            'total_drones' => 15,
            'active_drones' => 10,
            'active_threats' => 5,
            'total_nodes' => 15,
            'active_nodes' => 15,
            'total_users' => 4,
            'cpu_usage' => 45,
            'memory_usage' => 55,
            'disk_usage' => 62,
            'uptime' => '15 days',
            'api_version' => 'v1.0',
            'server_time' => date('Y-m-d H:i:s')
        ]
    ];
} else {
    $response = [
        'success' => false,
        'error' => 'Invalid type'
    ];
}

echo json_encode($response);
exit;
?>
