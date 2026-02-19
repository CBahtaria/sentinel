<?php
/**
 * UEDF SENTINEL - Chat API
 */

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($method) {
    case 'GET':
        if ($action === 'messages') {
            $channel = $_GET['channel'] ?? 'general';
            $limit = $_GET['limit'] ?? 50;
            
            $stmt = $db->prepare("
                SELECT * FROM chat_messages 
                WHERE channel = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$channel, $limit]);
            $messages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
            
            echo json_encode(['success' => true, 'messages' => $messages]);
        }
        elseif ($action === 'channels') {
            $stmt = $db->query("SELECT * FROM chat_channels ORDER BY name");
            $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'channels' => $channels]);
        }
        break;
        
    case 'POST':
        if ($action === 'send') {
            $data = json_decode(file_get_contents('php://input'), true);
            $message = trim($data['message'] ?? '');
            $channel = $data['channel'] ?? 'general';
            
            if (empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Message cannot be empty']);
                exit;
            }
            
            $stmt = $db->prepare("
                INSERT INTO chat_messages (user_id, username, user_role, message, channel)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $_SESSION['username'],
                $_SESSION['role'],
                $message,
                $channel
            ]);
            
            $messageId = $db->lastInsertId();
            
            // Get the inserted message
            $stmt = $db->prepare("SELECT * FROM chat_messages WHERE id = ?");
            $stmt->execute([$messageId]);
            $newMessage = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Broadcast via WebSocket (if connected)
            if (function_exists('broadcastToChannel')) {
                broadcastToChannel('chat', [
                    'type' => 'new_message',
                    'data' => $newMessage
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => $newMessage]);
        }
        break;
}
?>
