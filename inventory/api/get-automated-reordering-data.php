<?php
/**
 * Get Automated Reordering Data
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
    $data = getAutomatedReorderingData();
    
    echo json_encode([
        'success' => true,
        'statistics' => $data['statistics'],
        'reorder_rules' => $data['reorder_rules'],
        'below_reorder_items' => $data['below_reorder_items'],
        'purchase_orders' => $data['purchase_orders']
    ]);
    
} catch (Exception $e) {
    error_log("Error getting automated reordering data: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get automated reordering data
 */
function getAutomatedReorderingData() {
    global $pdo;
    
    try {
        // Get statistics
        $statistics = getReorderingStatistics();
        
        // Get reorder rules
        $reorder_rules = getReorderRules();
        
        // Get items below reorder point
        $below_reorder_items = getBelowReorderItems();
        
        // Get recent purchase orders
        $purchase_orders = getRecentPurchaseOrders();
        
        return [
            'statistics' => $statistics,
            'reorder_rules' => $reorder_rules,
            'below_reorder_items' => $below_reorder_items,
            'purchase_orders' => $purchase_orders
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting automated reordering data: " . $e->getMessage());
        return [
            'statistics' => [],
            'reorder_rules' => [],
            'below_reorder_items' => [],
            'purchase_orders' => []
        ];
    }
}

/**
 * Get reordering statistics
 */
function getReorderingStatistics() {
    global $pdo;
    
    try {
        // Items below reorder point
        $stmt = $pdo->query("
            SELECT COUNT(*) as below_reorder_point
            FROM inventory_items i
            INNER JOIN reorder_rules r ON i.id = r.item_id
            WHERE i.quantity <= r.reorder_point AND i.status = 'active' AND r.active = 1
        ");
        $below_reorder_point = $stmt->fetch()['below_reorder_point'] ?? 0;
        
        // Pending purchase orders
        $stmt = $pdo->query("SELECT COUNT(*) as pending_pos FROM purchase_orders WHERE status IN ('draft', 'pending', 'approved')");
        $pending_pos = $stmt->fetch()['pending_pos'] ?? 0;
        
        // Auto reorder rules
        $stmt = $pdo->query("SELECT COUNT(*) as auto_reorder_rules FROM reorder_rules WHERE active = 1");
        $auto_reorder_rules = $stmt->fetch()['auto_reorder_rules'] ?? 0;
        
        // Total PO value (30 days)
        $stmt = $pdo->query("
            SELECT SUM(total_amount) as total_po_value
            FROM purchase_orders
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $total_po_value = $stmt->fetch()['total_po_value'] ?? 0;
        
        return [
            'below_reorder_point' => (int)$below_reorder_point,
            'pending_pos' => (int)$pending_pos,
            'auto_reorder_rules' => (int)$auto_reorder_rules,
            'total_po_value' => (float)$total_po_value
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting reordering statistics: " . $e->getMessage());
        return [
            'below_reorder_point' => 0,
            'pending_pos' => 0,
            'auto_reorder_rules' => 0,
            'total_po_value' => 0
        ];
    }
}

/**
 * Get reorder rules
 */
function getReorderRules() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                r.id,
                r.reorder_point,
                r.reorder_quantity,
                r.lead_time_days,
                r.auto_generate_po,
                r.active,
                i.name as item_name,
                i.quantity as current_stock
            FROM reorder_rules r
            LEFT JOIN inventory_items i ON r.item_id = i.id
            ORDER BY i.name ASC
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting reorder rules: " . $e->getMessage());
        return [];
    }
}

/**
 * Get items below reorder point
 */
function getBelowReorderItems() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                i.id,
                i.name,
                i.quantity,
                i.sku,
                c.name as category_name,
                r.reorder_point,
                r.reorder_quantity as suggested_quantity,
                s.name as supplier_name
            FROM inventory_items i
            INNER JOIN reorder_rules r ON i.id = r.item_id
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            LEFT JOIN inventory_suppliers s ON r.supplier_id = s.id
            WHERE i.quantity <= r.reorder_point 
            AND i.status = 'active' 
            AND r.active = 1
            ORDER BY i.quantity ASC
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting below reorder items: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent purchase orders
 */
function getRecentPurchaseOrders() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                po.id,
                po.po_number,
                po.status,
                po.total_amount,
                po.order_date,
                po.expected_delivery,
                s.name as supplier_name
            FROM purchase_orders po
            LEFT JOIN inventory_suppliers s ON po.supplier_id = s.id
            ORDER BY po.created_at DESC
            LIMIT 10
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting recent purchase orders: " . $e->getMessage());
        return [];
    }
}
?>
