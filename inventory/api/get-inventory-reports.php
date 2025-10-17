<?php
/**
 * Get Inventory Reports
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
    $report_type = $_GET['report_type'] ?? 'stock_level';
    $category = $_GET['category'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    $data = getInventoryReports($report_type, $category, $date_from, $date_to);
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory reports: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory reports
 */
function getInventoryReports($report_type, $category, $date_from, $date_to) {
    global $pdo;
    
    try {
        switch ($report_type) {
            case 'stock_level':
                return getStockLevelReport($category);
            case 'value':
                return getValueReport($category);
            case 'low_stock':
                return getLowStockReport($category);
            case 'fast_moving':
                return getFastMovingReport($category, $date_from, $date_to);
            case 'slow_moving':
                return getSlowMovingReport($category, $date_from, $date_to);
            default:
                return getStockLevelReport($category);
        }
    } catch (PDOException $e) {
        error_log("Error getting inventory reports: " . $e->getMessage());
        return [];
    }
}

/**
 * Get stock level report
 */
function getStockLevelReport($category) {
    global $pdo;
    
    $sql = "
        SELECT 
            ii.item_name,
            ic.name as category_name,
            ii.sku,
            ii.current_stock,
            ii.minimum_stock,
            ii.unit_price,
            (ii.current_stock * ii.unit_price) as total_value,
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
    
    $sql .= " ORDER BY ii.item_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get value report
 */
function getValueReport($category) {
    global $pdo;
    
    $sql = "
        SELECT 
            ii.item_name,
            ic.name as category_name,
            ii.sku,
            ii.current_stock,
            ii.unit_price,
            (ii.current_stock * ii.unit_price) as total_value
        FROM inventory_items ii
        LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
        WHERE 1=1
    ";
    
    $params = [];
    if (!empty($category)) {
        $sql .= " AND ic.name = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY total_value DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get low stock report
 */
function getLowStockReport($category) {
    global $pdo;
    
    $sql = "
        SELECT 
            ii.item_name,
            ic.name as category_name,
            ii.sku,
            ii.current_stock,
            ii.minimum_stock,
            ii.unit_price,
            (ii.current_stock * ii.unit_price) as total_value,
            (ii.minimum_stock - ii.current_stock) as shortage
        FROM inventory_items ii
        LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
        WHERE ii.current_stock <= ii.minimum_stock
    ";
    
    $params = [];
    if (!empty($category)) {
        $sql .= " AND ic.name = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY shortage DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get fast moving report
 */
function getFastMovingReport($category, $date_from, $date_to) {
    global $pdo;
    
    $date_from = $date_from ?: date('Y-m-d', strtotime('-30 days'));
    $date_to = $date_to ?: date('Y-m-d');
    
    $sql = "
        SELECT 
            ii.item_name,
            ic.name as category_name,
            ii.sku,
            SUM(it.quantity) as total_used,
            COUNT(it.id) as transaction_count,
            AVG(it.quantity) as avg_quantity_per_transaction
        FROM inventory_transactions it
        JOIN inventory_items ii ON it.item_id = ii.id
        LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
        WHERE it.transaction_type = 'out'
        AND DATE(it.created_at) BETWEEN ? AND ?
    ";
    
    $params = [$date_from, $date_to];
    if (!empty($category)) {
        $sql .= " AND ic.name = ?";
        $params[] = $category;
    }
    
    $sql .= "
        GROUP BY ii.id, ii.item_name, ic.name, ii.sku
        ORDER BY total_used DESC
        LIMIT 20
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get slow moving report
 */
function getSlowMovingReport($category, $date_from, $date_to) {
    global $pdo;
    
    $date_from = $date_from ?: date('Y-m-d', strtotime('-30 days'));
    $date_to = $date_to ?: date('Y-m-d');
    
    $sql = "
        SELECT 
            ii.item_name,
            ic.name as category_name,
            ii.sku,
            ii.current_stock,
            ii.unit_price,
            (ii.current_stock * ii.unit_price) as total_value,
            COALESCE(SUM(it.quantity), 0) as total_used
        FROM inventory_items ii
        LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
        LEFT JOIN inventory_transactions it ON ii.id = it.item_id 
            AND it.transaction_type = 'out'
            AND DATE(it.created_at) BETWEEN ? AND ?
        WHERE 1=1
    ";
    
    $params = [$date_from, $date_to];
    if (!empty($category)) {
        $sql .= " AND ic.name = ?";
        $params[] = $category;
    }
    
    $sql .= "
        GROUP BY ii.id, ii.item_name, ic.name, ii.sku, ii.current_stock, ii.unit_price
        HAVING total_used = 0 OR total_used < 5
        ORDER BY total_used ASC, total_value DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
