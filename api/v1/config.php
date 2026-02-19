// Authenticate API request (skip for login)
function authenticate() {
    // Check if this is a login request - if so, skip authentication
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $action = $_GET['action'] ?? '';
    
    if (strpos($script_name, 'auth.php') !== false && $action === 'login') {
        error_log("Skipping API key check for login endpoint");
        return true;
    }
    
    $headers = getallheaders();
    
    // Log all headers for debugging
    error_log("Auth headers: " . json_encode($headers));
    
    // Try to get API key from various possible header names
    $apiKey = '';
    
    // Check different possible header names (case-insensitive)
    $headerNames = ['X-API-Key', 'x-api-key', 'API-Key', 'api-key'];
    
    foreach ($headerNames as $name) {
        if (isset($headers[$name])) {
            $apiKey = $headers[$name];
            error_log("Found API key in header '$name': $apiKey");
            break;
        }
    }
    
    // Also check query parameter
    if (empty($apiKey) && isset($_GET['api_key'])) {
        $apiKey = $_GET['api_key'];
        error_log("Found API key in query parameter: $apiKey");
    }
    
    // For debugging
    error_log("Final API Key received: '" . $apiKey . "'");
    error_log("Expected API Key: '" . API_KEY . "'");
    
    if ($apiKey !== API_KEY) {
        sendError('Unauthorized - Invalid API Key. Received: ' . $apiKey, 401);
    }
    
    return true;
}
