<?php
/**
 * Get POS Products for Inventory Display
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

session_start();
require_once '../config/database.php';

// Check if user is logged in - TEMPORARILY DISABLED FOR DEBUGGING
// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit();
// }

header('Content-Type: application/json');

try {
    // Get filter parameters
    $category = $_GET['category'] ?? '';
    $active_only = $_GET['active_only'] ?? 'true';
    
    $pos_products = getPOSProductsWithFilters($category, $active_only);
    
    echo json_encode([
        'success' => true,
        'pos_products' => $pos_products
    ]);
    
} catch (Exception $e) {
    error_log("Error getting POS products: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get POS products with filters
 */
function getPOSProductsWithFilters($category = '', $active_only = 'true') {
    global $pdo;
    
    try {
        // First try to get POS products from inventory_items with is_pos_product = 1
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'is_pos_product'");
            if ($stmt->rowCount() > 0) {
                return getPOSProductsFromInventoryItems($category, $active_only);
            }
        } catch (Exception $e) {
            // Column doesn't exist, try other tables
        }

        // Try pos_menu_items table
        try {
            return getPOSProductsFromMenuItems($category, $active_only);
        } catch (Exception $e) {
            // Table doesn't exist, try pos_inventory
        }

        // Try pos_inventory table
        try {
            return getPOSProductsFromPOSInventory($category, $active_only);
        } catch (Exception $e) {
            // No POS tables exist, return empty array
            return [];
        }
        
    } catch (Exception $e) {
        error_log("Error getting POS products with filters: " . $e->getMessage());
        return [];
    }
}

/**
 * Get POS products from inventory_items table
 */
function getPOSProductsFromInventoryItems($category = '', $active_only = 'true') {
    global $pdo;
    
    $where_conditions = ["i.is_pos_product = 1"];
    $params = [];
    
    // Category filter
    if (!empty($category)) {
        $where_conditions[] = "c.name = ?";
        $params[] = $category;
    }
    
    // Active filter
    if ($active_only === 'true') {
        $where_conditions[] = "COALESCE(i.status, 'active') = 'active'";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            i.id, 
            i.item_name as name, 
            i.description, 
            c.name as category, 
            i.unit_price as price, 
            COALESCE(i.cost_price, i.unit_price) as cost, 
            COALESCE(i.image, '') as image, 
            COALESCE(i.status, 'active') as active, 
            0 as sort_order, 
            i.created_at, 
            i.last_updated as updated_at,
            'inventory_item' as source_type
        FROM inventory_items i
        LEFT JOIN inventory_categories c ON i.category_id = c.id
        WHERE {$where_clause}
        ORDER BY c.name, i.item_name ASC
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get POS products from pos_menu_items table
 */
function getPOSProductsFromMenuItems($category = '', $active_only = 'true') {
    global $pdo;
    
    $where_conditions = ["1=1"];
    $params = [];
    
    // Category filter
    if (!empty($category)) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }
    
    // Active filter
    if ($active_only === 'true') {
        $where_conditions[] = "active = 1";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            name, 
            description, 
            category, 
            price, 
            cost, 
            image, 
            active, 
            sort_order, 
            created_at, 
            updated_at,
            'pos_menu_item' as source_type
        FROM pos_menu_items
        WHERE {$where_clause}
        ORDER BY category, sort_order, name ASC
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get POS products from pos_inventory table
 */
function getPOSProductsFromPOSInventory($category = '', $active_only = 'true') {
    global $pdo;
    
    $where_conditions = ["1=1"];
    $params = [];
    
    // Category filter
    if (!empty($category)) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }
    
    // Active filter
    if ($active_only === 'true') {
        $where_conditions[] = "active = 1";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            name, 
            description, 
            category, 
            price, 
            cost, 
            image, 
            active, 
            0 as sort_order, 
            created_at, 
            updated_at,
            'pos_inventory' as source_type
        FROM pos_inventory
        WHERE {$where_clause}
        ORDER BY category, name ASC
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>