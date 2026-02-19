<?php
require_once '../includes/session.php';
// Simple Export System
if (session_status() == PHP_SESSION_NONE) 
if (!isset($_SESSION['user_id'])) { header('Location: ?module=login'); exit; }

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="threats.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Type', 'Severity', 'Level']);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $threats = $pdo->query("SELECT detected_at, threat_type, severity, threat_level FROM threats");
    foreach ($threats as $t) {
        fputcsv($output, $t);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error', 'Database error', '', '']);
}
fclose($output);
?>
