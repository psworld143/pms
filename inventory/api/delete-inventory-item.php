<?php
/**
 * Delete Inventory Item
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
    $item_id = $_POST['item_id'] ?? 0;
    
    if ($item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        exit();
    }
    
    $result = deleteInventoryItem($item_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item deleted successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error deleting inventory item: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Delete inventory item
 */
function deleteInventoryItem($item_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check if item exists
        $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            throw new Exception('Item not found');
        }
        
        // Delete related transactions first
        $stmt = $pdo->prepare("DELETE FROM inventory_transactions WHERE item_id = ?");
        $stmt->execute([$item_id]);
        
        // Delete reorder rules if they exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'reorder_rules'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM reorder_rules WHERE item_id = ?");
            $stmt->execute([$item_id]);
        }
        
        // Delete room inventory if it exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'room_inventory'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM room_inventory WHERE item_id = ?");
            $stmt->execute([$item_id]);
        }
        
        // Delete the item
        $stmt = $pdo->prepare("DELETE FROM inventory_items WHERE id = ?");
        $stmt->execute([$item_id]);
        
        $pdo->commit();
        
        return ['success' => true];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>