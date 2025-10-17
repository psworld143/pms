<?php
/**
 * Get Inventory Reports for Management Dashboard
 * Hotel PMS - Management Reports Module
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__) . '/config/database.php';

// Check if user is logged in and has manager role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get category filter
    $categoryFilter = $_GET['category'] ?? '';
    
    // Build the query
    $sql = "
        SELECT 
            ii.id,
            ii.item_name as name,
            ii.current_stock,
            ii.minimum_stock,
            ii.unit_price,
            ic.name as category_name,
            CASE 
                WHEN ii.current_stock <= ii.minimum_stock THEN 'Low Stock'
                WHEN ii.current_stock <= (ii.minimum_stock * 1.5) THEN 'Warning'
                ELSE 'In Stock'
            END as stock_status
        FROM inventory_items ii
        LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
    ";
    
    $params = [];
    if (!empty($categoryFilter)) {
        $sql .= " WHERE ic.name = ?";
        $params[] = $categoryFilter;
    }
    
    $sql .= " ORDER BY ii.item_name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get inventory statistics
    $statsSql = "
        SELECT 
            COUNT(*) as total_items,
            SUM(CASE WHEN current_stock <= minimum_stock THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN current_stock > minimum_stock AND current_stock <= (minimum_stock * 1.5) THEN 1 ELSE 0 END) as medium_stock,
            SUM(CASE WHEN current_stock > (minimum_stock * 1.5) THEN 1 ELSE 0 END) as good_stock,
            SUM(current_stock * unit_price) as total_value
        FROM inventory_items
    ";
    
    $statsStmt = $pdo->query($statsSql);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get categories for filter
    $catStmt = $pdo->query("SELECT DISTINCT name FROM inventory_categories ORDER BY name");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'inventory_items' => $items,
        'stock_summary' => [
            'total_items' => (int)$stats['total_items'],
            'low_stock_items' => (int)$stats['low_stock'],
            'medium_stock_items' => (int)$stats['medium_stock'],
            'good_stock_items' => (int)$stats['good_stock'],
            'total_inventory_value' => (float)$stats['total_value']
        ],
        'categories' => $categories,
        'category_filter' => $categoryFilter
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching inventory reports: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
