-- ============================================
-- UEDF SENTINEL v5.0 - Complete Database Schema
-- UMBUTFO ESWATINI DEFENCE FORCE
-- ============================================

-- Drop database if exists (uncomment to reset)
-- DROP DATABASE IF EXISTS uedf_sentinel;

-- Create database
CREATE DATABASE IF NOT EXISTS uedf_sentinel
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE uedf_sentinel;

-- ============================================
-- USERS & AUTHENTICATION
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    full_name VARCHAR(100),
    role ENUM('commander','operator','analyst','viewer','admin') DEFAULT 'viewer',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    avatar VARCHAR(255),
    last_login DATETIME,
    last_ip VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,
    last_activity DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_user_activity (user_id, last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(100) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts (for rate limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    ip_address VARCHAR(45),
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    INDEX idx_ip (ip_address),
    INDEX idx_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DRONE MANAGEMENT
-- ============================================

-- Drones table
CREATE TABLE IF NOT EXISTS drones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(50),
    serial_number VARCHAR(100) UNIQUE,
    status ENUM('ACTIVE','STANDBY','MAINTENANCE','OFFLINE','DEPLOYED','RETURNING','EMERGENCY') DEFAULT 'STANDBY',
    battery_level INT DEFAULT 100,
    flight_hours DECIMAL(10,2) DEFAULT 0,
    last_maintenance DATETIME,
    next_maintenance DATETIME,
    
    -- Location data
    current_lat DECIMAL(10,8),
    current_lng DECIMAL(11,8),
    home_lat DECIMAL(10,8),
    home_lng DECIMAL(11,8),
    altitude INT DEFAULT 0,
    speed INT DEFAULT 0,
    heading INT DEFAULT 0,
    
    -- Telemetry
    camera_status BOOLEAN DEFAULT FALSE,
    recording_status BOOLEAN DEFAULT FALSE,
    signal_strength INT DEFAULT 100,
    temperature DECIMAL(5,2),
    
    -- Mission data
    current_mission_id INT,
    assigned_sector VARCHAR(50),
    
    -- Metadata
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_location (current_lat, current_lng),
    INDEX idx_mission (current_mission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drone telemetry history
CREATE TABLE IF NOT EXISTS drone_telemetry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drone_id INT NOT NULL,
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    altitude INT,
    speed INT,
    heading INT,
    battery_level INT,
    signal_strength INT,
    temperature DECIMAL(5,2),
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE CASCADE,
    INDEX idx_drone_time (drone_id, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drone missions
CREATE TABLE IF NOT EXISTS drone_missions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('PATROL','SURVEILLANCE','RECONNAISSANCE','SEARCH_RESCUE','DELIVERY','TRAINING','MAINTENANCE') DEFAULT 'PATROL',
    status ENUM('PLANNED','ACTIVE','PAUSED','COMPLETED','ABORTED') DEFAULT 'PLANNED',
    
    -- Waypoints as JSON
    waypoints JSON,
    
    -- Schedule
    scheduled_start DATETIME,
    scheduled_end DATETIME,
    actual_start DATETIME,
    actual_end DATETIME,
    
    -- Assigned drones (comma-separated IDs)
    assigned_drones TEXT,
    
    -- Area of operation
    sector VARCHAR(50),
    area_polygon JSON,
    
    -- Results
    flight_log TEXT,
    report_generated BOOLEAN DEFAULT FALSE,
    
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_schedule (scheduled_start, scheduled_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drone maintenance records
CREATE TABLE IF NOT EXISTS drone_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drone_id INT NOT NULL,
    type ENUM('SCHEDULED','UNSCHEDULED','EMERGENCY','CALIBRATION','SOFTWARE_UPDATE') DEFAULT 'SCHEDULED',
    description TEXT,
    performed_by VARCHAR(100),
    cost DECIMAL(10,2),
    parts_used TEXT,
    started_at DATETIME,
    completed_at DATETIME,
    next_maintenance DATETIME,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE CASCADE,
    INDEX idx_drone (drone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- THREAT MANAGEMENT
-- ============================================

-- Threats table
CREATE TABLE IF NOT EXISTS threats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    severity ENUM('CRITICAL','HIGH','MEDIUM','LOW','INFO') DEFAULT 'MEDIUM',
    status ENUM('ACTIVE','INVESTIGATING','CONTAINED','RESOLVED','FALSE_ALARM','ESCALATED') DEFAULT 'ACTIVE',
    
    -- Location
    location_name VARCHAR(255),
    sector VARCHAR(50),
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    
    -- Details
    description TEXT,
    indicators TEXT,
    confidence_score INT DEFAULT 50,
    source VARCHAR(100),
    detected_by VARCHAR(100),
    
    -- Timeline
    detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    investigated_at DATETIME,
    contained_at DATETIME,
    resolved_at DATETIME,
    
    -- Response
    assigned_to INT,
    response_actions TEXT,
    drone_dispatched INT,
    
    -- Metadata
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status_severity (status, severity),
    INDEX idx_location (sector),
    INDEX idx_time (detected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Threat intelligence
CREATE TABLE IF NOT EXISTS threat_intel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    threat_id INT,
    source VARCHAR(100),
    data JSON,
    analyzed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    confidence INT,
    notes TEXT,
    FOREIGN KEY (threat_id) REFERENCES threats(id) ON DELETE CASCADE,
    INDEX idx_threat (threat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Threat patterns
CREATE TABLE IF NOT EXISTS threat_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pattern_name VARCHAR(255),
    pattern_data JSON,
    frequency INT DEFAULT 1,
    first_seen DATETIME,
    last_seen DATETIME,
    risk_score INT,
    mitigation TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pattern (pattern_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NODES & INFRASTRUCTURE
-- ============================================

-- Nodes (facilities, towers, etc.)
CREATE TABLE IF NOT EXISTS nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('HQ','RADAR','COMMS','OBSERVATION','LOGISTICS','MAINTENANCE','LAUNCH','SHELTER','SENSOR','GATE') DEFAULT 'SENSOR',
    status ENUM('ACTIVE','INACTIVE','MAINTENANCE','ALERT','OFFLINE') DEFAULT 'ACTIVE',
    
    -- Location
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    altitude INT,
    sector VARCHAR(50),
    
    -- Details
    description TEXT,
    capabilities JSON,
    sensors JSON,
    
    -- Connectivity
    parent_node INT,
    network_id VARCHAR(50),
    ip_address VARCHAR(45),
    
    -- Status
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    uptime INT DEFAULT 0,
    health_score INT DEFAULT 100,
    
    -- Metadata
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_location (lat, lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Node telemetry
CREATE TABLE IF NOT EXISTS node_telemetry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    node_id INT NOT NULL,
    cpu_usage INT,
    memory_usage INT,
    disk_usage INT,
    temperature DECIMAL(5,2),
    signal_strength INT,
    uptime INT,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (node_id) REFERENCES nodes(id) ON DELETE CASCADE,
    INDEX idx_node_time (node_id, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- RECORDINGS & MEDIA
-- ============================================

-- Drone recordings
CREATE TABLE IF NOT EXISTS recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drone_id INT,
    drone_name VARCHAR(50),
    filename VARCHAR(255),
    file_path VARCHAR(500),
    thumbnail_path VARCHAR(500),
    duration INT,
    file_size BIGINT,
    resolution VARCHAR(20),
    fps INT,
    format VARCHAR(10),
    recording_type ENUM('video','snapshot','timelapse','thermal','multispectral') DEFAULT 'video',
    
    -- Location
    location_lat DECIMAL(10,8),
    location_lng DECIMAL(11,8),
    altitude INT,
    sector VARCHAR(50),
    
    -- Camera settings
    camera_angle INT,
    zoom_level INT,
    thermal_range VARCHAR(50),
    
    -- Conditions
    weather_conditions TEXT,
    visibility INT,
    
    -- Tags & metadata
    tags TEXT,
    description TEXT,
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    recorded_at DATETIME,
    processed_at DATETIME,
    
    -- Analysis
    processed BOOLEAN DEFAULT FALSE,
    analyzed BOOLEAN DEFAULT FALSE,
    threat_detected BOOLEAN DEFAULT FALSE,
    detected_objects TEXT,
    confidence_score FLOAT,
    analysis_results JSON,
    
    INDEX idx_drone (drone_id),
    INDEX idx_date (created_at),
    INDEX idx_type (recording_type),
    INDEX idx_analyzed (analyzed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Snapshots (individual frames)
CREATE TABLE IF NOT EXISTS snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recording_id INT,
    drone_id INT,
    timestamp DATETIME,
    file_path VARCHAR(500),
    thumbnail_path VARCHAR(500),
    metadata JSON,
    
    -- Analysis
    analyzed BOOLEAN DEFAULT FALSE,
    detected_objects TEXT,
    threat_level ENUM('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'LOW',
    analysis_results JSON,
    
    INDEX idx_recording (recording_id),
    INDEX idx_drone (drone_id),
    FOREIGN KEY (recording_id) REFERENCES recordings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ANALYTICS & AI
-- ============================================

-- ML predictions
CREATE TABLE IF NOT EXISTS ml_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(100),
    model_version VARCHAR(20),
    prediction_type VARCHAR(50),
    input_data JSON,
    output_data JSON,
    confidence FLOAT,
    execution_time_ms INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    INDEX idx_model (model_name, model_version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics events
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50),
    event_data JSON,
    user_id INT,
    session_id VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_url VARCHAR(500),
    referrer VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (event_type),
    INDEX idx_user (user_id),
    INDEX idx_time (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Performance metrics
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(255),
    load_time_ms INT,
    query_count INT,
    memory_usage_mb DECIMAL(10,2),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    INDEX idx_page (page),
    INDEX idx_time (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY & AUDIT
-- ============================================

-- Audit logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(50),
    action VARCHAR(255),
    category VARCHAR(50),
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_uri VARCHAR(500),
    request_method VARCHAR(10),
    response_code INT,
    execution_time_ms INT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_category (category),
    INDEX idx_timestamp (timestamp),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IP whitelist
CREATE TABLE IF NOT EXISTS ip_whitelist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    description VARCHAR(255),
    added_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API keys
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(100),
    permissions JSON,
    rate_limit INT DEFAULT 100,
    last_used DATETIME,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Two-factor backup codes
CREATE TABLE IF NOT EXISTS two_factor_backup_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(20) NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    used_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS
-- ============================================

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    priority ENUM('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    INDEX idx_user (user_id),
        INDEX idx_user_read (user_id, is_read),
    INDEX idx_priority (priority),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification preferences
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50),
    channel ENUM('email','sms','push','in_app') DEFAULT 'in_app',
    enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pref (user_id, type, channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- REPORTS & DOCUMENTS
-- ============================================

-- Reports
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50),
    format VARCHAR(10),
    data JSON,
    file_path VARCHAR(500),
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_archived BOOLEAN DEFAULT FALSE,
    INDEX idx_type (type),
    INDEX idx_created (created_at),
    INDEX idx_creator (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report schedules
CREATE TABLE IF NOT EXISTS report_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    report_type VARCHAR(50),
    frequency ENUM('daily','weekly','monthly','quarterly','yearly') DEFAULT 'weekly',
    recipients TEXT,
    parameters JSON,
    last_run DATETIME,
    next_run DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_next_run (next_run)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SYSTEM CONFIGURATION
-- ============================================

-- System settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value JSON,
    setting_type ENUM('text','number','boolean','json','array') DEFAULT 'text',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    updated_by INT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scheduled tasks
CREATE TABLE IF NOT EXISTS scheduled_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255),
    task_type VARCHAR(50),
    schedule VARCHAR(100),
    last_run DATETIME,
    next_run DATETIME,
    status ENUM('pending','running','completed','failed','disabled') DEFAULT 'pending',
    result JSON,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_next_run (next_run),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DEFAULT DATA
-- ============================================

-- Insert default users (password: Password123!)
INSERT INTO users (username, password, full_name, email, role, two_factor_enabled, created_at) VALUES
('commander', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gen. Bartaria', 'commander@uedf.sz', 'commander', 0, NOW()),
('operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maj. Dlamini', 'operator@uedf.sz', 'operator', 0, NOW()),
('analyst', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Capt. Nkosi', 'analyst@uedf.sz', 'analyst', 0, NOW()),
('viewer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lt. Mamba', 'viewer@uedf.sz', 'viewer', 0, NOW());

-- Insert default drones
INSERT INTO drones (name, model, status, battery_level, flight_hours, current_lat, current_lng, altitude, speed, last_seen) VALUES
('DRONE-001', 'Mavic 3 Enterprise', 'ACTIVE', 95, 125.5, -26.5225, 31.4658, 150, 12, NOW()),
('DRONE-002', 'Mavic 3 Enterprise', 'ACTIVE', 87, 98.2, -26.5235, 31.4678, 200, 15, NOW()),
('DRONE-003', 'Matrice 300', 'STANDBY', 100, 245.8, -26.5200, 31.4700, 0, 0, NOW()),
('DRONE-004', 'Matrice 300', 'MAINTENANCE', 45, 567.3, -26.5200, 31.4700, 0, 0, NOW()),
('DRONE-005', 'Phantom 4 Pro', 'ACTIVE', 72, 89.1, -26.5245, 31.4665, 120, 10, NOW()),
('DRONE-006', 'Phantom 4 Pro', 'ACTIVE', 88, 134.7, -26.5220, 31.4690, 180, 14, NOW()),
('DRONE-007', 'Mavic 3 Thermal', 'STANDBY', 100, 67.2, -26.5200, 31.4700, 0, 0, NOW()),
('DRONE-008', 'Mavic 3 Thermal', 'ACTIVE', 93, 156.4, -26.5250, 31.4650, 160, 13, NOW()),
('DRONE-009', 'Matrice 350', 'ACTIVE', 78, 234.9, -26.5230, 31.4680, 140, 11, NOW()),
('DRONE-010', 'Matrice 350', 'STANDBY', 100, 187.3, -26.5200, 31.4700, 0, 0, NOW()),
('DRONE-011', 'Inspire 2', 'MAINTENANCE', 30, 423.6, -26.5200, 31.4700, 0, 0, NOW()),
('DRONE-012', 'Inspire 2', 'ACTIVE', 82, 276.8, -26.5240, 31.4660, 170, 12, NOW()),
('DRONE-013', 'Agras T30', 'ACTIVE', 91, 345.2, -26.5210, 31.4690, 190, 16, NOW()),
('DRONE-014', 'Agras T30', 'STANDBY', 100, 298.4, -26.5200, 31.4700, 0, 0, NOW()),
('DRONE-015', 'Mavic 2 Pro', 'ACTIVE', 76, 67.9, -26.5255, 31.4645, 130, 9, NOW());

-- Insert default threats
INSERT INTO threats (type, severity, status, location_name, sector, lat, lng, description, detected_at) VALUES
('Unauthorized Access Attempt', 'CRITICAL', 'ACTIVE', 'Command Center', 'Sector 4', -26.5225, 31.4658, 'Multiple failed login attempts from external IP', DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
('Drone Intrusion Detected', 'HIGH', 'ACTIVE', 'Airspace Alpha', 'Sector 7', -26.5235, 31.4678, 'Unidentified drone crossing restricted airspace', DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
('Suspicious Network Activity', 'MEDIUM', 'ACTIVE', 'Network Hub', 'Sector 2', -26.5200, 31.4700, 'Unusual port scanning detected from external source', DATE_SUB(NOW(), INTERVAL 12 MINUTE)),
('Perimeter Breach Attempt', 'CRITICAL', 'ACTIVE', 'Checkpoint 3', 'Sector 9', -26.5245, 31.4665, 'Physical breach attempt detected at perimeter fence', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
('Unusual Weather Pattern', 'LOW', 'ACTIVE', 'Weather Station', 'Sector 1', -26.5220, 31.4690, 'Abnormal barometric readings detected', DATE_SUB(NOW(), INTERVAL 22 MINUTE)),
('Radar Anomaly', 'MEDIUM', 'ACTIVE', 'Radar Station', 'Sector 3', -26.5250, 31.4650, 'Unusual radar signature detected', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
('Communication Interference', 'HIGH', 'ACTIVE', 'Comms Tower', 'Sector 5', -26.5230, 31.4680, 'Signal jamming detected on primary frequency', DATE_SUB(NOW(), INTERVAL 35 MINUTE)),
('Power Fluctuation', 'MEDIUM', 'ACTIVE', 'Power Station', 'Sector 6', -26.5240, 31.4660, 'Unstable power supply detected', DATE_SUB(NOW(), INTERVAL 45 MINUTE));

-- Insert default nodes
INSERT INTO nodes (name, type, status, lat, lng, sector, description) VALUES
('Command Center', 'HQ', 'ACTIVE', -26.5225, 31.4658, 'Sector 0', 'Main command and control center'),
('Radar Station Alpha', 'RADAR', 'ACTIVE', -26.5235, 31.4678, 'Sector 1', 'Primary radar surveillance'),
('Radar Station Bravo', 'RADAR', 'ACTIVE', -26.5200, 31.4700, 'Sector 2', 'Secondary radar coverage'),
('Communication Tower 1', 'COMMS', 'ACTIVE', -26.5245, 31.4665, 'Sector 3', 'Primary communication relay'),
('Communication Tower 2', 'COMMS', 'MAINTENANCE', -26.5220, 31.4690, 'Sector 4', 'Secondary communication relay'),
('Observation Post 1', 'OBSERVATION', 'ACTIVE', -26.5250, 31.4650, 'Sector 5', 'Northern observation post'),
('Observation Post 2', 'OBSERVATION', 'ACTIVE', -26.5230, 31.4680, 'Sector 6', 'Eastern observation post'),
('Observation Post 3', 'OBSERVATION', 'INACTIVE', -26.5240, 31.4660, 'Sector 7', 'Western observation post'),
('Supply Depot', 'LOGISTICS', 'ACTIVE', -26.5210, 31.4690, 'Sector 8', 'Main supply and equipment depot'),
('Fuel Station', 'LOGISTICS', 'ACTIVE', -26.5255, 31.4645, 'Sector 9', 'Drone refueling station'),
('Maintenance Bay', 'MAINTENANCE', 'ACTIVE', -26.5225, 31.4658, 'Sector 10', 'Drone maintenance facility'),
('Launch Pad 1', 'LAUNCH', 'ACTIVE', -26.5235, 31.4678, 'Sector 11', 'Primary drone launch pad'),
('Launch Pad 2', 'LAUNCH', 'ACTIVE', -26.5200, 31.4700, 'Sector 12', 'Secondary drone launch pad'),
('Emergency Shelter 1', 'SHELTER', 'ACTIVE', -26.5245, 31.4665, 'Sector 13', 'Emergency personnel shelter'),
('Emergency Shelter 2', 'SHELTER', 'ACTIVE', -26.5220, 31.4690, 'Sector 14', 'Secondary emergency shelter');

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('system_name', '"UEDF SENTINEL v5.0"', 'text', 'System name'),
('system_version', '"5.0.0"', 'text', 'System version'),
('timezone', '"Africa/Mbabane"', 'text', 'Default timezone'),
('session_timeout', '1800', 'number', 'Session timeout in seconds'),
('max_login_attempts', '5', 'number', 'Maximum login attempts before lockout'),
('lockout_duration', '300', 'number', 'Lockout duration in seconds'),
('two_factor_required', 'false', 'boolean', 'Require 2FA for all users'),
('maintenance_mode', 'false', 'boolean', 'System maintenance mode'),
('allow_registration', 'false', 'boolean', 'Allow user registration'),
('default_user_role', '"viewer"', 'text', 'Default role for new users'),
('email_notifications', 'true', 'boolean', 'Enable email notifications'),
('sms_notifications', 'false', 'boolean', 'Enable SMS notifications'),
('push_notifications', 'true', 'boolean', 'Enable push notifications'),
('log_retention_days', '90', 'number', 'Days to retain logs'),
('backup_retention_days', '30', 'number', 'Days to retain backups'),
('api_rate_limit', '100', 'number', 'API rate limit per minute'),
('drone_auto_return', 'true', 'boolean', 'Auto-return drones on low battery'),
('threat_auto_escalate', 'true', 'boolean', 'Auto-escalate critical threats'),
('map_default_zoom', '12', 'number', 'Default map zoom level'),
('map_center_lat', '-26.5225', 'text', 'Default map center latitude'),
('map_center_lng', '31.4658', 'text', 'Default map center longitude');

-- Insert sample recordings
INSERT INTO recordings (drone_id, drone_name, filename, file_path, duration, file_size, resolution, recording_type, recorded_at, created_at) VALUES
(1, 'DRONE-001', 'border_patrol_20260217_1423.mp4', '/recordings/20260217/1423.mp4', 923, 245000000, '4K', 'video', DATE_SUB(NOW(), INTERVAL 2 HOUR), NOW()),
(3, 'DRONE-003', 'sector7_surveillance_20260217.mp4', '/recordings/20260217/1545.mp4', 1845, 500000000, '1080p', 'video', DATE_SUB(NOW(), INTERVAL 5 HOUR), NOW()),
(2, 'DRONE-002', 'night_patrol_20260216_2345.mp4', '/recordings/20260216/2345.mp4', 2765, 750000000, '1080p', 'video', DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
(5, 'DRONE-005', 'training_exercise_20260216.mp4', '/recordings/20260216/1005.mp4', 3600, 1000000000, '4K', 'video', DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
(4, 'DRONE-004', 'maintenance_log_20260215.mp4', '/recordings/20260215/1630.mp4', 452, 120000000, '720p', 'video', DATE_SUB(NOW(), INTERVAL 2 DAY), NOW());

-- Insert sample snapshots
INSERT INTO snapshots (recording_id, drone_id, timestamp, file_path, metadata) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 2 HOUR), '/snapshots/20260217/1423_01.jpg', '{"resolution":"3840x2160","location":"Sector 4","object":"Vehicle"}'),
(1, 1, DATE_SUB(NOW(), INTERVAL 2 HOUR), '/snapshots/20260217/1423_02.jpg', '{"resolution":"3840x2160","location":"Sector 4","object":"Person"}'),
(1, 1, DATE_SUB(NOW(), INTERVAL 2 HOUR), '/snapshots/20260217/1423_03.jpg', '{"resolution":"3840x2160","location":"Sector 4","object":"Structure"}'),
(2, 3, DATE_SUB(NOW(), INTERVAL 5 HOUR), '/snapshots/20260217/1545_01.jpg', '{"resolution":"1920x1080","location":"Sector 7","object":"Drone"}'),
(2, 3, DATE_SUB(NOW(), INTERVAL 5 HOUR), '/snapshots/20260217/1545_02.jpg', '{"resolution":"1920x1080","location":"Sector 7","object":"Unknown"}'),
(3, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), '/snapshots/20260216/2345_01.jpg', '{"resolution":"1920x1080","location":"Sector 2","object":"Vehicle"}'),
(3, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), '/snapshots/20260216/2345_02.jpg', '{"resolution":"1920x1080","location":"Sector 2","object":"Person"}');

-- Insert sample notifications
INSERT INTO notifications (user_id, type, title, message, priority, created_at) VALUES
(1, 'threat', 'Critical Threat Detected', 'Unauthorized access attempt detected in Sector 4', 'CRITICAL', DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
(1, 'drone', 'Drone Mission Complete', 'DRONE-003 has successfully completed patrol mission', 'HIGH', DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(2, 'system', 'System Update Available', 'New system update v5.0.1 is available', 'MEDIUM', DATE_SUB(NOW(), INTERVAL 12 MINUTE)),
(1, 'security', '2FA Enabled', 'Two-factor authentication has been enabled for your account', 'LOW', DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(2, 'drone', 'Low Battery Warning', 'DRONE-005 battery level is below 20%', 'CRITICAL', DATE_SUB(NOW(), INTERVAL 32 MINUTE)),
(3, 'report', 'Daily Report Ready', 'Your daily threat analysis report has been generated', 'MEDIUM', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'threat', 'Suspicious Network Activity', 'Unusual network traffic detected from external IP', 'HIGH', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'system', 'Database Optimization Complete', 'Scheduled database optimization completed', 'LOW', DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Insert sample audit logs
INSERT INTO audit_logs (user_id, username, action, category, details, ip_address, timestamp) VALUES
(1, 'commander', 'LOGIN_SUCCESS', 'authentication', '{"method":"password"}', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
(1, 'commander', 'VIEW_DASHBOARD', 'navigation', '{"page":"home"}', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(2, 'operator', 'DRONE_LAUNCH', 'drone', '{"drone_id":3,"drone":"DRONE-003"}', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 8 MINUTE)),
(3, 'analyst', 'THREAT_ANALYSIS', 'threat', '{"threat_id":2,"action":"investigate"}', '192.168.1.105', DATE_SUB(NOW(), INTERVAL 12 MINUTE)),
(1, 'commander', 'SECURITY_UPDATE', 'security', '{"setting":"2fa","value":true}', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(4, 'viewer', 'VIEW_RECORDINGS', 'media', '{"recording_id":1}', '192.168.1.110', DATE_SUB(NOW(), INTERVAL 18 MINUTE)),
(2, 'operator', 'DRONE_RETURN', 'drone', '{"drone_id":1,"drone":"DRONE-001"}', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 22 MINUTE)),
(0, 'system', 'THREAT_DETECTED', 'threat', '{"threat_id":1,"severity":"CRITICAL"}', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(3, 'analyst', 'REPORT_EXPORT', 'report', '{"report_type":"threat","format":"pdf"}', '192.168.1.105', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(1, 'commander', 'LOGOUT', 'authentication', '{"method":"manual"}', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 35 MINUTE));

-- Insert API keys
INSERT INTO api_keys (user_id, api_key, name, permissions, rate_limit) VALUES
(1, 'live_ueNcDgRkYvWmQpLhJfKg', 'Commander API Key', '{"*":true}', 1000),
(2, 'test_AbCdEfGhIjKlMnOpQrSt', 'Operator API Key', '{"read":true,"write":false}', 100);

-- Insert notification preferences
INSERT INTO notification_preferences (user_id, type, channel, enabled) VALUES
(1, 'threat', 'in_app', true),
(1, 'threat', 'email', true),
(1, 'threat', 'sms', true),
(1, 'drone', 'in_app', true),
(1, 'drone', 'email', false),
(1, 'system', 'in_app', true),
(1, 'system', 'email', true),
(2, 'threat', 'in_app', true),
(2, 'threat', 'email', true),
(2, 'drone', 'in_app', true),
(2, 'drone', 'sms', true),
(3, 'threat', 'in_app', true),
(3, 'analytics', 'email', true),
(4, 'report', 'in_app', true);

-- Create indexes for performance
CREATE INDEX idx_threats_severity_time ON threats(severity, detected_at);
CREATE INDEX idx_drones_status_battery ON drones(status, battery_level);
CREATE INDEX idx_recordings_date_type ON recordings(created_at, recording_type);
CREATE INDEX idx_audit_user_time ON audit_logs(user_id, timestamp);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at);

-- Create views for common queries
CREATE VIEW vw_active_threats AS
SELECT id, type, severity, status, location_name, sector, detected_at
FROM threats
WHERE status = 'ACTIVE'
ORDER BY 
    CASE severity 
        WHEN 'CRITICAL' THEN 1 
        WHEN 'HIGH' THEN 2 
        WHEN 'MEDIUM' THEN 3 
        WHEN 'LOW' THEN 4 
    END,
    detected_at DESC;

CREATE VIEW vw_drone_status AS
SELECT 
    id,
    name,
    model,
    status,
    battery_level,
    flight_hours,
    CASE 
        WHEN battery_level < 20 THEN 'CRITICAL'
        WHEN battery_level < 50 THEN 'LOW'
        WHEN battery_level < 80 THEN 'MEDIUM'
        ELSE 'GOOD'
    END as battery_status,
    CASE 
        WHEN last_seen < DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'OFFLINE'
        ELSE 'ONLINE'
    END as connectivity,
    TIMESTAMPDIFF(MINUTE, last_seen, NOW()) as minutes_since_seen
FROM drones;

CREATE VIEW vw_system_summary AS
SELECT
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as active_users,
    (SELECT COUNT(*) FROM drones) as total_drones,
    (SELECT COUNT(*) FROM drones WHERE status = 'ACTIVE') as active_drones,
    (SELECT COUNT(*) FROM threats) as total_threats,
    (SELECT COUNT(*) FROM threats WHERE status = 'ACTIVE') as active_threats,
    (SELECT COUNT(*) FROM threats WHERE severity = 'CRITICAL' AND status = 'ACTIVE') as critical_threats,
    (SELECT COUNT(*) FROM nodes) as total_nodes,
    (SELECT COUNT(*) FROM nodes WHERE status = 'ACTIVE') as active_nodes,
    (SELECT COUNT(*) FROM recordings WHERE DATE(created_at) = CURDATE()) as todays_recordings,
    (SELECT COUNT(*) FROM audit_logs WHERE DATE(timestamp) = CURDATE()) as todays_events,
    (SELECT COUNT(*) FROM notifications WHERE is_read = FALSE) as unread_notifications;

-- Create stored procedure for cleaning old data
DELIMITER //

CREATE PROCEDURE sp_cleanup_old_data()
BEGIN
    -- Delete old telemetry data (older than 30 days)
    DELETE FROM drone_telemetry WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    DELETE FROM node_telemetry WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Delete old audit logs (older than 90 days)
    DELETE FROM audit_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    -- Delete old notifications (older than 30 days)
    DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Delete expired sessions
    DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 1 DAY);
    
    -- Delete expired password resets
    DELETE FROM password_resets WHERE expires_at < NOW();
    
    -- Optimize tables
    OPTIMIZE TABLE drone_telemetry;
    OPTIMIZE TABLE node_telemetry;
    OPTIMIZE TABLE audit_logs;
    OPTIMIZE TABLE notifications;
    OPTIMIZE TABLE user_sessions;
END//

-- Create stored procedure for generating daily reports
CREATE PROCEDURE sp_generate_daily_report(IN report_date DATE)
BEGIN
    SELECT
        report_date as date,
        (SELECT COUNT(*) FROM threats WHERE DATE(detected_at) = report_date) as threats_detected,
        (SELECT COUNT(*) FROM threats WHERE DATE(resolved_at) = report_date) as threats_resolved,
        (SELECT COUNT(*) FROM drone_missions WHERE DATE(actual_start) = report_date) as missions_started,
        (SELECT COUNT(*) FROM drone_missions WHERE DATE(actual_end) = report_date) as missions_completed,
        (SELECT COUNT(*) FROM recordings WHERE DATE(recorded_at) = report_date) as recordings_made,
        (SELECT COUNT(*) FROM users WHERE DATE(last_login) = report_date) as active_users,
        (SELECT COUNT(*) FROM audit_logs WHERE DATE(timestamp) = report_date) as total_events;
END//

DELIMITER ;

-- Create event for automatic cleanup (if events are enabled)
-- SET GLOBAL event_scheduler = ON;
-- CREATE EVENT IF NOT EXISTS e_cleanup_old_data
-- ON SCHEDULE EVERY 1 DAY
-- DO CALL sp_cleanup_old_data();

-- ============================================
-- DATABASE VERIFICATION
-- ============================================

-- Verify tables were created
SELECT 'Database schema created successfully' as status,
       COUNT(*) as table_count
FROM information_schema.tables 
WHERE table_schema = 'uedf_sentinel';

-- Show table list
SHOW TABLES;

-- Show counts
SELECT 'users' as table_name, COUNT(*) as record_count FROM users UNION ALL
SELECT 'drones', COUNT(*) FROM drones UNION ALL
SELECT 'threats', COUNT(*) FROM threats UNION ALL
SELECT 'nodes', COUNT(*) FROM nodes UNION ALL
SELECT 'recordings', COUNT(*) FROM recordings UNION ALL
SELECT 'snapshots', COUNT(*) FROM snapshots UNION ALL
SELECT 'audit_logs', COUNT(*) FROM audit_logs UNION ALL
SELECT 'notifications', COUNT(*) FROM notifications;