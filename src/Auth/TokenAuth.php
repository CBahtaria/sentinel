<?php
/**
 * API Token Authentication
 */
class TokenAuth {
    public static function verify() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? $_GET['token'] ?? '';
        
        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        // Remove 'Bearer ' prefix if present
        $token = str_replace('Bearer ', '', $token);
        
        // Verify token (you should store tokens in database)
        $valid_tokens = ['your-api-token-here']; // Move to database
        
        if (!in_array($token, $valid_tokens)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
    }
    
    public static function generate($user_id) {
        return bin2hex(random_bytes(32));
    }
}
?>
