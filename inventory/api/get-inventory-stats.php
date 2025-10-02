<?php
/**
 * Get Inventory Statistics
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
    $stats = getInventoryStatistics();
    
    echo json_encode([
        'success' => true,
        'statistics' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory statistics: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory statistics
 */
function getInventoryStatistics() {
    global $pdo;
    
    try {
        // Get total items count
        $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM inventory_items");
        $total_items = $stmt->fetch()['total_items'];
        
        // Get in stock count (current_stock > minimum_stock)
        $stmt = $pdo->query("
            SELECT COUNT(*) as in_stock 
            FROM inventory_items 
            WHERE current_stock > minimum_stock
        ");
        $in_stock = $stmt->fetch()['in_stock'];
        
        // Get low stock count (current_stock <= minimum_stock AND current_stock > 0)
        $stmt = $pdo->query("
            SELECT COUNT(*) as low_stock 
            FROM inventory_items 
            WHERE current_stock <= minimum_stock AND current_stock > 0
        ");
        $low_stock = $stmt->fetch()['low_stock'];
        
        // Get out of stock count (current_stock = 0)
        $stmt = $pdo->query("
            SELECT COUNT(*) as out_of_stock 
            FROM inventory_items 
            WHERE current_stock = 0
        ");
        $out_of_stock = $stmt->fetch()['out_of_stock'];
        
        // Get POS products count
        $stmt = $pdo->query("SELECT COUNT(*) as pos_products FROM pos_menu_items WHERE active = 1");
        $pos_products = $stmt->fetch()['pos_products'];
        
        // Get category statistics
        $stmt = $pdo->query("
            SELECT 
                c.name as category_name,
                '#10B981' as category_color,
                'fas fa-box' as category_icon,
                COUNT(i.id) as item_count,
                SUM(CASE WHEN i.current_stock > i.minimum_stock THEN 1 ELSE 0 END) as in_stock_count,
                SUM(CASE WHEN i.current_stock <= i.minimum_stock AND i.current_stock > 0 THEN 1 ELSE 0 END) as low_stock_count,
                SUM(CASE WHEN i.current_stock = 0 THEN 1 ELSE 0 END) as out_of_stock_count
            FROM inventory_categories c
            LEFT JOIN inventory_items i ON c.id = i.category_id
            GROUP BY c.id, c.name
            ORDER BY c.name
        ");
        $category_stats = $stmt->fetchAll();
        
        return [
            'total_items' => (int)$total_items,
            'in_stock' => (int)$in_stock,
            'low_stock' => (int)$low_stock,
            'out_of_stock' => (int)$out_of_stock,
            'pos_products' => (int)$pos_products,
            'category_stats' => $category_stats
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting inventory statistics: " . $e->getMessage());
        return [
            'total_items' => 0,
            'in_stock' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'pos_products' => 0,
            'category_stats' => []
        ];
    }
}
?>
