<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Simple CSV certificate export of completed training attempts
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="training-certificate-' . date('Ymd-His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['User ID', 'Scenario', 'Type', 'Score', 'Completed At']);

try {
    $stmt = $pdo->prepare("SELECT ts.title, ta.scenario_type, ta.score, ta.created_at
        FROM training_attempts ta
        LEFT JOIN training_scenarios ts ON ts.id = ta.scenario_id
        WHERE ta.user_id = ? AND ta.status = 'completed'");
    $stmt->execute([$_SESSION['user_id']]);
    while ($row = $stmt->fetch()) {
        fputcsv($out, [$_SESSION['user_id'], $row['title'] ?? '-', $row['scenario_type'], $row['score'], $row['created_at']]);
    }
} catch (Exception $e) {
    fputcsv($out, ['Error', $e->getMessage()]);
}

fclose($out);
exit;
?>

