<?php
/**
 * UEDF SENTINEL v5.0 - API v1 Router
 */

header('Content-Type: application/json');
header('X-API-Deprecated: 2026-09-01');
header('X-API-Successor: /api/v2');
$_cors_allowed = ['https://sentinel.uedf.gov.sz'];
if (getenv('SENTINEL_ENV') === 'development') { $_cors_allowed[] = 'http://localhost:8080'; $_cors_allowed[] = 'http://localhost:3000'; }
$_cors_origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($_cors_origin, $_cors_allowed, true)) { header('Access-Control-Allow-Origin: ' . $_cors_origin); header('Vary: Origin'); }
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
    case 'lce':
        require __DIR__ . '/lce.php';
        break;
    case 'ecosystem':
        require __DIR__ . '/ecosystem.php';
        break;
    case 'adaptive':
        require __DIR__ . '/adaptive.php';
        break;
    default:
        echo json_encode([
            'api_version' => 'v1',
            'status' => 'operational',
            'endpoints' => ['drones', 'threats', 'users', 'nodes', 'lce', 'ecosystem', 'adaptive']
        ]);
}
?>
