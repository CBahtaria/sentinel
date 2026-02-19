<?php
namespace UEDF\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use UEDF\Database\Connection;
use UEDF\Config\Config;

class RealTimeServer implements MessageComponentInterface {
    protected $clients;
    protected $db;
    protected $config;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->db = Connection::getInstance();
        $this->config = Config::getInstance();
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send initial drone data
        $stmt = $this->db->query("SELECT * FROM drones WHERE status = 'active'");
        $drones = $stmt->fetchAll();
        $conn->send(json_encode(['type' => 'initial', 'data' => $drones]));
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        switch ($data['type'] ?? '') {
            case 'drone_update':
                $this->handleDroneUpdate($data);
                break;
            case 'threat_alert':
                $this->broadcastThreatAlert($data);
                break;
            case 'subscribe_sector':
                $this->subscribeToSector($from, $data['sector']);
                break;
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} closed\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
    
    private function handleDroneUpdate($data) {
        // Update drone position in database
        $stmt = $this->db->prepare("UPDATE drones SET latitude = ?, longitude = ?, altitude = ?, last_update = NOW() WHERE id = ?");
        $stmt->execute([$data['lat'], $data['lng'], $data['alt'], $data['drone_id']]);
        
        // Broadcast to all clients
        foreach ($this->clients as $client) {
            $client->send(json_encode(['type' => 'drone_update', 'data' => $data]));
        }
    }
    
    private function broadcastThreatAlert($data) {
        // Log threat
        $stmt = $this->db->prepare("INSERT INTO threats (type, severity, latitude, longitude, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['type'], $data['severity'], $data['lat'], $data['lng'], $data['description']]);
        
        // Broadcast alert
        foreach ($this->clients as $client) {
            $client->send(json_encode(['type' => 'threat_alert', 'data' => $data]));
        }
    }
    
    private function subscribeToSector($client, $sector) {
        // Store sector subscription
        $client->sector = $sector;
    }
}
