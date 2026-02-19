<?php
namespace UEDF\Controllers;

use UEDF\Database\Connection;
use UEDF\Config\Config;

class MobileApiController {
    private $db;
    private $config;
    
    public function __construct() {
        $this->db = Connection::getInstance();
        $this->config = Config::getInstance();
    }
    
    public function authenticate() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND api_access = 1");
        $stmt->execute([$input['username']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($input['password'], $user['password'])) {
            $token = bin2hex(random_bytes(32));
            
            // Store token
            $stmt = $this->db->prepare("UPDATE users SET api_token = ?, token_expires = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            
            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    }
    
    public function getDrones() {
        $this->validateToken();
        
        $stmt = $this->db->query("SELECT * FROM drones WHERE status = 'active'");
        $drones = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $drones]);
    }
    
    public function getThreats() {
        $this->validateToken();
        
        $stmt = $this->db->query("SELECT * FROM threats WHERE status = 'active' ORDER BY severity DESC, detected_at DESC LIMIT 50");
        $threats = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $threats]);
    }
    
    public function controlDrone($id) {
        $this->validateToken();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("UPDATE drones SET command = ?, command_issued_at = NOW() WHERE id = ?");
        $stmt->execute([$input['command'], $id]);
        
        echo json_encode(['success' => true, 'message' => 'Command sent']);
    }
    
    private function validateToken() {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE api_token = ? AND token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }
    }
}
