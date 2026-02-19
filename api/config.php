<?php
/**
 * UEDF SENTINEL v4.0 - API Configuration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// API密钥 (在生產環境中應該更安全地存儲)
define('API_KEY', 'uedf-sentinel-mobile-2026');
define('API_VERSION', 'v1.0');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'uedf_sentinel');
define('DB_USER', 'root');
define('DB_PASS', '');

// Response function
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode([
        'success' => $status >= 200 && $status < 300,
        'timestamp' => time(),
        'data' => $data
    ]);
    exit;
}

function sendError($message, $status = 400) {
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'timestamp' => time(),
        'error' => $message
    ]);
    exit;
}

// Authenticate API request
function authenticate() {
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? $_GET['api_key'] ?? '';
    
    if ($apiKey !== API_KEY) {
        sendError('Unauthorized - Invalid API Key', 401);
    }
    
    return true;
}

// Get database connection
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        sendError('Database connection failed: ' . $e->getMessage(), 500);
    }
}
?>
