<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Simple installer to execute scenario-seed.sql using project PDO
require_once '../../includes/session-config.php';
session_start();
require_once '../../config/database.php';

header('Content-Type: text/plain');

try {
    $sqlPath = __DIR__ . DIRECTORY_SEPARATOR . 'scenario-seed.sql';
    if (!file_exists($sqlPath)) {
        http_response_code(404);
        echo "Seed file not found: scenario-seed.sql\n";
        exit;
    }

    $sql = file_get_contents($sqlPath);
    if ($sql === false) {
        throw new Exception('Unable to read seed file');
    }

    // Split on DELIMITER not used; execute as a whole via PDO->exec
    $pdo->exec($sql);
    echo "Training scenarios installed successfully.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error installing training scenarios: " . $e->getMessage() . "\n";
}


