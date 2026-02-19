<?php
// Complete Database Schema for Bartarian Defence
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🛡️ BARTARIAN DEFENCE - DATABASE SETUP\n";
    echo "=====================================\n\n";
    
    // 1. USERS TABLE (Enhanced)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE,
            full_name VARCHAR(100),
            role ENUM('viewer','analyst','operator','commander','admin','superadmin') DEFAULT 'viewer',
            department VARCHAR(50),
            phone VARCHAR(20),
            avatar VARCHAR(255),
            twofa_secret VARCHAR(255),
            twofa_enabled BOOLEAN DEFAULT FALSE,
            last_login DATETIME,
            last_login_ip VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            INDEX idx_username (username),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Users table created\n";
    
    // 2. DRONES TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS drones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            model VARCHAR(50),
            status ENUM('online','offline','mission','maintenance','standby') DEFAULT 'standby',
            battery INT DEFAULT 100,
            altitude INT DEFAULT 0,
            speed INT DEFAULT 0,
            heading INT DEFAULT 0,
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            location_sector VARCHAR(20),
            mission_id INT,
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_mission (mission_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Drones table created\n";
    
    // 3. DRONE MISSIONS TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS drone_missions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            drone_id INT,
            waypoints JSON,
            start_time DATETIME,
            end_time DATETIME,
            status ENUM('planned','active','completed','aborted') DEFAULT 'planned',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Missions table created\n";
    
    // 4. DRONE TELEMETRY TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS drone_telemetry (
            id INT AUTO_INCREMENT PRIMARY KEY,
            drone_id INT NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            battery INT,
            altitude INT,
            speed INT,
            heading INT,
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            temperature DECIMAL(5,2),
            signal_strength INT,
            FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE CASCADE,
            INDEX idx_drone_time (drone_id, timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Telemetry table created\n";
    
    // 5. THREATS TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS threats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type ENUM('drone','vehicle','personnel','cyber','weather','other') DEFAULT 'other',
            severity ENUM('low','medium','high','critical') DEFAULT 'medium',
            status ENUM('active','investigating','resolved','false_alarm') DEFAULT 'active',
            description TEXT,
            location_sector VARCHAR(20),
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            detected_by VARCHAR(50),
            detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            resolved_by INT,
            resolved_at DATETIME,
            confidence INT DEFAULT 0,
            threat_level INT DEFAULT 0,
            INDEX idx_status (status),
            INDEX idx_severity (severity),
            FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Threats table created\n";
    
    // 6. NODES TABLE (Network)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS nodes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            type ENUM('Command','Control','Database','Sensor','Communication','Drone','Radar','Camera','Satellite','Analysis') DEFAULT 'Sensor',
            status ENUM('online','offline','warning','maintenance') DEFAULT 'online',
            ip_address VARCHAR(45),
            cpu_usage INT DEFAULT 0,
            memory_usage INT DEFAULT 0,
            disk_usage INT DEFAULT 0,
            uptime INT DEFAULT 0,
            location VARCHAR(100),
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            redundancy_level ENUM('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
            backup_count INT DEFAULT 0,
            INDEX idx_status (status),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Nodes table created\n";
    
    // 7. NODE CONNECTIONS TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS node_connections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_node INT NOT NULL,
            target_node INT NOT NULL,
            connection_type VARCHAR(20) DEFAULT 'primary',
            bandwidth INT,
            latency INT,
            status ENUM('active','degraded','failed') DEFAULT 'active',
            last_check TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (source_node) REFERENCES nodes(id) ON DELETE CASCADE,
            FOREIGN KEY (target_node) REFERENCES nodes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_connection (source_node, target_node)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Node connections table created\n";
    
    // 8. AUDIT LOGS TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            username VARCHAR(50),
            action VARCHAR(100) NOT NULL,
            category VARCHAR(50),
            description TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            status ENUM('success','failure','warning') DEFAULT 'success',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_action (action),
            INDEX idx_created (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Audit logs table created\n";
    
    // 9. NOTIFICATIONS TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            type ENUM('info','warning','success','danger') DEFAULT 'info',
            title VARCHAR(100) NOT NULL,
            message TEXT,
            link VARCHAR(255),
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            INDEX idx_user_read (user_id, is_read),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Notifications table created\n";
    
    // 10. SYSTEM SETTINGS TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_type ENUM('string','integer','boolean','json') DEFAULT 'string',
            description VARCHAR(255),
            updated_by INT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ System settings table created\n";
    
    // 11. API KEYS TABLE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            api_key VARCHAR(64) UNIQUE NOT NULL,
            name VARCHAR(100),
            permissions JSON,
            last_used DATETIME,
            expires_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_key (api_key),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ API keys table created\n";
    
    // 12. INSERT SAMPLE DATA
    echo "\n📊 Inserting sample data...\n";
    
    // Check if users exist
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count == 0) {
        // Insert default users
        $users = [
            ['commander', password_hash('Password123!', PASSWORD_DEFAULT), 'commander@defence.bd', 'Gen. Bartaria', 'commander'],
            ['operator', password_hash('Password123!', PASSWORD_DEFAULT), 'operator@defence.bd', 'Maj. Dlamini', 'operator'],
            ['analyst', password_hash('Password123!', PASSWORD_DEFAULT), 'analyst@defence.bd', 'Capt. Nkosi', 'analyst'],
            ['viewer', password_hash('Password123!', PASSWORD_DEFAULT), 'viewer@defence.bd', 'Lt. Mamba', 'viewer']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        foreach ($users as $user) {
            $stmt->execute($user);
        }
        echo "   ✅ Added 4 users\n";
    }
    
    // Insert sample drones
    $count = $pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn();
    if ($count == 0) {
        $drones = [
            ['EAGLE-1', 'MQ-9 Reaper', 'online', 95, 4500, 120, 180, 'Sector 7'],
            ['HAWK-2', 'RQ-4 Global Hawk', 'mission', 78, 5200, 110, 270, 'Sector 3'],
            ['FALCON-3', 'MQ-1 Predator', 'standby', 100, 0, 0, 0, 'Base'],
            ['RAVEN-4', 'ScanEagle', 'online', 72, 3200, 85, 90, 'Sector 9'],
            ['PHOENIX-5', 'Wing Loong', 'maintenance', 45, 0, 0, 0, 'Hangar'],
            ['VIPER-6', 'Bayraktar TB2', 'online', 91, 3800, 95, 45, 'Sector 2']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO drones (name, model, status, battery, altitude, speed, heading, location_sector) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($drones as $drone) {
            $stmt->execute($drone);
        }
        echo "   ✅ Added 6 drones\n";
    }
    
    // Insert sample threats
    $count = $pdo->query("SELECT COUNT(*) FROM threats")->fetchColumn();
    if ($count == 0) {
        $threats = [
            ['Unauthorized Drone', 'drone', 'medium', 'active', 'Sector 7', 'Radar A7', 85],
            ['Suspicious Vehicle', 'vehicle', 'high', 'investigating', 'Sector 3', 'Ground Patrol', 92],
            ['Cyber Attack Attempt', 'cyber', 'critical', 'active', 'Network', 'IDS', 98],
            ['Weather Anomaly', 'weather', 'low', 'active', 'Sector 9', 'Weather Station', 45],
            ['Unknown Personnel', 'personnel', 'medium', 'resolved', 'Perimeter', 'Guard Post', 100]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO threats (name, type, severity, status, location_sector, detected_by, confidence) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($threats as $threat) {
            $stmt->execute($threat);
        }
        echo "   ✅ Added 5 threats\n";
    }
    
    // Insert sample nodes
    $count = $pdo->query("SELECT COUNT(*) FROM nodes")->fetchColumn();
    if ($count == 0) {
        $nodes = [
            ['CMD-NODE', 'Command', 'online', 45, 62, 34, 720, 'HQ', 'HIGH', 3],
            ['DRONE-CTRL', 'Control', 'online', 32, 45, 28, 720, 'Ops Center', 'MEDIUM', 2],
            ['THREAT-DB', 'Database', 'online', 78, 85, 67, 720, 'Data Center', 'HIGH', 3],
            ['SURVEILLANCE', 'Sensor', 'warning', 92, 76, 45, 720, 'Sector 7', 'LOW', 1],
            ['COMM-LINK', 'Communication', 'online', 23, 34, 12, 720, 'Comms Tower', 'HIGH', 4],
            ['EAGLE-1', 'Drone', 'online', 45, 62, 34, 720, 'Sector 7', 'MEDIUM', 2],
            ['HAWK-2', 'Drone', 'online', 61, 52, 41, 720, 'Sector 3', 'MEDIUM', 2],
            ['SECTOR-7-RADAR', 'Radar', 'warning', 88, 91, 56, 720, 'Radar Tower', 'LOW', 1],
            ['SAT-LINK', 'Satellite', 'online', 34, 45, 23, 720, 'Orbit', 'HIGH', 3]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO nodes (name, type, status, cpu_usage, memory_usage, disk_usage, uptime, location, redundancy_level, backup_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($nodes as $node) {
            $stmt->execute($node);
        }
        echo "   ✅ Added 9 nodes\n";
    }
    
    // Insert node connections
    $count = $pdo->query("SELECT COUNT(*) FROM node_connections")->fetchColumn();
    if ($count == 0) {
        $connections = [
            [1, 2, 'primary', 1000, 5, 'active'],
            [1, 3, 'primary', 1000, 5, 'active'],
            [1, 4, 'primary', 100, 20, 'degraded'],
            [1, 5, 'primary', 1000, 5, 'active'],
            [2, 6, 'primary', 500, 10, 'active'],
            [2, 7, 'primary', 500, 10, 'active'],
            [3, 8, 'primary', 1000, 5, 'active'],
            [4, 8, 'secondary', 100, 25, 'degraded'],
            [5, 9, 'primary', 1000, 50, 'active'],
            [2, 1, 'backup', 100, 30, 'active'],
            [3, 1, 'backup', 100, 30, 'active']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO node_connections (source_node, target_node, connection_type, bandwidth, latency, status) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($connections as $conn) {
            $stmt->execute($conn);
        }
        echo "   ✅ Added 11 node connections\n";
    }
    
    // Insert sample notifications
    $count = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    if ($count == 0) {
        $notifications = [
            [1, 'success', 'System Online', 'All systems operational', NULL, 0],
            [NULL, 'warning', 'High CPU Usage', 'SURVEILLANCE node at 92% CPU', NULL, 0],
            [NULL, 'danger', 'Critical Threat', 'Cyber attack detected - Sector 7', NULL, 0],
            [2, 'info', 'Mission Complete', 'Drone EAGLE-1 returned to base', NULL, 0],
            [NULL, 'warning', 'Low Battery', 'PHOENIX-5 battery at 45%', NULL, 0]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link, is_read) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($notifications as $notif) {
            $stmt->execute($notif);
        }
        echo "   ✅ Added 5 notifications\n";
    }
    
    // Insert system settings
    $count = $pdo->query("SELECT COUNT(*) FROM system_settings")->fetchColumn();
    if ($count == 0) {
        $settings = [
            ['system_name', 'Bartarian Defence', 'string', 'System name'],
            ['system_version', '5.0', 'string', 'System version'],
            ['maintenance_mode', 'false', 'boolean', 'Maintenance mode status'],
            ['session_timeout', '3600', 'integer', 'Session timeout in seconds'],
            ['max_login_attempts', '5', 'integer', 'Maximum failed login attempts'],
            ['two_factor_required', 'false', 'boolean', 'Require 2FA for all users']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        echo "   ✅ Added 6 system settings\n";
    }
    
    echo "\n✅ DATABASE SETUP COMPLETE!\n";
    echo "   Total tables: 12\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
