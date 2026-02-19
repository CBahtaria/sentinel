-- Add indexes for performance
CREATE INDEX idx_threats_severity ON threats(severity);
CREATE INDEX idx_threats_status ON threats(status);
CREATE INDEX idx_drones_status ON drones(status);
CREATE INDEX idx_nodes_zone ON nodes(zone);
CREATE INDEX idx_nodes_status ON nodes(status);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_sessions_expires ON sessions(expires_at);

-- Optimize tables
OPTIMIZE TABLE users;
OPTIMIZE TABLE nodes;
OPTIMIZE TABLE drones;
OPTIMIZE TABLE threats;
OPTIMIZE TABLE audit_logs;
OPTIMIZE TABLE sessions;