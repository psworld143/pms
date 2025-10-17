<?php
/**
 * Simple test API - no authentication required
 */

header('Content-Type: application/json');

try {
    require_once '../config/database.php';
    
    global $pdo;
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    
    // Simple test query
    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM inventory_items");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'total_items' => $result['total_items']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
