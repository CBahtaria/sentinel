<?php
namespace UEDF\Controllers;

use UEDF\Session;
use UEDF\Database\Connection;
use PDO;

class ThreatController {
    private $db;
    private $session;
    
    public function __construct() {
        $this->session = new Session();
        $this->db = Connection::getInstance();
    }
    
    public function requireAuth() {
        if (!$this->session->isLoggedIn()) {
            header('Location: ?module=login');
            exit;
        }
    }
    
    public function index() {
        $this->requireAuth();
        // Threat monitoring
        require 'views/threats/index.php';
    }
}
