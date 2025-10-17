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
    $item_id = (int)($_POST['item_id'] ?? 0);
    if (!$item_id) { echo json_encode(['success'=>false,'message'=>'Item required']); exit; }

    // Use existing table structure
    $stmt = $pdo->prepare('DELETE FROM reorder_rules WHERE item_id = ?');
    $stmt->execute([$item_id]);

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
