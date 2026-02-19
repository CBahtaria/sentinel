<?php
/**
 * SENTINEL v3.1 - Authentication API
 * Handles login, logout, token refresh, user management
 */

require_once __DIR__ . '/../src/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$auth = new SentinelAuth();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['username']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'error' => 'Username and password required']);
            exit();
        }
        
        $result = $auth->login(
            $data['username'],
            $data['password'],
            $data['deviceInfo'] ?? null
        );
        
        echo json_encode($result);
        break;
        
    case 'logout':
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        if (!$token) {
            echo json_encode(['success' => false, 'error' => 'No token provided']);
            exit();
        }
        
        echo json_encode($auth->logout($token));
        break;
        
    case 'refresh':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['refresh_token'])) {
            echo json_encode(['success' => false, 'error' => 'Refresh token required']);
            exit();
        }
        
        echo json_encode($auth->refreshToken($data['refresh_token']));
        break;
        
    case 'me':
        $user = $auth->getCurrentUser();
        
        if ($user) {
            unset($user['password_hash']);
            $user['permissions'] = $auth->getUserPermissions($user['role']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        }
        break;
        
    case 'users':
        // Require admin role
        $user = $auth->requireAuth('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $role = $_GET['role'] ?? null;
            echo json_encode(['success' => true, 'users' => $auth->listUsers($role)]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($auth->register($data));
        }
        break;
        
    case 'user':
        // Require admin role
        $user = $auth->requireAuth('admin');
        
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'error' => 'User ID required']);
            exit();
        }
        
        $id = (int)$_GET['id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($auth->updateUser($id, $data));
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            echo json_encode($auth->deleteUser($id));
        }
        break;
        
    case 'activity':
        $user = $auth->requireAuth();
        $userId = $_GET['user_id'] ?? $user['id'];
        $limit = $_GET['limit'] ?? 50;
        
        // Only admin can view other users' activity
        if ($userId != $user['id'] && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'activity' => $auth->getUserActivity($userId, $limit)
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action',
            'available_actions' => ['login', 'logout', 'refresh', 'me', 'users', 'user', 'activity']
        ]);
}
?>
