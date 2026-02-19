<?php
/**
 * UEDF SENTINEL v5.0 - Unified API Gateway
 * Handles all API requests with improved routing, security, and error handling
 */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set secure headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS headers
$allowed_origins = [
    'http://localhost',
    'https://yourdomain.com',
    'http://localhost:3000' // Add your frontend URLs
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$origin}");
} else {
    header('Access-Control-Allow-Origin: *'); // Fallback, restrict in production
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // 24 hours cache for preflight requests

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize response array
$response = [
    'success' => false,
    'data' => null,
    'error' => null,
    'timestamp' => time(),
    'version' => '5.0'
];

try {
    // Parse request
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Log request (optional, remove in production)
    error_log("API Request: {$request_method} {$request_uri}");
    
    // Get request body for POST/PUT/PATCH
    $input_data = [];
    if (in_array($request_method, ['POST', 'PUT', 'PATCH'])) {
        $input_json = file_get_contents('php://input');
        if (!empty($input_json)) {
            $input_data = json_decode($input_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON payload: ' . json_last_error_msg(), 400);
            }
        }
    }
    
    // Get query parameters
    $query_params = $_GET;
    
    // Extract API version from multiple possible sources
    $api_version = 'v1'; // Default version
    
    // Method 1: From URL path /api/v{X}/
    if (preg_match('/\/api\/(v\d+)\//', $request_uri, $matches)) {
        $api_version = $matches[1];
    } 
    // Method 2: From query parameter ?v=X
    elseif (isset($_GET['v']) && preg_match('/^\d+$/', $_GET['v'])) {
        $api_version = 'v' . $_GET['v'];
    }
    // Method 3: From Accept header (optional)
    elseif (isset($_SERVER['HTTP_ACCEPT']) && preg_match('/version=(\d+)/', $_SERVER['HTTP_ACCEPT'], $matches)) {
        $api_version = 'v' . $matches[1];
    }
    
    // Validate API version
    if (!preg_match('/^v\d+$/', $api_version)) {
        throw new Exception('Invalid API version format', 400);
    }
    
    // Extract endpoint
    $endpoint = 'router'; // Default endpoint for v1
    
    // Method 1: From query parameter ?endpoint=name
    if (isset($_GET['endpoint'])) {
        $endpoint = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['endpoint']); // Sanitize
        if (empty($endpoint)) {
            throw new Exception('Invalid endpoint name', 400);
        }
    }
    // Method 2: From URL path after version
    else {
        $path_parts = explode('/', trim(str_replace('/api/', '', $request_uri), '/'));
        if (isset($path_parts[1]) && !empty($path_parts[1])) {
            $endpoint = preg_replace('/[^a-zA-Z0-9_\-]/', '', $path_parts[1]);
        }
    }
    
    // Special handling for v2 mobile endpoint
    if ($api_version === 'v2' && $endpoint === 'router') {
        $endpoint = 'mobile';
    }
    
    // Construct file path
    $api_base_dir = __DIR__;
    $version_dir = $api_base_dir . '/' . $api_version;
    $file_path = $version_dir . '/' . $endpoint . '.php';
    
    // Check if file exists
    if (!file_exists($file_path)) {
        // Try alternate paths
        $alt_paths = [
            $api_base_dir . '/' . $api_version . '/router.php',
            $api_base_dir . '/router.php',
            $api_base_dir . '/index.php'
        ];
        
        $found = false;
        foreach ($alt_paths as $alt_path) {
            if (file_exists($alt_path)) {
                $file_path = $alt_path;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            throw new Exception("API endpoint '{$endpoint}' not found for version {$api_version}", 404);
        }
    }
    
    // Include the API handler
    ob_start(); // Start output buffering
    include $file_path;
    $handler_output = ob_get_clean();
    
    // If handler already output JSON, use it
    if (!empty($handler_output)) {
        // Check if output is valid JSON
        $decoded_output = json_decode($handler_output, true);
        if ($decoded_output !== null) {
            echo $handler_output;
            exit();
        }
    }
    
    // If handler didn't output anything or output wasn't JSON, send success response
    $response['success'] = true;
    $response['data'] = [
        'message' => 'Request processed successfully',
        'version' => $api_version,
        'endpoint' => $endpoint,
        'method' => $request_method
    ];
    
} catch (Exception $e) {
    // Handle errors
    $http_code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($http_code);
    
    $response['error'] = [
        'code' => $http_code,
        'message' => $e->getMessage()
    ];
    
    // Add debug info in development mode
    if (ini_get('display_errors') === '1') {
        $response['error']['file'] = $e->getFile();
        $response['error']['line'] = $e->getLine();
        $response['error']['trace'] = $e->getTraceAsString();
    }
    
} catch (Error $e) {
    // Handle PHP errors
    http_response_code(500);
    $response['error'] = [
        'code' => 500,
        'message' => 'Internal server error'
    ];
    
    if (ini_get('display_errors') === '1') {
        $response['error']['type'] = get_class($e);
        $response['error']['file'] = $e->getFile();
        $response['error']['line'] = $e->getLine();
    }
}

// Output JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
