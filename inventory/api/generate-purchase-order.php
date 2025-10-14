<?php
/**
 * Generate Purchase Order
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
    $item_ids = $_POST['item_ids'] ?? [];
    $supplier_id = $_POST['supplier_id'] ?? 0;
    $expected_delivery = $_POST['expected_delivery'] ?? date('Y-m-d', strtotime('+7 days'));
    
    if (empty($item_ids) || $supplier_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }
    
    $result = generatePurchaseOrder($item_ids, $supplier_id, $expected_delivery);
    
    echo json_encode([
        'success' => true,
        'message' => 'Purchase order generated successfully',
        'po_id' => $result['po_id'],
        'po_number' => $result['po_number']
    ]);
    
} catch (Exception $e) {
    error_log("Error generating purchase order: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Generate purchase order
 */
function generatePurchaseOrder($item_ids, $supplier_id, $expected_delivery) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Generate PO number
        $po_number = 'PO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Create purchase order
        $stmt = $pdo->prepare("
            INSERT INTO purchase_orders (
                po_number,
                supplier_id,
                status,
                order_date,
                expected_delivery_date,
                total_amount
            ) VALUES (?, ?, 'pending', NOW(), ?, 0)
        ");
        
        $stmt->execute([$po_number, $supplier_id, $expected_delivery]);
        $po_id = $pdo->lastInsertId();
        
        $total_amount = 0;
        
        // Add items to purchase order
        foreach ($item_ids as $item_id) {
            // Get item details
            $stmt = $pdo->prepare("
                SELECT 
                    ii.item_name,
                    ii.unit_price,
                    rr.reorder_quantity
                FROM inventory_items ii
                LEFT JOIN reorder_rules rr ON ii.id = rr.item_id
                WHERE ii.id = ?
            ");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item) {
                $quantity = $item['reorder_quantity'] ?: 1;
                $unit_price = $item['unit_price'] ?: 0;
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
                    $item_id,
                    $quantity,
                    $unit_price,
                    $line_total
                ]);
            }
        }
        
        // Update purchase order total
        $stmt = $pdo->prepare("
            UPDATE purchase_orders 
            SET total_amount = ? 
            WHERE id = ?
        ");
        $stmt->execute([$total_amount, $po_id]);
        
        $pdo->commit();
        
        return [
            'po_id' => $po_id,
            'po_number' => $po_number
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>
