<?php
/**
 * Get Inventory Items
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
    // Get filter parameters
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'active';
    
    $inventory_items = getInventoryItemsWithFilters($category, $search, $status);
    
    echo json_encode([
        'success' => true,
        'inventory_items' => $inventory_items
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory items: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory items with filters
 */
function getInventoryItemsWithFilters($category = '', $search = '', $status = 'active') {
    global $pdo;
    
    try {
        $where_conditions = ["1=1"];
        $params = [];
        
        // Category filter
        if (!empty($category)) {
            $where_conditions[] = "c.name = ?";
            $params[] = $category;
        }
        
        // Search filter
        if (!empty($search)) {
            $where_conditions[] = "(i.item_name LIKE ? OR i.description LIKE ?)";
            $search_term = "%$search%";
            $params = array_merge($params, [$search_term, $search_term]);
        }
        
        // Status filter - not applicable for current schema
        // All items are considered active
        
        $where_clause = implode(" AND ", $where_conditions);
        
        $stmt = $pdo->prepare("
            SELECT 
                i.id,
                i.item_name as name,
                '' as sku,
                i.description,
                i.current_stock as quantity,
                i.minimum_stock,
                i.current_stock * 2 as maximum_stock,
                i.unit_price,
                i.unit_price as cost_price,
                '' as supplier,
                'Main Storage' as location,
                'Piece' as unit,
                '' as barcode,
                '' as image,
                'active' as status,
                i.created_at,
                i.last_updated as updated_at,
                c.name as category_name,
                '#10B981' as category_color,
                'fas fa-box' as category_icon,
                CASE 
                    WHEN i.current_stock = 0 THEN 'out_of_stock'
                    WHEN i.current_stock <= i.minimum_stock THEN 'low_stock'
                    ELSE 'in_stock'
                END as stock_status
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE {$where_clause}
            ORDER BY c.name, i.item_name ASC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting inventory items with filters: " . $e->getMessage());
        return [];
    }
}
?>
