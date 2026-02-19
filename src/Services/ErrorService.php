<?php
/**
 * UEDF SENTINEL v5.0 - Centralized Error Handler
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Comprehensive error handling and logging system
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Custom error handler
function sentinelErrorHandler($errno, $errstr, $errfile, $errline) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'error',
        'code' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
    ];
    
    // Log to file
    error_log(json_encode($log_entry) . PHP_EOL, 3, __DIR__ . '/logs/error.log');
    
    // Don't execute PHP internal handler
    return true;
}

// Custom exception handler
function sentinelExceptionHandler($exception) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'exception',
        'message' => $exception->getMessage(),
        'code' => $exception->getCode(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
    
    // Log to file
    error_log(json_encode($log_entry) . PHP_EOL, 3, __DIR__ . '/logs/exceptions.log');
    
    // Display user-friendly error page
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: text/html; charset=UTF-8');
    }
    
    // Load error page
    include __DIR__ . '/error/500.php';
    exit;
}

// Fatal error handler
function sentinelShutdownHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'fatal',
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ];
        
        error_log(json_encode($log_entry) . PHP_EOL, 3, __DIR__ . '/logs/fatal.log');
        
        // Load fatal error page
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        include __DIR__ . '/error/500.php';
    }
}

// Set handlers
set_error_handler('sentinelErrorHandler');
set_exception_handler('sentinelExceptionHandler');
register_shutdown_function('sentinelShutdownHandler');

// Logging function
function sentinelLog($level, $message, $context = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? 0,
        'uri' => $_SERVER['REQUEST_URI'] ?? ''
    ];
    
    $log_file = __DIR__ . '/logs/' . date('Y-m-d') . '.log';
    error_log(json_encode($log_entry) . PHP_EOL, 3, $log_file);
    
    // Also log to database if available
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        try {
            $stmt = $GLOBALS['pdo']->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address, timestamp) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'] ?? 0,
                'log_' . $level,
                json_encode(['message' => $message, 'context' => $context]),
                $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        } catch (Exception $e) {
            // Silently fail
        }
    }
}

// Performance monitoring
class PerformanceMonitor {
    private $start_time;
    private $queries = [];
    private $memory_start;
    
    public function __construct() {
        $this->start_time = microtime(true);
        $this->memory_start = memory_get_usage();
    }
    
    public function logQuery($sql, $params = []) {
        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => microtime(true)
        ];
    }
    
    public function getStats() {
        $end_time = microtime(true);
        $memory_end = memory_get_usage();
        
        return [
            'execution_time' => round(($end_time - $this->start_time) * 1000, 2) . 'ms',
            'memory_usage' => round(($memory_end - $this->memory_start) / 1024 / 1024, 2) . 'MB',
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
            'query_count' => count($this->queries)
        ];
    }
    
    public function logStats() {
        $stats = $this->getStats();
        sentinelLog('info', 'Performance stats', $stats);
        return $stats;
    }
}

// Database error handler
function handleDatabaseError($e, $sql = '') {
    $context = [
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'sql' => $sql
    ];
    
    sentinelLog('error', 'Database error', $context);
    
    // Return user-friendly message
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        return "Database error: " . $e->getMessage();
    } else {
        return "A database error occurred. Please try again later.";
    }
}

// Security error handler
function handleSecurityError($message, $severity = 'warning') {
    $context = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'session_id' => session_id()
    ];
    
    sentinelLog($severity, 'Security: ' . $message, $context);
    
    // Take action based on severity
    if ($severity === 'critical') {
        // Log out user
        $_SESSION = [];
        session_destroy();
        
        // Redirect to login
        header('Location: ?module=login&error=security');
        exit;
    }
}

// API error handler
function sendApiError($message, $code = 400, $details = []) {
    http_response_code($code);
    header('Content-Type: application/json');
    
    $response = [
        'error' => true,
        'message' => $message,
        'code' => $code,
        'timestamp' => date('c')
    ];
    
    if (!empty($details)) {
        $response['details'] = $details;
    }
    
    sentinelLog('error', 'API Error: ' . $message, ['code' => $code, 'details' => $details]);
    
    echo json_encode($response);
    exit;
}

// Maintenance mode check
function checkMaintenanceMode() {
    if (file_exists(__DIR__ . '/.maintenance')) {
        $maintenance_file = file_get_contents(__DIR__ . '/.maintenance');
        $allowed_ips = ['127.0.0.1', '::1'];
        
        // Allow access for admins or specific IPs
        if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) && 
            (!isset($_SESSION['role']) || $_SESSION['role'] !== 'commander')) {
            
            header('HTTP/1.1 503 Service Unavailable');
            header('Retry-After: 3600');
            
            if (file_exists(__DIR__ . '/error/503.php')) {
                include __DIR__ . '/error/503.php';
            } else {
                echo "<h1>Maintenance Mode</h1>";
                echo "<p>" . htmlspecialchars($maintenance_file) . "</p>";
            }
            exit;
        }
    }
}

// Rate limiting
class RateLimiter {
    private $max_requests = 100;
    private $time_window = 60; // seconds
    private $storage_dir;
    
    public function __construct($max_requests = 100, $time_window = 60) {
        $this->max_requests = $max_requests;
        $this->time_window = $time_window;
        $this->storage_dir = __DIR__ . '/cache/ratelimit/';
        
        if (!is_dir($this->storage_dir)) {
            mkdir($this->storage_dir, 0755, true);
        }
    }
    
    public function check($key) {
        $file = $this->storage_dir . md5($key) . '.json';
        $now = time();
        
        $data = [
            'count' => 0,
            'reset_time' => $now + $this->time_window
        ];
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            
            // Reset if window expired
            if ($now > $data['reset_time']) {
                $data = [
                    'count' => 0,
                    'reset_time' => $now + $this->time_window
                ];
            }
        }
        
        $data['count']++;
        
        file_put_contents($file, json_encode($data));
        
        $remaining = $this->max_requests - $data['count'];
        $reset_in = $data['reset_time'] - $now;
        
        return [
            'allowed' => $data['count'] <= $this->max_requests,
            'remaining' => max(0, $remaining),
            'reset_in' => $reset_in
        ];
    }
}

// Input validation
function validateInput($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? null;
        
        // Required check
        if (strpos($rule, 'required') !== false && empty($value)) {
            $errors[$field] = "$field is required";
            continue;
        }
        
        // Type checks
        if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Invalid email format";
        }
        
        if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
            $errors[$field] = "Must be numeric";
        }
        
        if (strpos($rule, 'int') !== false && !filter_var($value, FILTER_VALIDATE_INT)) {
            $errors[$field] = "Must be an integer";
        }
        
        if (strpos($rule, 'url') !== false && !filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[$field] = "Invalid URL";
        }
        
        // Length checks
        preg_match('/min:(\d+)/', $rule, $min_match);
        if ($min_match && strlen($value) < $min_match[1]) {
            $errors[$field] = "Minimum length is {$min_match[1]}";
        }
        
        preg_match('/max:(\d+)/', $rule, $max_match);
        if ($max_match && strlen($value) > $max_match[1]) {
            $errors[$field] = "Maximum length is {$max_match[1]}";
        }
        
        // Pattern check
        preg_match('/pattern:(.+)/', $rule, $pattern_match);
        if ($pattern_match && !preg_match('/' . $pattern_match[1] . '/', $value)) {
            $errors[$field] = "Invalid format";
        }
    }
    
    return $errors;
}

// CSRF Protection
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        sentinelLog('warning', 'CSRF token mismatch', ['token' => $token]);
        return false;
    }
    return true;
}

// XSS Protection
function escape($data) {
    if (is_array($data)) {
        return array_map('escape', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// SQL Injection Prevention (use prepared statements, this is just extra)
function sanitizeForSql($value) {
    if (is_numeric($value)) {
        return $value;
    }
    // This is just a backup - always use prepared statements
    return addslashes($value);
}

// Initialize error handling
sentinelLog('info', 'Error handler initialized', [
    'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'production',
    'php_version' => PHP_VERSION
]);
?>
