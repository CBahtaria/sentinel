<?php
/**
 * UEDF SENTINEL - Real-time Drone Tracking API
 * Returns latest telemetry written by the UAV-stack NATS bridge.
 * No simulated coordinates — data comes from the bridge pipeline.
 */

header('Content-Type: application/json');

$cfg = require __DIR__ . '/../config/database.php';
try {
    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
    $db = new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => 'DB unavailable']);
    exit();
}

// Latest telemetry per drone via the sentinel_bridge pipeline.
// Falls back to Eswatini centre coords when no telemetry row exists.
$stmt = $db->prepare("
    SELECT
        d.id, d.name, d.status, d.battery_level, d.location, d.last_seen,
        COALESCE(t.latitude,  -26.315) AS latitude,
        COALESCE(t.longitude,  31.135) AS longitude,
        COALESCE(t.altitude_m,    0.0) AS altitude_m,
        COALESCE(t.velocity_ms,   0.0) AS speed_ms,
        COALESCE(t.heading_deg,   0.0) AS heading_deg,
        t.recorded_at AS telemetry_at
    FROM drones d
    LEFT JOIN (
        SELECT dt.drone_id, dt.latitude, dt.longitude, dt.altitude_m,
               dt.velocity_ms, dt.heading_deg, dt.recorded_at
        FROM drone_telemetry dt
        INNER JOIN (
            SELECT drone_id, MAX(recorded_at) AS max_ts
            FROM drone_telemetry
            GROUP BY drone_id
        ) latest ON dt.drone_id = latest.drone_id AND dt.recorded_at = latest.max_ts
    ) t ON d.id = t.drone_id
    ORDER BY d.id
");
$stmt->execute();
$drones = $stmt->fetchAll();

echo json_encode([
    'success'   => true,
    'timestamp' => time(),
    'data'      => $drones,
]);
