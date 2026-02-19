<?php
namespace UEDF\Controllers;

use UEDF\Session;
use UEDF\Database\Connection;
use PDO;

class DashboardController {
    private $db;
    private $session;
    
    public function __construct() {
        $this->session = new Session();
        $this->db = Connection::getInstance();
        $this->requireAuth();
    }
    
    public function requireAuth() {
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        // Get drone stats
        $droneStats = $this->getDroneStats();
        
        // Get threat stats
        $threatStats = $this->getThreatStats();
        
        // Get system health
        $systemHealth = $this->getSystemHealth();
        
        // Include view
        require __DIR__ . '/../../views/dashboard/index.php';
    }
    
    private function getDroneStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
                SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline
            FROM drones
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getThreatStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical,
                SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high,
                SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium,
                SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low
            FROM threats 
            WHERE status = 'active'
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getSystemHealth() {
        return [
            'cpu' => rand(20, 60) . '%',
            'memory' => rand(30, 70) . '%',
            'disk' => rand(40, 80) . '%',
            'uptime' => rand(1, 30) . ' days'
        ];
    }
}
