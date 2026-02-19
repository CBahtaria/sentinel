<?php
namespace UEDF\Controllers;

use UEDF\Session;
use UEDF\Database\Connection;
use PDO;

class AnalyticsController {
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
        // Analytics dashboard logic
        require 'views/analytics/index.php';
    }
}
