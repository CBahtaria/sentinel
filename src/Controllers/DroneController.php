<?php
namespace UEDF\Controllers;

use UEDF\Session;
use UEDF\Database\Connection;
use PDO;

/**
 * UEDF SENTINEL v5.0 - Drone Fleet Management
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete drone inventory and status monitoring
 */
class DroneController {
    
    private $db;
    private $session;
    
    public function __construct() {
        $this->session = new Session();
        $this->db = Connection::getInstance();
    }
    
    /**
     * Check if user is authenticated
     */
    public function requireAuth() {
        if (!$this->session->isLoggedIn()) {
            header('Location: ?module=login');
            exit;
        }
    }
    
    /**
     * Display drone dashboard
     */
    public function index() {
        $this->requireAuth();
        
        // Get drone statistics
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM drones");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as active FROM drones WHERE status = 'active'");
        $active = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
        
        // Get recent drones
        $stmt = $this->db->query("SELECT * FROM drones ORDER BY id DESC LIMIT 10");
        $recentDrones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/drones/index.php';
    }
    
    /**
     * Get drone details
     */
    public function show($id) {
        $this->requireAuth();
        
        $stmt = $this->db->prepare("SELECT * FROM drones WHERE id = ?");
        $stmt->execute([$id]);
        $drone = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$drone) {
            header('Location: ?module=drones');
            exit;
        }
        
        // Get telemetry data
        $stmt = $this->db->prepare("SELECT * FROM drone_telemetry WHERE drone_id = ? ORDER BY timestamp DESC LIMIT 100");
        $stmt->execute([$id]);
        $telemetry = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/drones/show.php';
    }
    
    /**
     * API endpoint to get drone status
     */
    public function apiStatus() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        $stmt = $this->db->query("
            SELECT d.*, 
                   (SELECT COUNT(*) FROM drone_telemetry WHERE drone_id = d.id) as telemetry_count,
                   (SELECT timestamp FROM drone_telemetry WHERE drone_id = d.id ORDER BY timestamp DESC LIMIT 1) as last_update
            FROM drones d
        ");
        
        $drones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $drones]);
    }
}
