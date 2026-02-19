<?php
require_once __DIR__ . '/../src/session.php';
/**
 * BARTARIAN DEFENCE v5.0 - REST API Gateway
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete API endpoints for external integrations
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 3600');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session for API authentication
if (session_status() === PHP_SESSION_NONE) {
    
}

// API configuration
define('API_VERSION', 'v1');
define('API_NAME', 'BARTARIAN DEFENCE API');

// Get request parameters
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? explode('/', rtrim($_GET['endpoint'], '/')) : [];
$resource = $endpoint[0] ?? '';
$id = $endpoint[1] ?? null;
$action = $endpoint[2] ?? null;

// Get request body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// API Authentication (simplified for demo)
$authenticated = false;
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';

// Simple API key validation (in production, use database)
$valid_api_keys = [
    'live_ueNcDgRkYvWmQpLhJfKg' => 'commander',
    'test_AbCdEfGhIjKlMnOpQrSt' => 'operator'
];

if (isset($valid_api_keys[$api_key])) {
    $authenticated = true;
    $api_user = $valid_api_keys[$api_key];
} elseif (isset($_SESSION['user_id'])) {
    $authenticated = true;
    $api_user = $_SESSION['username'] ?? 'authenticated';
}

// Response function
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function sendError($message, $status = 400) {
    sendResponse([
        'error' => true,
        'message' => $message,
        'timestamp' => date('c')
    ], $status);
}

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=BARTARIAN_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    sendError('Database connection failed', 503);
}

// API Documentation endpoint
if ($resource === '' || $resource === 'docs') {
    sendResponse([
        'api' => API_NAME,
        'version' => API_VERSION,
        'status' => 'operational',
        'timestamp' => date('c'),
        'endpoints' => [
            'GET /api/status' => 'System status',
            'GET /api/drones' => 'List all drones',
            'GET /api/drones/{id}' => 'Get drone by ID',
            'POST /api/drones/{id}/control' => 'Control drone',
            'GET /api/threats' => 'List active threats',
            'GET /api/threats/{id}' => 'Get threat by ID',
            'POST /api/threats/{id}/resolve' => 'Resolve threat',
            'GET /api/nodes' => 'List all nodes',
            'GET /api/analytics' => 'Get system analytics',
            'GET /api/health' => 'System health check',
            'GET /api/users' => 'List users (auth required)',
            'POST /api/auth/login' => 'User login',
            'POST /api/auth/logout' => 'User logout'
        ],
        'authentication' => 'Use X-API-Key header or session cookie',
        'example' => 'curl -H "X-API-Key: your_key" http://localhost:8080/sentinel/api.php?endpoint=drones'
    ]);
}

// Public endpoints (no auth required)
if (!$authenticated && !in_array($resource, ['auth', 'status', 'health'])) {
    sendError('Authentication required', 401);
}

// Route based on resource
switch ($resource) {
    // =============================================
    // SYSTEM STATUS
    // =============================================
    case 'status':
        sendResponse([
            'status' => 'online',
            'version' => API_VERSION,
            'timestamp' => date('c'),
            'uptime' => '24h',
            'authenticated' => $authenticated,
            'user' => $api_user ?? null
        ]);
        break;
        
    case 'health':
        try {
            $db_status = $pdo->query("SELECT 1") ? 'connected' : 'disconnected';
            $drone_count = $pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn();
            $threat_count = $pdo->query("SELECT COUNT(*) FROM threats WHERE status = 'ACTIVE'")->fetchColumn();
            
            sendResponse([
                'status' => 'healthy',
                'timestamp' => date('c'),
                'database' => $db_status,
                'stats' => [
                    'drones' => (int)$drone_count,
                    'active_threats' => (int)$threat_count
                ],
                'php_version' => PHP_VERSION,
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
            ]);
        } catch (Exception $e) {
            sendError('Health check failed', 503);
        }
        break;
    
    // =============================================
    // AUTHENTICATION
    // =============================================
    case 'auth':
        if ($method === 'POST') {
            if ($action === 'login') {
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';
                
                try {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        sendResponse([
                            'success' => true,
                            'message' => 'Login successful',
                            'user' => [
                                'id' => $user['id'],
                                'username' => $user['username'],
                                'role' => $user['role']
                            ]
                        ]);
                    } else {
                        sendError('Invalid credentials', 401);
                    }
                } catch (Exception $e) {
                    sendError('Login failed', 500);
                }
            } elseif ($action === 'logout') {
                $_SESSION = [];
                session_destroy();
                sendResponse(['success' => true, 'message' => 'Logged out']);
            }
        }
        break;
    
    // =============================================
    // DRONES
    // =============================================
    case 'drones':
        if ($method === 'GET') {
            if ($id) {
                // Get single drone
                $stmt = $pdo->prepare("SELECT * FROM drones WHERE id = ?");
                $stmt->execute([$id]);
                $drone = $stmt->fetch();
                
                if ($drone) {
                    sendResponse(['drone' => $drone]);
                } else {
                    sendError('Drone not found', 404);
                }
            } else {
                // List all drones
                $stmt = $pdo->query("SELECT * FROM drones ORDER BY id");
                $drones = $stmt->fetchAll();
                
                sendResponse([
                    'total' => count($drones),
                    'drones' => $drones
                ]);
            }
        } elseif ($method === 'POST' && $id && $action === 'control') {
            // Control drone
            $command = $input['command'] ?? '';
            $valid_commands = ['launch', 'land', 'return', 'scan', 'record'];
            
            if (!in_array($command, $valid_commands)) {
                sendError('Invalid command', 400);
            }
            
            // Log the command
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'] ?? 0, 'drone_command', "Drone $id: $command"]);
            
            sendResponse([
                'success' => true,
                'message' => "Command '$command' sent to drone $id",
                'drone_id' => $id,
                'command' => $command,
                'timestamp' => date('c')
            ]);
        }
        break;
    
    // =============================================
    // THREATS
    // =============================================
    case 'threats':
        if ($method === 'GET') {
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM threats WHERE id = ?");
                $stmt->execute([$id]);
                $threat = $stmt->fetch();
                
                if ($threat) {
                    sendResponse(['threat' => $threat]);
                } else {
                    sendError('Threat not found', 404);
                }
            } else {
                $status = $_GET['status'] ?? 'ACTIVE';
                $stmt = $pdo->prepare("SELECT * FROM threats WHERE status = ? ORDER BY severity DESC, detected_at DESC");
                $stmt->execute([$status]);
                $threats = $stmt->fetchAll();
                
                sendResponse([
                    'total' => count($threats),
                    'status' => $status,
                    'threats' => $threats
                ]);
            }
        } elseif ($method === 'POST' && $id && $action === 'resolve') {
            $stmt = $pdo->prepare("UPDATE threats SET status = 'RESOLVED', resolved_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            
            sendResponse([
                'success' => true,
                'message' => "Threat $id resolved",
                'threat_id' => $id
            ]);
        }
        break;
    
    // =============================================
    // NODES
    // =============================================
    case 'nodes':
        if ($method === 'GET') {
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM nodes WHERE id = ?");
                $stmt->execute([$id]);
                $node = $stmt->fetch();
                
                if ($node) {
                    sendResponse(['node' => $node]);
                } else {
                    sendError('Node not found', 404);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM nodes ORDER BY name");
                $nodes = $stmt->fetchAll();
                
                sendResponse([
                    'total' => count($nodes),
                    'nodes' => $nodes
                ]);
            }
        }
        break;
    
    // =============================================
    // ANALYTICS
    // =============================================
    case 'analytics':
        if ($method === 'GET') {
            $period = $_GET['period'] ?? '24h';
            
            // Get summary stats
            $stats = [
                'total_drones' => (int)$pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn(),
                'active_drones' => (int)$pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'ACTIVE'")->fetchColumn(),
                'total_threats' => (int)$pdo->query("SELECT COUNT(*) FROM threats")->fetchColumn(),
                'active_threats' => (int)$pdo->query("SELECT COUNT(*) FROM threats WHERE status = 'ACTIVE'")->fetchColumn(),
                'total_users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'total_nodes' => (int)$pdo->query("SELECT COUNT(*) FROM nodes")->fetchColumn()
            ];
            
            // Get threat trends
            if ($period === '24h') {
                $trends = $pdo->query("
                    SELECT HOUR(detected_at) as hour, COUNT(*) as count 
                    FROM threats 
                    WHERE detected_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY HOUR(detected_at)
                    ORDER BY hour
                ")->fetchAll();
            } else {
                $trends = $pdo->query("
                    SELECT DATE(detected_at) as date, COUNT(*) as count 
                    FROM threats 
                    WHERE detected_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(detected_at)
                    ORDER BY date
                ")->fetchAll();
            }
            
            sendResponse([
                'period' => $period,
                'summary' => $stats,
                'trends' => $trends,
                'timestamp' => date('c')
            ]);
        }
        break;
    
    // =============================================
    // USERS (Admin only)
    // =============================================
    case 'users':
        if ($method === 'GET' && $api_user === 'commander') {
            $stmt = $pdo->query("SELECT id, username, full_name, role, two_factor_enabled, created_at, last_login FROM users");
            $users = $stmt->fetchAll();
            
            sendResponse([
                'total' => count($users),
                'users' => $users
            ]);
        } else {
            sendError('Unauthorized', 403);
        }
        break;
    
    // =============================================
    // DEFAULT - 404
    // =============================================
    default:
        sendError('Endpoint not found', 404);
        break;
}
?>

