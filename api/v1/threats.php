<?php
require_once '../config.php';
authenticate();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDB();
    
    switch($method) {
        case 'GET':
            $status = $_GET['status'] ?? 'ACTIVE';
            $limit = $_GET['limit'] ?? 50;
            
            $sql = "SELECT * FROM threats WHERE 1=1";
            $params = [];
            
            if ($status !== 'all') {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY 
                CASE severity 
                    WHEN 'CRITICAL' THEN 1 
                    WHEN 'HIGH' THEN 2 
                    WHEN 'MEDIUM' THEN 3 
                    WHEN 'LOW' THEN 4 
                END,
                detected_at DESC
                LIMIT ?";
            $params[] = $limit;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $threats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get statistics
            $stats = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN severity = 'CRITICAL' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN severity = 'HIGH' THEN 1 ELSE 0 END) as high,
                    SUM(CASE WHEN severity = 'MEDIUM' THEN 1 ELSE 0 END) as medium,
                    SUM(CASE WHEN severity = 'LOW' THEN 1 ELSE 0 END) as low,
                    SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active
                FROM threats
            ")->fetch(PDO::FETCH_ASSOC);
            
            sendResponse([
                'threats' => $threats,
                'stats' => $stats
            ]);
            break;
            
        case 'PUT':
            // Update threat status
            $data = json_decode(file_get_contents('php://input'), true);
            $threatId = $data['threat_id'] ?? $_GET['id'] ?? null;
            $status = $data['status'] ?? '';
            
            if (!$threatId || !$status) {
                sendError('Threat ID and status required', 400);
            }
            
            $validStatus = ['ACTIVE', 'INVESTIGATING', 'RESOLVED'];
            if (!in_array($status, $validStatus)) {
                sendError('Invalid status', 400);
            }
            
            $stmt = $pdo->prepare("UPDATE threats SET status = ? WHERE id = ?");
            $stmt->execute([$status, $threatId]);
            
            if ($status === 'RESOLVED') {
                $stmt = $pdo->prepare("UPDATE threats SET resolved_at = NOW() WHERE id = ?");
                $stmt->execute([$threatId]);
            }
            
            sendResponse([
                'threat_id' => $threatId,
                'status' => $status,
                'message' => 'Threat updated successfully'
            ]);
            break;
            
        default:
            sendError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>
