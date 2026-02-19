<?php
class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        $config = include __DIR__ . '/../config/database.php';
        $this->connection = new mysqli(
            $config['host'],
            $config['username'], 
            $config['password'],
            $config['database']
        );
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function fetchAll($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}
?>
