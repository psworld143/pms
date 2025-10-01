<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    // Build query based on category filter
    $where_clause = '';
    $params = [];
    
    if (!empty($category)) {
        $where_clause = 'WHERE i.category = ?';
        $params[] = $category;
    }
    
    // Get inventory items with stock levels
    $stmt = $pdo->prepare("
        SELECT 
            i.*,
            c.name as category_name,
            CASE 
                WHEN i.current_stock <= i.minimum_stock THEN 'Low Stock'
                WHEN i.current_stock <= (i.minimum_stock * 1.5) THEN 'Medium Stock'
                ELSE 'Good Stock'
            END as stock_status
        FROM inventory_items i
        LEFT JOIN inventory_categories c ON i.category_id = c.id
        $where_clause
        ORDER BY i.current_stock ASC, i.name ASC
    ");
    $stmt->execute($params);
    $inventory_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stock summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_items,
            SUM(CASE WHEN current_stock <= minimum_stock THEN 1 ELSE 0 END) as low_stock_items,
            SUM(CASE WHEN current_stock <= (minimum_stock * 1.5) AND current_stock > minimum_stock THEN 1 ELSE 0 END) as medium_stock_items,
            SUM(CASE WHEN current_stock > (minimum_stock * 1.5) THEN 1 ELSE 0 END) as good_stock_items,
            SUM(current_stock * unit_price) as total_inventory_value
        FROM inventory_items i
        $where_clause
    ");
    $stmt->execute($params);
    $stock_summary = $stmt->fetch();
    
    // Get recent transactions
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            i.name as item_name,
            c.name as category_name
        FROM inventory_transactions t
        JOIN inventory_items i ON t.item_id = i.id
        LEFT JOIN inventory_categories c ON i.category_id = c.id
        WHERE DATE(t.created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY t.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'category_filter' => $category,
        'inventory_items' => $inventory_items,
        'stock_summary' => [
            'total_items' => (int)$stock_summary['total_items'],
            'low_stock_items' => (int)$stock_summary['low_stock_items'],
            'medium_stock_items' => (int)$stock_summary['medium_stock_items'],
            'good_stock_items' => (int)$stock_summary['good_stock_items'],
            'total_inventory_value' => (float)$stock_summary['total_inventory_value']
        ],
        'recent_transactions' => $recent_transactions
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching inventory reports: ' . $e->getMessage()
    ]);
}
?>
