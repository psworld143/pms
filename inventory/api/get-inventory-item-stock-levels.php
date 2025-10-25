<?php
/**
 * Get Inventory Item Stock Levels
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
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $items = getInventoryItemStockLevels($category, $status, $search);
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory item stock levels: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory item stock levels
 */
function getInventoryItemStockLevels($category, $status, $search) {
    global $pdo;
    
    try {
        $sql = "
            SELECT 
                ii.id,
                ii.item_name,
                ii.sku,
                ii.current_stock,
                ii.minimum_stock,
                ii.unit_price,
                ii.unit,
                (ii.current_stock * ii.unit_price) as total_value,
                ic.name as category_name,
                CASE 
                    WHEN ii.current_stock = 0 THEN 'Out of Stock'
                    WHEN ii.current_stock <= ii.minimum_stock THEN 'Low Stock'
                    ELSE 'In Stock'
                END as stock_status
            FROM inventory_items ii
            LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($category)) {
            $sql .= " AND ic.name = ?";
            $params[] = $category;
        }
        
        if (!empty($status)) {
            switch ($status) {
                case 'in_stock':
                    $sql .= " AND ii.current_stock > ii.minimum_stock";
                    break;
                case 'low_stock':
                    $sql .= " AND ii.current_stock <= ii.minimum_stock AND ii.current_stock > 0";
                    break;
                case 'out_of_stock':
                    $sql .= " AND ii.current_stock = 0";
                    break;
            }
        }
        
        if (!empty($search)) {
            $sql .= " AND (ii.item_name LIKE ? OR ii.sku LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        $sql .= " ORDER BY ii.item_name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting inventory item stock levels: " . $e->getMessage());
        return [];
    }
}
?>
