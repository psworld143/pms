<?php
/**
 * Get POS Products for Inventory Display
 * Hotel PMS Training System - Inventory Module
 */

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
                'pos_product' as source_type
            FROM pos_menu_items
            WHERE {$where_clause}
            ORDER BY category, sort_order, name ASC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting POS products with filters: " . $e->getMessage());
        return [];
    }
}
?>

