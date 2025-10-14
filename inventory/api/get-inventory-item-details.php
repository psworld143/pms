<?php
/**
 * Get Inventory Item Details
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
    
    if ($item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        exit();
    }
    
    $item = getInventoryItemDetails($item_id);
    
    if ($item) {
        echo json_encode([
            'success' => true,
            'item' => $item
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error getting inventory item details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory item details
 */
function getInventoryItemDetails($item_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ii.id,
                ii.item_name,
                ii.sku,
                ii.description,
                ii.current_stock,
                ii.minimum_stock,
                ii.unit_price,
                ii.unit,
                ii.supplier,
                ii.is_pos_product,
                ii.created_at,
                ii.last_updated,
                ic.name as category_name
            FROM inventory_items ii
            LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
            WHERE ii.id = ?
        ");
        $stmt->execute([$item_id]);
        
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            // Get recent transactions
            $stmt = $pdo->prepare("
                SELECT 
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
                LIMIT 10
            ");
            $stmt->execute([$item_id]);
            $item['recent_transactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate stock status
            if ($item['current_stock'] == 0) {
                $item['stock_status'] = 'Out of Stock';
            } elseif ($item['current_stock'] <= $item['minimum_stock']) {
                $item['stock_status'] = 'Low Stock';
            } else {
                $item['stock_status'] = 'In Stock';
            }
            
            // Calculate total value
            $item['total_value'] = $item['current_stock'] * $item['unit_price'];
        }
        
        return $item;
        
    } catch (PDOException $e) {
        error_log("Error getting inventory item details: " . $e->getMessage());
        return false;
    }
}
?>
