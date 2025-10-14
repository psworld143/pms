<?php
/**
 * Get Inventory Item Categories
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
    $categories = getInventoryItemCategories();
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory item categories: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory item categories
 */
function getInventoryItemCategories() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                ic.id,
                ic.name,
                ic.description,
                COUNT(ii.id) as item_count,
                SUM(ii.current_stock * ii.unit_price) as total_value
            FROM inventory_categories ic
            LEFT JOIN inventory_items ii ON ic.id = ii.category_id
            GROUP BY ic.id, ic.name, ic.description
            ORDER BY ic.name ASC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting inventory item categories: " . $e->getMessage());
        return [];
    }
}
?>
