<?php
/**
 * Get Inventory Item History
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $item_id = $_GET['item_id'] ?? 0;
    $limit = $_GET['limit'] ?? 50;
    
    if ($item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        exit();
    }
    
    $history = getInventoryItemHistory($item_id, $limit);
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory item history: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory item history
 */
function getInventoryItemHistory($item_id, $limit) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                it.id,
                it.transaction_type,
                it.quantity,
                it.unit_price,
                it.total_value,
                it.reason,
                u.name as performed_by,
                it.created_at
            FROM inventory_transactions it
            LEFT JOIN users u ON it.performed_by = u.id
            WHERE it.item_id = ?
            ORDER BY it.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$item_id, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting inventory item history: " . $e->getMessage());
        return [];
    }
}
?>
