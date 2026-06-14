<?php
/**
 * UEDF SENTINEL v4.0 - API Configuration
 */

header('Content-Type: application/json');

// CORS — allowlist only; never wildcard
$allowed_origins = ['https://sentinel.uedf.gov.sz'];
if (getenv('SENTINEL_ENV') === 'development') {
    $allowed_origins[] = 'http://localhost:8080';
    $allowed_origins[] = 'http://localhost:3000';
}
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Content-Security-Policy: default-src \'self\'');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Referrer-Policy: no-referrer');
header('X-Permitted-Cross-Domain-Policies: none');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// API key — must be set via environment variable
define('API_KEY', $_ENV['SENTINEL_API_KEY'] ?? throw new \RuntimeException('SENTINEL_API_KEY not set'));
define('API_VERSION', 'v1.0');

// Database configuration — loaded from environment; never hardcoded
define('DB_HOST', $_ENV['DB_HOST'] ?? throw new \RuntimeException('DB_HOST not set'));
define('DB_NAME', $_ENV['DB_NAME'] ?? throw new \RuntimeException('DB_NAME not set'));
define('DB_USER', $_ENV['DB_USER'] ?? throw new \RuntimeException('DB_USER not set'));
define('DB_PASS', $_ENV['DB_PASS'] ?? throw new \RuntimeException('DB_PASS not set'));

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
