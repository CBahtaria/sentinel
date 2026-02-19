<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/includes/Database.php';
/**
 * UEDF SENTINEL v4.0 - Enhanced WebSocket Server
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class EnhancedSentinelWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $channels;
    protected $pdo;
    protected $connectionHistory;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->channels = [
            'drones' => [],
            'threats' => [],
            'system' => [],
            'alerts' => [],
            'chat' => [],
            'telemetry' => []
        ];
        $this->connectionHistory = [];
        
        // Initialize database
        try {
            $this->pdo = Database::getInstance()->getConnection();
            echo "✅ Database connected\n";
        } catch (Exception $e) {
            echo "⚠️ Database warning: " . $e->getMessage() . "\n";
        }
        
        echo "🚀 Enhanced Sentinel WebSocket Server Started on port 8081\n";
        echo "=============================================\n";
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $connId = spl_object_hash($conn);
        $this->connectionHistory[$connId] = [
            'connected_at' => time(),
            'ip' => $conn->remoteAddress,
            'user_agent' => ''
        ];
        
        echo "🔌 New connection! Total: " . count($this->clients) . "\n";
        
        // Send welcome with server time
        $conn->send(json_encode([
            'type' => 'welcome',
            'server_time' => time(),
            'formatted_time' => date('H:i:s'),
            'active_connections' => count($this->clients)
        ]));
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        $connId = spl_object_hash($from);
        
        if (!$data) {
            echo "⚠️ Invalid message format\n";
            return;
        }
        
        $type = $data['type'] ?? 'unknown';
        $payload = $data['payload'] ?? [];
        
        // Log message
        echo "📨 [$type] from " . ($this->users[$connId]['username'] ?? 'guest') . "\n";
        
        switch($type) {
            case 'auth':
                $this->authenticate($from, $payload);
                break;
                
            case 'subscribe':
                $this->subscribe($from, $payload);
                break;
                
            case 'unsubscribe':
                $this->unsubscribe($from, $payload);
                break;
                
            case 'drone_command':
                $this->handleDroneCommand($from, $payload);
                break;
                
            case 'threat_update':
                $this->handleThreatUpdate($from, $payload);
                break;
                
            case 'chat_message':
                $this->handleChatMessage($from, $payload);
                break;
                
            case 'telemetry_request':
                $this->sendTelemetry($from, $payload);
                break;
                
            case 'ping':
                $from->send(json_encode([
                    'type' => 'pong',
                    'timestamp' => time(),
                    'server_load' => $this->getServerLoad()
                ]));
                break;
                
            case 'heartbeat':
                $this->handleHeartbeat($from);
                break;
                
            default:
                echo "⚠️ Unknown message type: $type\n";
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $connId = spl_object_hash($conn);
        
        if (isset($this->users[$connId])) {
            $username = $this->users[$connId]['username'];
            $role = $this->users[$connId]['role'];
            
            // Broadcast user left
            $this->broadcastToChannel('system', [
                'type' => 'user_left',
                'username' => $username,
                'role' => $role,
                'time' => date('H:i:s')
            ]);
            
            unset($this->users[$connId]);
            echo "👤 User $username disconnected\n";
        }
        
        // Remove from all channels
        foreach ($this->channels as $channel => $subscribers) {
            if (isset($subscribers[$connId])) {
                unset($this->channels[$channel][$connId]);
            }
        }
        
        $this->clients->detach($conn);
        echo "🔌 Connection closed. Active: " . count($this->clients) . "\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        $conn->close();
    }
    
    private function authenticate($conn, $payload) {
        $connId = spl_object_hash($conn);
        $userId = $payload['user_id'] ?? null;
        $username = $payload['username'] ?? 'guest';
        $role = $payload['role'] ?? 'viewer';
        
        $this->users[$connId] = [
            'user_id' => $userId,
            'username' => $username,
            'role' => $role,
            'authenticated_at' => time(),
            'connection' => $conn
        ];
        
        // Send auth success
        $conn->send(json_encode([
            'type' => 'auth_success',
            'message' => "Welcome $username",
            'timestamp' => time(),
            'server_info' => [
                'version' => '4.0',
                'uptime' => $this->getUptime(),
                'active_users' => count($this->users)
            ]
        ]));
        
        // Broadcast user joined
        $this->broadcastToChannel('system', [
            'type' => 'user_joined',
            'username' => $username,
            'role' => $role,
            'time' => date('H:i:s')
        ]);
        
        echo "👤 User $username authenticated as $role\n";
        
        // Send initial data
        $this->sendInitialData($conn);
    }
    
    private function subscribe($conn, $payload) {
        $connId = spl_object_hash($conn);
        $channels = $payload['channels'] ?? [];
        
        foreach ($channels as $channel) {
            if (!isset($this->channels[$channel])) {
                $this->channels[$channel] = [];
            }
            $this->channels[$channel][$connId] = $conn;
            echo "📡 Subscribed to $channel\n";
        }
        
        $conn->send(json_encode([
            'type' => 'subscribed',
            'channels' => $channels,
            'timestamp' => time()
        ]));
        
        // Send channel-specific initial data
        foreach ($channels as $channel) {
            $this->sendChannelData($conn, $channel);
        }
    }
    
    private function unsubscribe($conn, $payload) {
        $connId = spl_object_hash($conn);
        $channels = $payload['channels'] ?? [];
        
        foreach ($channels as $channel) {
            if (isset($this->channels[$channel][$connId])) {
                unset($this->channels[$channel][$connId]);
                echo "📡 Unsubscribed from $channel\n";
            }
        }
    }
    
    private function handleDroneCommand($conn, $payload) {
        $connId = spl_object_hash($conn);
        $droneId = $payload['drone_id'] ?? null;
        $command = $payload['command'] ?? null;
        
        if (!isset($this->users[$connId])) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Not authenticated']));
            return;
        }
        
        $username = $this->users[$connId]['username'];
        
        echo "🚁 Drone command: $command for drone $droneId from $username\n";
        
        // Simulate command execution
        $response = [
            'type' => 'drone_command_response',
            'drone_id' => $droneId,
            'command' => $command,
            'status' => 'executing',
            'estimated_time' => rand(2, 5) . 's',
            'timestamp' => time()
        ];
        
        $conn->send(json_encode($response));
        
        // Broadcast to drone channel
        $this->broadcastToChannel('drones', [
            'type' => 'drone_command',
            'drone_id' => $droneId,
            'command' => $command,
            'username' => $username,
            'timestamp' => time()
        ]);
        
        // Log to database
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
            $stmt->execute([$this->users[$connId]['user_id'], 'DRONE_COMMAND', "Drone $droneId: $command"]);
        }
    }
    
    private function handleThreatUpdate($conn, $payload) {
        $connId = spl_object_hash($conn);
        $threatId = $payload['threat_id'] ?? null;
        $status = $payload['status'] ?? null;
        
        if (!isset($this->users[$connId])) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Not authenticated']));
            return;
        }
        
        // Broadcast threat update to all subscribers
        $this->broadcastToChannel('threats', [
            'type' => 'threat_updated',
            'threat_id' => $threatId,
            'status' => $status,
            'updated_by' => $this->users[$connId]['username'],
            'timestamp' => time()
        ]);
        
        // Send alert to all users
        $this->broadcastToChannel('alerts', [
            'type' => 'threat_alert',
            'title' => 'Threat Status Changed',
            'message' => "Threat #$threatId marked as $status",
            'severity' => $payload['severity'] ?? 'info',
            'timestamp' => time()
        ]);
    }
    
    private function handleChatMessage($conn, $payload) {
        $connId = spl_object_hash($conn);
        
        if (!isset($this->users[$connId])) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Not authenticated']));
            return;
        }
        
        $message = [
            'type' => 'chat_message',
            'username' => $this->users[$connId]['username'],
            'role' => $this->users[$connId]['role'],
            'message' => $payload['message'] ?? '',
            'channel' => $payload['channel'] ?? 'general',
            'timestamp' => time(),
            'formatted_time' => date('H:i:s')
        ];
        
        // Broadcast to chat channel
        $this->broadcastToChannel('chat', $message);
    }
    
    private function sendTelemetry($conn, $payload) {
        $droneId = $payload['drone_id'] ?? null;
        
        if ($droneId) {
            // Simulate telemetry data
            $telemetry = [
                'type' => 'telemetry',
                'drone_id' => $droneId,
                'battery' => rand(70, 100),
                'altitude' => rand(100, 500) . 'm',
                'speed' => rand(20, 60) . 'km/h',
                'heading' => rand(0, 359) . '°',
                'signal' => rand(70, 100) . '%',
                'temperature' => rand(25, 45) . '°C',
                'gps_sats' => rand(8, 15),
                'timestamp' => time()
            ];
            
            $conn->send(json_encode($telemetry));
        }
    }
    
    private function handleHeartbeat($conn) {
        $connId = spl_object_hash($conn);
        
        if (isset($this->users[$connId])) {
            $this->users[$connId]['last_heartbeat'] = time();
        }
        
        $conn->send(json_encode([
            'type' => 'heartbeat_ack',
            'timestamp' => time(),
            'server_time' => date('H:i:s')
        ]));
    }
    
    private function sendInitialData($conn) {
        // Send system status
        $conn->send(json_encode([
            'type' => 'system_status',
            'data' => [
                'version' => '4.0',
                'active_users' => count($this->users),
                'total_connections' => count($this->clients),
                'channels' => array_keys($this->channels),
                'uptime' => $this->getUptime()
            ]
        ]));
    }
    
    private function sendChannelData($conn, $channel) {
        switch($channel) {
            case 'system':
                $conn->send(json_encode([
                    'type' => 'system_info',
                    'data' => [
                        'active_users' => count($this->users),
                        'channels' => array_keys($this->channels),
                        'server_time' => date('H:i:s')
                    ]
                ]));
                break;
                
            case 'drones':
                // Send drone fleet status
                $conn->send(json_encode([
                    'type' => 'drone_fleet',
                    'data' => [
                        'total' => 15,
                        'active' => rand(8, 12),
                        'standby' => rand(2, 4),
                        'maintenance' => rand(1, 2)
                    ]
                ]));
                break;
        }
    }
    
    private function broadcastToChannel($channel, $data) {
        if (isset($this->channels[$channel])) {
            foreach ($this->channels[$channel] as $conn) {
                $conn->send(json_encode($data));
            }
        }
    }
    
    private function getServerLoad() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0], 2);
        }
        return rand(10, 50) / 10;
    }
    
    private function getUptime() {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $uptime = explode(' ', $uptime)[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            return "$days days, $hours hours";
        }
        return "15 days (estimated)";
    }
}

// Start the enhanced server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new EnhancedSentinelWebSocket()
        )
    ),
    8081
);

echo "=============================================\n";
echo "   UEDF SENTINEL Enhanced WebSocket Server\n";
echo "   Port: 8081\n";
echo "   Status: ACTIVE\n";
echo "=============================================\n";
echo "Press Ctrl+C to stop\n\n";

$server->run();
?>
