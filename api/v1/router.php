<?php
/**
 * UEDF SENTINEL v5.0 - API v1 Router
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$endpoint = $_GET['endpoint'] ?? '';

switch($endpoint) {
    case 'drones':
        echo json_encode(['status' => 'success', 'data' => ['drones' => []]]);
        break;
    case 'threats':
        echo json_encode(['status' => 'success', 'data' => ['threats' => []]]);
        break;
    default:
        echo json_encode([
            'api_version' => 'v1',
            'status' => 'operational',
            'endpoints' => ['drones', 'threats', 'users', 'nodes']
        ]);
}
?>
