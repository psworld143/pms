<?php
/**
 * Run Auto Reorder
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
    $result = runAutoReorder();
    
    echo json_encode([
        'success' => true,
        'message' => 'Auto reorder completed successfully',
        'orders_generated' => $result['orders_generated'],
        'items_processed' => $result['items_processed']
    ]);
    
} catch (Exception $e) {
    error_log("Error running auto reorder: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Run auto reorder process
 */
function runAutoReorder() {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $orders_generated = 0;
        $items_processed = 0;
        
        // Get items below reorder point with active reorder rules
        $stmt = $pdo->query("
            SELECT 
                ii.id,
                ii.item_name,
                ii.current_stock,
                ii.minimum_stock,
                rr.reorder_point,
                rr.reorder_quantity,
                rr.lead_time_days,
                rr.supplier_id,
                rr.auto_generate_po,
                s.name as supplier_name
            FROM inventory_items ii
            JOIN reorder_rules rr ON ii.id = rr.item_id
            LEFT JOIN suppliers s ON rr.supplier_id = s.id
            WHERE ii.current_stock <= rr.reorder_point
            AND rr.active = 1
        ");
        
        $items_to_reorder = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group items by supplier for purchase orders
        $supplier_orders = [];
        
        foreach ($items_to_reorder as $item) {
            $supplier_id = $item['supplier_id'] ?: 0;
            
            if (!isset($supplier_orders[$supplier_id])) {
                $supplier_orders[$supplier_id] = [
                    'supplier_name' => $item['supplier_name'] ?: 'No Supplier',
                    'items' => []
                ];
            }
            
            $supplier_orders[$supplier_id]['items'][] = $item;
            $items_processed++;
        }
        
        // Generate purchase orders
        foreach ($supplier_orders as $supplier_id => $order_data) {
            if ($supplier_id > 0) {
                // Create purchase order
                $po_number = 'PO-' . date('Ymd') . '-' . str_pad($orders_generated + 1, 4, '0', STR_PAD_LEFT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO purchase_orders (
                        po_number,
                        supplier_id,
                        status,
                        order_date,
                        expected_delivery_date,
                        total_amount
                    ) VALUES (?, ?, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 0)
                ");
                
                $stmt->execute([$po_number, $supplier_id]);
                $po_id = $pdo->lastInsertId();
                
                $total_amount = 0;
                
                // Add items to purchase order
                foreach ($order_data['items'] as $item) {
                    $quantity = $item['reorder_quantity'];
                    $unit_price = $item['unit_price'] ?? 0;
                    $line_total = $quantity * $unit_price;
                    $total_amount += $line_total;
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO purchase_order_items (
                            po_id,
                            item_id,
                            quantity,
                            unit_price,
                            line_total
                        ) VALUES (?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $po_id,
                        $item['id'],
                        $quantity,
                        $unit_price,
                        $line_total
                    ]);
                }
                
                // Update purchase order total
                $stmt = $pdo->prepare("
                    UPDATE purchase_orders 
                    SET total_amount = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$total_amount, $po_id]);
                
                $orders_generated++;
            }
        }
        
        $pdo->commit();
        
        return [
            'orders_generated' => $orders_generated,
            'items_processed' => $items_processed
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>
