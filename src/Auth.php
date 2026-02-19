<?php
/**
 * SENTINEL v3.1 - User Authentication System
 * Roles: superadmin, admin, editor, viewer
 * 
 * A comprehensive authentication system with JWT support,
 * session management, and role-based access control.
 */

require_once 'db_connect.php';

class SentinelAuth {
    private $pdo;
    private $config;
    private $logger;
    
    /**
     * Default configuration
     */
    private $defaultConfig = [
        'jwt_secret' => 'your-256-bit-secret-key-change-this-in-production',
        'session_lifetime' => 86400, // 24 hours in seconds
        'refresh_token_lifetime' => 604800, // 7 days in seconds
        'token_bytes' => 32,
        'bcrypt_cost' => 12,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'cookie_secure' => false, // Set to true in production with HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ];
    
    /**
     * Role hierarchy for permission checking
     */
    private $roleHierarchy = [
        'viewer' => 1,
        'editor' => 2,
        'admin' => 3,
        'superadmin' => 4
    ];
    
    /**
     * Constructor
     */
    public function __construct(array $config = []) {
        $this->pdo = SentinelDB::getInstance();
        $this->config = array_merge($this->defaultConfig, $config);
        $this->initLogger();
        $this->initTables();
    }
    
    /**
     * Initialize logger
     */
    private function initLogger() {
        $this->logger = new class {
            public function log($level, $message, $context = []) {
                $logEntry = date('Y-m-d H:i:s') . " [$level] $message";
                if (!empty($context)) {
                    $logEntry .= " " . json_encode($context);
                }
                error_log($logEntry);
            }
        };
    }
    
    /**
     * Initialize database tables
     */
    private function initTables() {
        try {
            // Users table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('superadmin', 'admin', 'editor', 'viewer') DEFAULT 'viewer',
                    twofa_secret VARCHAR(255) NULL,
                    twofa_enabled BOOLEAN DEFAULT FALSE,
                    full_name VARCHAR(100),
                    department VARCHAR(50),
                    phone VARCHAR(20),
                    last_login_ip VARCHAR(45),
                    last_login_at TIMESTAMP NULL,
                    failed_login_attempts INT DEFAULT 0,
                    locked_until TIMESTAMP NULL,
                    password_changed_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    is_active BOOLEAN DEFAULT TRUE,
                    is_deleted BOOLEAN DEFAULT FALSE,
                    deleted_at TIMESTAMP NULL,
                    
                    INDEX idx_role (role),
                    INDEX idx_email (email),
                    INDEX idx_active (is_active),
                    INDEX idx_username (username),
                    INDEX idx_locked (locked_until)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Sessions table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS user_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    session_token VARCHAR(255) UNIQUE NOT NULL,
                    refresh_token VARCHAR(255) UNIQUE NOT NULL,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    device_info JSON,
                    expires_at TIMESTAMP NOT NULL,
                    refresh_expires_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_token (session_token),
                    INDEX idx_refresh (refresh_token),
                    INDEX idx_expires (expires_at),
                    INDEX idx_refresh_expires (refresh_expires_at),
                    INDEX idx_user_activity (user_id, last_activity)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Login attempts table for security
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50),
                    ip_address VARCHAR(45) NOT NULL,
                    success BOOLEAN DEFAULT FALSE,
                    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    
                    INDEX idx_ip (ip_address),
                    INDEX idx_username (username),
                    INDEX idx_attempted (attempted_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Create default admin if needed
            $this->createDefaultAdmin();
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to initialize tables', ['error' => $e->getMessage()]);
            throw new Exception('Database initialization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Create default admin user if no users exist
     */
    private function createDefaultAdmin() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('superadmin', 'admin')");
            $adminCount = $stmt->fetchColumn();
            
            if ($adminCount == 0) {
                $password_hash = password_hash('Admin123!', PASSWORD_BCRYPT, ['cost' => $this->config['bcrypt_cost']]);
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO users (
                        username, email, password_hash, role, full_name, department, twofa_enabled
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    'admin',
                    'admin@sentinel.local',
                    $password_hash,
                    'superadmin',
                    'System Administrator',
                    'IT Security',
                    false
                ]);
                
                $this->logger->log('INFO', 'Default admin user created');
            }
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to create default admin', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Authenticate user login
     */
    public function login(string $username, string $password, ?array $deviceInfo = null): array {
        try {
            // Record login attempt
            $this->recordLoginAttempt($username, false);
            
            // Check for account lockout
            if ($this->isAccountLocked($username)) {
                return $this->errorResponse('Account temporarily locked. Please try again later.');
            }
            
            // Get user
            $user = $this->getUserByUsernameOrEmail($username);
            
            if (!$user || !$this->verifyPassword($password, $user['password_hash'])) {
                $this->incrementFailedAttempts($username);
                return $this->errorResponse('Invalid credentials');
            }
            
            // Check if user is active
            if (!$user['is_active']) {
                return $this->errorResponse('Account is disabled');
            }
            
            // Reset failed attempts on successful login
            $this->resetFailedAttempts($user['id']);
            
            // Generate tokens
            $tokens = $this->generateTokens();
            $expires = $this->getExpiryTime();
            $refreshExpires = $this->getRefreshExpiryTime();
            
            // Store session
            $this->createSession($user['id'], $tokens, $expires, $refreshExpires, $deviceInfo);
            
            // Update last login info
            $this->updateLastLogin($user['id']);
            
            // Record successful login
            $this->recordLoginAttempt($username, true);
            
            // Remove sensitive data
            $user = $this->sanitizeUserData($user);
            
            $this->logger->log('INFO', 'User logged in successfully', ['user_id' => $user['id'], 'username' => $user['username']]);
            
            return [
                'success' => true,
                'session_token' => $tokens['session'],
                'refresh_token' => $tokens['refresh'],
                'expires_at' => $expires,
                'refresh_expires_at' => $refreshExpires,
                'user' => $user
            ];
            
        } catch (Exception $e) {
            $this->logger->log('ERROR', 'Login failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Authentication failed');
        }
    }
    
    /**
     * Validate session token
     */
    public function validateToken(string $token): ?array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, s.session_token, s.expires_at, s.device_info, s.last_activity
                FROM user_sessions s
                JOIN users u ON s.user_id = u.id
                WHERE s.session_token = ? 
                AND s.expires_at > NOW()
                AND u.is_active = TRUE
                AND u.is_deleted = FALSE
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update last activity
                $this->updateSessionActivity($token);
                return $this->sanitizeUserData($user);
            }
            
            return null;
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Token validation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Logout user
     */
    public function logout(string $token): array {
        try {
            $this->pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?")
                 ->execute([$token]);
            
            $this->logger->log('INFO', 'User logged out', ['token' => substr($token, 0, 10) . '...']);
            
            return ['success' => true, 'message' => 'Logged out successfully'];
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Logout failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Logout failed');
        }
    }
    
    /**
     * Refresh session token
     */
    public function refreshToken(string $refreshToken): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, u.role, u.is_active, u.is_deleted
                FROM user_sessions s
                JOIN users u ON s.user_id = u.id
                WHERE s.refresh_token = ? 
                AND s.refresh_expires_at > NOW()
                AND u.is_active = TRUE
                AND u.is_deleted = FALSE
            ");
            $stmt->execute([$refreshToken]);
            $session = $stmt->fetch();
            
            if (!$session) {
                return $this->errorResponse('Invalid or expired refresh token');
            }
            
            // Generate new tokens
            $newTokens = $this->generateTokens();
            $expires = $this->getExpiryTime();
            $refreshExpires = $this->getRefreshExpiryTime();
            
            // Update session
            $stmt = $this->pdo->prepare("
                UPDATE user_sessions 
                SET session_token = ?, refresh_token = ?, expires_at = ?, refresh_expires_at = ?, last_activity = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $newTokens['session'],
                $newTokens['refresh'],
                $expires,
                $refreshExpires,
                $session['id']
            ]);
            
            $this->logger->log('INFO', 'Token refreshed', ['session_id' => $session['id']]);
            
            return [
                'success' => true,
                'session_token' => $newTokens['session'],
                'refresh_token' => $newTokens['refresh'],
                'expires_at' => $expires,
                'refresh_expires_at' => $refreshExpires
            ];
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Token refresh failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Token refresh failed');
        }
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array {
        $token = $this->extractTokenFromRequest();
        
        if (!$token) {
            return null;
        }
        
        return $this->validateToken($token);
    }
    
    /**
     * Require authentication with optional role check
     */
    public function requireAuth(?string $minimumRole = null): array {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->sendUnauthorizedResponse();
        }
        
        if ($minimumRole && !$this->hasRole($user, $minimumRole)) {
            $this->sendForbiddenResponse();
        }
        
        return $user;
    }
    
    /**
     * Check if user has required role
     */
    public function hasRole(array $user, string $requiredRole): bool {
        $userWeight = $this->roleHierarchy[$user['role']] ?? 0;
        $requiredWeight = $this->roleHierarchy[$requiredRole] ?? 0;
        
        return $userWeight >= $requiredWeight;
    }
    
    /**
     * List users with optional role filter
     */
    public function listUsers(?string $role = null): array {
        try {
            $sql = "
                SELECT id, username, email, role, full_name, department, 
                       is_active, last_login_at, created_at, twofa_enabled
                FROM users
                WHERE is_deleted = FALSE
            ";
            $params = [];
            
            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to list users', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Create new user (admin only)
     */
    public function createUser(array $userData): array {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return $this->errorResponse("Missing required field: $field");
                }
            }
            
            // Check if username or email already exists
            if ($this->userExists($userData['username'], $userData['email'])) {
                return $this->errorResponse('Username or email already exists');
            }
            
            // Hash password
            $password_hash = password_hash($userData['password'], PASSWORD_BCRYPT, [
                'cost' => $this->config['bcrypt_cost']
            ]);
            
            // Insert user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (
                    username, email, password_hash, role, full_name, department, phone
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userData['username'],
                $userData['email'],
                $password_hash,
                $userData['role'],
                $userData['full_name'] ?? null,
                $userData['department'] ?? null,
                $userData['phone'] ?? null
            ]);
            
            $userId = $this->pdo->lastInsertId();
            
            $this->logger->log('INFO', 'User created', ['user_id' => $userId, 'username' => $userData['username']]);
            
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ];
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to create user', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to create user');
        }
    }
    
    /**
     * Update user
     */
    public function updateUser(int $userId, array $userData): array {
        try {
            $updates = [];
            $params = [];
            
            $allowedFields = ['full_name', 'department', 'phone', 'role', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($userData[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $userData[$field];
                }
            }
            
            if (empty($updates)) {
                return $this->errorResponse('No fields to update');
            }
            
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $this->logger->log('INFO', 'User updated', ['user_id' => $userId]);
            
            return ['success' => true, 'message' => 'User updated successfully'];
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to update user', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to update user');
        }
    }
    
    /**
     * Delete user (soft delete)
     */
    public function deleteUser(int $userId): array {
        try {
            // Check if this is the last superadmin
            if ($this->isLastSuperadmin($userId)) {
                return $this->errorResponse('Cannot delete the last superadmin');
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET is_deleted = TRUE, deleted_at = NOW(), is_active = FALSE
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            
            // Also delete all sessions for this user
            $this->pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?")
                 ->execute([$userId]);
            
            $this->logger->log('INFO', 'User deleted', ['user_id' => $userId]);
            
            return ['success' => true, 'message' => 'User deleted successfully'];
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to delete user', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to delete user');
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): array {
        try {
            // Get current password hash
            $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
                return $this->errorResponse('Current password is incorrect');
            }
            
            // Update password
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT, [
                'cost' => $this->config['bcrypt_cost']
            ]);
            
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET password_hash = ?, password_changed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$newHash, $userId]);
            
            // Invalidate all sessions except current one
            $currentToken = $this->extractTokenFromRequest();
            if ($currentToken) {
                $this->pdo->prepare("
                    DELETE FROM user_sessions 
                    WHERE user_id = ? AND session_token != ?
                ")->execute([$userId, $currentToken]);
            }
            
            $this->logger->log('INFO', 'Password changed', ['user_id' => $userId]);
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to change password', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to change password');
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById(int $userId): ?array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, role, full_name, department, phone,
                       is_active, last_login_at, created_at, twofa_enabled
                FROM users
                WHERE id = ? AND is_deleted = FALSE
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch() ?: null;
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to get user', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int {
        try {
            $stmt = $this->pdo->exec("
                DELETE FROM user_sessions 
                WHERE expires_at < NOW() OR refresh_expires_at < NOW()
            ");
            
            $this->logger->log('INFO', 'Expired sessions cleaned up', ['count' => $stmt]);
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logger->log('ERROR', 'Failed to cleanup sessions', ['error' => $e->getMessage()]);
            return 0;
        }
    }
    
    // ==================== PRIVATE HELPER METHODS ====================
    
    /**
     * Get user by username or email
     */
    private function getUserByUsernameOrEmail(string $username): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE (username = ? OR email = ?) 
            AND is_deleted = FALSE
        ");
        $stmt->execute([$username, $username]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Check if account is locked
     */
    private function isAccountLocked(string $username): bool {
        $stmt = $this->pdo->prepare("
            SELECT locked_until FROM users 
            WHERE (username = ? OR email = ?) 
            AND locked_until > NOW()
        ");
        $stmt->execute([$username, $username]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Increment failed login attempts
     */
    private function incrementFailedAttempts(string $username) {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET failed_login_attempts = failed_login_attempts + 1,
                locked_until = CASE 
                    WHEN failed_login_attempts + 1 >= ? 
                    THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                    ELSE NULL
                END
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([
            $this->config['max_login_attempts'],
            $this->config['lockout_duration'],
            $username,
            $username
        ]);
    }
    
    /**
     * Reset failed attempts on successful login
     */
    private function resetFailedAttempts(int $userId) {
        $this->pdo->prepare("
            UPDATE users 
            SET failed_login_attempts = 0, locked_until = NULL
            WHERE id = ?
        ")->execute([$userId]);
    }
    
    /**
     * Record login attempt
     */
    private function recordLoginAttempt(string $username, bool $success) {
        $stmt = $this->pdo->prepare("
            INSERT INTO login_attempts (username, ip_address, success)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $username,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $success
        ]);
    }
    
    /**
     * Generate session and refresh tokens
     */
    private function generateTokens(): array {
        return [
            'session' => bin2hex(random_bytes($this->config['token_bytes'])),
            'refresh' => bin2hex(random_bytes($this->config['token_bytes']))
        ];
    }
    
    /**
     * Get session expiry time
     */
    private function getExpiryTime(): string {
        return date('Y-m-d H:i:s', time() + $this->config['session_lifetime']);
    }
    
    /**
     * Get refresh token expiry time
     */
    private function getRefreshExpiryTime(): string {
        return date('Y-m-d H:i:s', time() + $this->config['refresh_token_lifetime']);
    }
    
    /**
     * Create new session
     */
    private function createSession(int $userId, array $tokens, string $expires, string $refreshExpires, ?array $deviceInfo) {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (
                user_id, session_token, refresh_token, ip_address, 
                user_agent, device_info, expires_at, refresh_expires_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $tokens['session'],
            $tokens['refresh'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $deviceInfo ? json_encode($deviceInfo) : null,
            $expires,
            $refreshExpires
        ]);
    }
    
    /**
     * Update last login information
     */
    private function updateLastLogin(int $userId) {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET last_login_at = NOW(), last_login_ip = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_SERVER['REMOTE_ADDR'] ?? null,
            $userId
        ]);
    }
    
    /**
     * Update session activity
     */
    private function updateSessionActivity(string $token) {
        $this->pdo->prepare("
            UPDATE user_sessions 
            SET last_activity = NOW() 
            WHERE session_token = ?
        ")->execute([$token]);
    }
    
    /**
     * Check if user exists
     */
    private function userExists(string $username, string $email): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM users 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if user is last superadmin
     */
    private function isLastSuperadmin(int $userId): bool {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin' AND is_deleted = FALSE");
        $superadminCount = $stmt->fetchColumn();
        
        if ($superadminCount <= 1) {
            $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userRole = $stmt->fetchColumn();
            
            return $userRole === 'superadmin';
        }
        
        return false;
    }
    
    /**
     * Verify password
     */
    private function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * Sanitize user data by removing sensitive fields
     */
    private function sanitizeUserData(array $user): array {
        $sensitive = ['password_hash', 'twofa_secret', 'failed_login_attempts', 'locked_until'];
        foreach ($sensitive as $field) {
            unset($user[$field]);
        }
        return $user;
    }
    
    /**
     * Extract token from request headers
     */
    private function extractTokenFromRequest(): ?string {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // Check cookies as fallback
        return $_COOKIE['session_token'] ?? null;
    }
    
    /**
     * Send unauthorized response
     */
    private function sendUnauthorizedResponse() {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => 'Authentication required'
        ]));
    }
    
    /**
     * Send forbidden response
     */
    private function sendForbiddenResponse() {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode([
            'success' => false,
            'error' => 'Forbidden',
            'message' => 'Insufficient permissions'
        ]));
    }
    
    /**
     * Create error response
     */
    private function errorResponse(string $message): array {
        return [
            'success' => false,
            'error' => $message
        ];
    }
    
    /**
     * Destructor - cleanup expired sessions occasionally
     */
    public function __destruct() {
        // 5% chance to cleanup expired sessions on destruction
        if (rand(1, 100) <= 5) {
            $this->cleanupExpiredSessions();
        }
    }
}
