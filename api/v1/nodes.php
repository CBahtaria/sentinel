<?php
/**
 * UEDF SENTINEL v4.0 - API v1 - Nodes
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/Database/Connection.php';
require_once __DIR__ . '/../../src/Auth/TokenAuth.php';

// Verify API token
TokenAuth::verify();

$db = DatabaseConnection::getInstance()->getConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT * FROM nodes WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch();
        } else {
            $stmt = $db->query("SELECT * FROM nodes WHERE is_deleted = 0");
            $data = $stmt->fetchAll();
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'POST':
        // Create new node (admin only)
        // Add validation and insert
        break;
        
    case 'PUT':
        // Update node
        break;
        
    case 'DELETE':
        // Delete node
        break;
}
?>
