<?php
require_once '../config.php';
authenticate();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

try {
    $pdo = getDB();
    
    switch($method) {
        case 'GET':
            if ($action === 'list') {
                // Get all drones
                $stmt = $pdo->query("SELECT * FROM drones ORDER BY id");
                $drones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get statistics
                $stats = $pdo->query("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'STANDBY' THEN 1 ELSE 0 END) as standby,
                        SUM(CASE WHEN status = 'MAINTENANCE' THEN 1 ELSE 0 END) as maintenance,
                        AVG(battery_level) as avg_battery
                    FROM drones
                ")->fetch(PDO::FETCH_ASSOC);
                
                sendResponse([
                    'drones' => $drones,
                    'stats' => $stats
                ]);
                
            } elseif ($action === 'get' && isset($_GET['id'])) {
                // Get single drone
                $stmt = $pdo->prepare("SELECT * FROM drones WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $drone = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($drone) {
                    sendResponse($drone);
                } else {
                    sendError('Drone not found', 404);
                }
            }
            break;
            
        case 'POST':
            // Update drone status (from mobile command)
            $data = json_decode(file_get_contents('php://input'), true);
            $droneId = $data['drone_id'] ?? $_GET['id'] ?? null;
            $command = $data['command'] ?? '';
            
            if (!$droneId || !$command) {
                sendError('Drone ID and command required', 400);
            }
            
            // Validate command
            $validCommands = ['launch', 'land', 'return', 'hover', 'scan', 'emergency'];
            if (!in_array($command, $validCommands)) {
                sendError('Invalid command', 400);
            }
            
            // Update drone status based on command
            $newStatus = 'STANDBY';
            if ($command === 'launch') $newStatus = 'ACTIVE';
            if ($command === 'land' || $command === 'return') $newStatus = 'STANDBY';
            if ($command === 'emergency') $newStatus = 'MAINTENANCE';
            
            $stmt = $pdo->prepare("UPDATE drones SET status = ?, last_seen = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $droneId]);
            
            // Log the command
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'MOBILE_DRONE_COMMAND', ?)");
            $stmt->execute([1, "Drone $droneId command: $command"]);
            
            sendResponse([
                'drone_id' => $droneId,
                'command' => $command,
                'status' => $newStatus,
                'message' => 'Command executed successfully'
            ]);
            break;
            
        default:
            sendError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>
