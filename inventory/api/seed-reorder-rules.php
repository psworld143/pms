<?php
// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Direct database connection without debug output
try {
    $host = 'localhost';
    $dbname = 'pms_pms_hotel';
    $username = 'pms_pms_hotel';
    $password = '020894HotelPMS';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Clean any output before sending JSON
ob_clean();
header('Content-Type: application/json');

try {

    $pdo->beginTransaction();
    // Insert defaults for items without a rule
    $sql = "INSERT IGNORE INTO reorder_rules (item_id, reorder_point, reorder_quantity, active)
            SELECT id, COALESCE(minimum_stock, 10), 20, 1 FROM inventory_items";
    $pdo->exec($sql);
    $pdo->commit();

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
