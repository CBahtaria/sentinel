<?php
/**
 * UEDF SENTINEL v5.0 - Database Setup
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

echo "========================================\n";
echo "UEDF SENTINEL v5.0 - Database Setup\n";
echo "========================================\n\n";

try {
    // Connect to MySQL
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "[1/6] Connecting to MySQL...\n";
    echo "✓ Connected successfully\n\n";
    
    // Create database
    echo "[2/6] Creating database 'uedf_sentinel'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS uedf_sentinel");
    $pdo->exec("USE uedf_sentinel");
    echo "✓ Database created/verified\n\n";
    
    // Create tables
    echo "[3/6] Creating tables...\n";
    
    $tables = [
        "users" => "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100),
                role ENUM('commander','operator','analyst','viewer') DEFAULT 'viewer',
                two_factor_enabled BOOLEAN DEFAULT FALSE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME,
                INDEX idx_username (username)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        "drones" => "
            CREATE TABLE IF NOT EXISTS drones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                status ENUM('ACTIVE','STANDBY','MAINTENANCE','OFFLINE') DEFAULT 'STANDBY',
                battery_level INT DEFAULT 100,
                location_lat DECIMAL(10,8),
                location_lng DECIMAL(11,8),
                altitude INT DEFAULT 0,
                speed INT DEFAULT 0,
                last_update DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        "threats" => "
            CREATE TABLE IF NOT EXISTS threats (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(100) NOT NULL,
                severity ENUM('CRITICAL','HIGH','MEDIUM','LOW') DEFAULT 'MEDIUM',
                status ENUM('ACTIVE','INVESTIGATING','RESOLVED','FALSE_ALARM') DEFAULT 'ACTIVE',
                location VARCHAR(100),
                location_lat DECIMAL(10,8),
                location_lng DECIMAL(11,8),
                description TEXT,
                detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                resolved_at DATETIME,
                resolved_by INT,
                INDEX idx_status_severity (status, severity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        "nodes" => "
            CREATE TABLE IF NOT EXISTS nodes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                type VARCHAR(50),
                status ENUM('ACTIVE','INACTIVE','MAINTENANCE') DEFAULT 'ACTIVE',
                location_lat DECIMAL(10,8),
                location_lng DECIMAL(11,8),
                last_seen DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        "audit_logs" => "
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                username VARCHAR(50),
                action VARCHAR(255),
                ip_address VARCHAR(45),
                user_agent TEXT,
                details TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_action (action),
                INDEX idx_timestamp (timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        
        "drone_video_streams" => "
            CREATE TABLE IF NOT EXISTS drone_video_streams (
                id INT AUTO_INCREMENT PRIMARY KEY,
                drone_id INT,
                stream_url VARCHAR(500),
                is_recording BOOLEAN DEFAULT FALSE,
                pip_enabled BOOLEAN DEFAULT FALSE,
                quality VARCHAR(20),
                started_at DATETIME,
                ended_at DATETIME,
                INDEX idx_drone (drone_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        "
    ];
    
    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        echo "  ✓ $name table created\n";
    }
    echo "\n";
    
    // Insert default users
    echo "[4/6] Inserting default data...\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $user_count = $stmt->fetchColumn();
    
    if ($user_count == 0) {
        $users = [
            ['commander', password_hash('Password123!', PASSWORD_DEFAULT), 'Gen. Bartaria', 'commander'],
            ['operator', password_hash('Password123!', PASSWORD_DEFAULT), 'Maj. Dlamini', 'operator'],
            ['analyst', password_hash('Password123!', PASSWORD_DEFAULT), 'Capt. Nkosi', 'analyst'],
            ['viewer', password_hash('Password123!', PASSWORD_DEFAULT), 'Lt. Mamba', 'viewer']
        ];
        
        $insert = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        foreach ($users as $user) {
            $insert->execute($user);
        }
        echo "  ✓ Default users created\n";
    } else {
        echo "  ✓ Users already exist, skipping\n";
    }
    
    // Insert sample drones
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM drones");
    $stmt->execute();
    $drone_count = $stmt->fetchColumn();
    
    if ($drone_count == 0) {
        $drones = [
            ['DRONE-001', 'ACTIVE', 95, 150, 12],
            ['DRONE-002', 'ACTIVE', 87, 200, 15],
            ['DRONE-003', 'STANDBY', 100, 0, 0],
            ['DRONE-004', 'MAINTENANCE', 45, 0, 0],
            ['DRONE-005', 'ACTIVE', 72, 120, 10],
            ['DRONE-006', 'ACTIVE', 88, 180, 14],
            ['DRONE-007', 'STANDBY', 100, 0, 0],
            ['DRONE-008', 'ACTIVE', 93, 160, 13],
            ['DRONE-009', 'ACTIVE', 78, 140, 11],
            ['DRONE-010', 'STANDBY', 100, 0, 0],
            ['DRONE-011', 'MAINTENANCE', 30, 0, 0],
            ['DRONE-012', 'ACTIVE', 82, 170, 12],
            ['DRONE-013', 'ACTIVE', 91, 190, 16],
            ['DRONE-014', 'STANDBY', 100, 0, 0],
            ['DRONE-015', 'ACTIVE', 76, 130, 9]
        ];
        
        $insert = $pdo->prepare("INSERT INTO drones (name, status, battery_level, altitude, speed) VALUES (?, ?, ?, ?, ?)");
        foreach ($drones as $drone) {
            $insert->execute($drone);
        }
        echo "  ✓ Sample drones created\n";
    } else {
        echo "  ✓ Drones already exist, skipping\n";
    }
    
    // Insert sample threats
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM threats");
    $stmt->execute();
    $threat_count = $stmt->fetchColumn();
    
    if ($threat_count == 0) {
        $threats = [
            ['Unauthorized Access Attempt', 'CRITICAL', 'ACTIVE', 'Sector 4', 'Multiple failed login attempts from external IP'],
            ['Drone Intrusion Detected', 'HIGH', 'ACTIVE', 'Sector 7', 'Unidentified drone crossing border'],
            ['Suspicious Network Activity', 'MEDIUM', 'ACTIVE', 'Sector 2', 'Unusual port scanning detected'],
            ['Perimeter Breach Attempt', 'CRITICAL', 'ACTIVE', 'Sector 9', 'Physical breach attempt at checkpoint'],
            ['Unusual Weather Pattern', 'LOW', 'ACTIVE', 'Sector 1', 'Abnormal atmospheric readings']
        ];
        
        $insert = $pdo->prepare("INSERT INTO threats (type, severity, status, location, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($threats as $threat) {
            $insert->execute($threat);
        }
        echo "  ✓ Sample threats created\n";
    } else {
        echo "  ✓ Threats already exist, skipping\n";
    }
    
    // Insert sample nodes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM nodes");
    $stmt->execute();
    $node_count = $stmt->fetchColumn();
    
    if ($node_count == 0) {
        $nodes = [
            ['Command Center', 'HQ', 'ACTIVE'],
            ['Radar Station Alpha', 'RADAR', 'ACTIVE'],
            ['Radar Station Bravo', 'RADAR', 'ACTIVE'],
            ['Communication Tower 1', 'COMMS', 'ACTIVE'],
            ['Communication Tower 2', 'COMMS', 'MAINTENANCE'],
            ['Observation Post 1', 'OBSERVATION', 'ACTIVE'],
            ['Observation Post 2', 'OBSERVATION', 'ACTIVE'],
            ['Observation Post 3', 'OBSERVATION', 'INACTIVE'],
            ['Supply Depot', 'LOGISTICS', 'ACTIVE'],
            ['Fuel Station', 'LOGISTICS', 'ACTIVE'],
            ['Maintenance Bay', 'MAINTENANCE', 'ACTIVE'],
            ['Drone Launch Pad 1', 'LAUNCH', 'ACTIVE'],
            ['Drone Launch Pad 2', 'LAUNCH', 'ACTIVE'],
            ['Emergency Shelter 1', 'SHELTER', 'ACTIVE'],
            ['Emergency Shelter 2', 'SHELTER', 'ACTIVE']
        ];
        
        $insert = $pdo->prepare("INSERT INTO nodes (name, type, status) VALUES (?, ?, ?)");
        foreach ($nodes as $node) {
            $insert->execute($node);
        }
        echo "  ✓ Sample nodes created\n";
    } else {
        echo "  ✓ Nodes already exist, skipping\n";
    }
    
    echo "\n[5/6] Verifying installation...\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  ✓ $table: $count records\n";
    }
    
    echo "\n[6/6] Setup complete!\n";
    echo "========================================\n";
    echo "Database: uedf_sentinel\n";
    echo "Host: localhost\n";
    echo "========================================\n\n";
    echo "Default Users:\n";
    echo "-------------\n";
    echo "Commander: commander / Password123!\n";
    echo "Operator: operator / Password123!\n";
    echo "Analyst: analyst / Password123!\n";
    echo "Viewer: viewer / Password123!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
