<?php
// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Direct database connection without debug output
try {
    // Use the same database configuration as the main system
    require_once __DIR__ . '/../../includes/database.php';
    
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection not established');
    }
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Clean any output before sending JSON
ob_clean();
header('Content-Type: application/json');

try {
    $item_id = (int)($_POST['item_id'] ?? 0);
    if (!$item_id) { echo json_encode(['success'=>false,'message'=>'Item required']); exit; }

    // Use existing table structure
    $stmt = $pdo->prepare('DELETE FROM reorder_rules WHERE item_id = ?');
    $stmt->execute([$item_id]);

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
