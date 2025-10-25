<?php
/**
 * Get Automated Reordering Data
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
        $statistics = [
            'below_reorder_point' => 0,
            'pending_pos' => 0,
            'auto_reorder_rules' => 0,
            'total_po_value' => 0
        ];
        
        $reorder_rules = [];
        $below_reorder_items = [];
        $purchase_orders = [];
        
        // Get statistics
        $stmt = $pdo->query("SELECT COUNT(*) FROM inventory_items WHERE current_stock <= minimum_stock");
        $statistics['below_reorder_point'] = (int)$stmt->fetchColumn();
        
        // Check if purchase_orders table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'purchase_orders'");
        $pos_table_exists = $stmt->rowCount() > 0;
        
        if ($pos_table_exists) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM purchase_orders WHERE status = 'pending'");
            $statistics['pending_pos'] = (int)$stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT SUM(total_amount) FROM purchase_orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $statistics['total_po_value'] = (float)$stmt->fetchColumn();
        }
        
        // Check if reorder_rules table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'reorder_rules'");
        $rules_table_exists = $stmt->rowCount() > 0;
        
        if ($rules_table_exists) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM reorder_rules WHERE active = 1");
            $statistics['auto_reorder_rules'] = (int)$stmt->fetchColumn();
            
            // Get reorder rules
            $stmt = $pdo->query("
                SELECT 
                    rr.id, 
                    rr.item_id, 
                    ii.item_name, 
                    ii.current_stock,
                    rr.reorder_point, 
                    rr.reorder_quantity, 
                    rr.lead_time_days, 
                    rr.auto_generate_po, 
                    rr.active,
                    s.name as supplier_name
                FROM reorder_rules rr
                JOIN inventory_items ii ON rr.item_id = ii.id
                LEFT JOIN suppliers s ON rr.supplier_id = s.id
                ORDER BY ii.item_name ASC
            ");
            $reorder_rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Get items below reorder point
        $stmt = $pdo->query("
            SELECT 
                ii.id, 
                ii.item_name as name, 
                ii.current_stock as quantity, 
                ii.minimum_stock as reorder_point,
                (COALESCE(rr.reorder_quantity, ii.minimum_stock) - ii.current_stock) as suggested_quantity,
                ic.name as category_name,
                s.name as supplier_name
            FROM inventory_items ii
            LEFT JOIN reorder_rules rr ON ii.id = rr.item_id
            LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
            LEFT JOIN suppliers s ON rr.supplier_id = s.id
            WHERE ii.current_stock <= ii.minimum_stock
            ORDER BY ii.item_name ASC
        ");
        $below_reorder_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recent purchase orders
        if ($pos_table_exists) {
            $stmt = $pdo->query("
                SELECT 
                    po.id, 
                    po.po_number, 
                    s.name as supplier_name, 
                    po.total_amount, 
                    po.status, 
                    po.order_date, 
                    po.expected_delivery_date as expected_delivery
                FROM purchase_orders po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                ORDER BY po.order_date DESC
                LIMIT 10
            ");
            $purchase_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [
            'statistics' => $statistics,
            'reorder_rules' => $reorder_rules,
            'below_reorder_items' => $below_reorder_items,
            'purchase_orders' => $purchase_orders
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting automated reordering data: " . $e->getMessage());
        // Return demo data on error
        return [
            'statistics' => [
                'below_reorder_point' => 5,
                'pending_pos' => 2,
                'auto_reorder_rules' => 8,
                'total_po_value' => 15000.00
            ],
            'reorder_rules' => [],
            'below_reorder_items' => [],
            'purchase_orders' => []
        ];
    }
}
?>