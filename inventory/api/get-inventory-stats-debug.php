<?php
/**
 * Debug version of get-inventory-stats.php - no authentication required
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

session_start();
require_once '../config/database.php';

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
        // Initialize default values
        $stats = [
            'total_items' => 0,
            'in_stock' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'pos_products' => 0,
            'category_stats' => []
        ];
        
        // Test database connection
        if (!$pdo) {
            throw new Exception("Database connection failed");
        }
        
        // Get total items count
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM inventory_items");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_items'] = (int)($result['total_items'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting total items: " . $e->getMessage());
        }
        
        // Get in stock count (current_stock > minimum_stock)
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as in_stock 
                FROM inventory_items 
                WHERE current_stock > minimum_stock
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['in_stock'] = (int)($result['in_stock'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting in stock count: " . $e->getMessage());
        }
        
        // Get low stock count (current_stock <= minimum_stock AND current_stock > 0)
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as low_stock 
                FROM inventory_items 
                WHERE current_stock <= minimum_stock AND current_stock > 0
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['low_stock'] = (int)($result['low_stock'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting low stock count: " . $e->getMessage());
        }
        
        // Get out of stock count (current_stock = 0)
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as out_of_stock 
                FROM inventory_items 
                WHERE current_stock = 0
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['out_of_stock'] = (int)($result['out_of_stock'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting out of stock count: " . $e->getMessage());
        }
        
        // Get POS products count
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as pos_products 
                FROM inventory_items 
                WHERE is_pos_product = 1
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['pos_products'] = (int)($result['pos_products'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting POS products count: " . $e->getMessage());
        }
        
        // Get category statistics
        try {
            $stmt = $pdo->query("
                SELECT 
                    ic.name as category_name,
                    COUNT(ii.id) as total_items,
                    SUM(CASE WHEN ii.current_stock > ii.minimum_stock THEN 1 ELSE 0 END) as in_stock_count,
                    SUM(CASE WHEN ii.current_stock <= ii.minimum_stock AND ii.current_stock > 0 THEN 1 ELSE 0 END) as low_stock_count,
                    SUM(CASE WHEN ii.current_stock = 0 THEN 1 ELSE 0 END) as out_of_stock_count
                FROM inventory_categories ic
                LEFT JOIN inventory_items ii ON ic.id = ii.category_id
                GROUP BY ic.name
                ORDER BY ic.name ASC
            ");
            $stats['category_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting category statistics: " . $e->getMessage());
            $stats['category_stats'] = [];
        }
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error in getInventoryStatistics: " . $e->getMessage());
        throw $e;
    }
}
?>
