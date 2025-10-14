<?php
/**
 * Process Scanned Items
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
    $items = $_POST['items'] ?? [];
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'No items provided']);
        exit();
    }
    
    $result = processScannedItems($items);
    
    echo json_encode([
        'success' => true,
        'message' => 'Items processed successfully',
        'processed_count' => $result['processed_count']
    ]);
    
} catch (Exception $e) {
    error_log("Error processing scanned items: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Process scanned items
 */
function processScannedItems($items) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $processed_count = 0;
        
        foreach ($items as $item) {
            $item_id = $item['item_id'] ?? 0;
            $quantity = $item['quantity'] ?? 0;
            $action = $item['action'] ?? 'usage';
            $location = $item['location'] ?? '';
            $notes = $item['notes'] ?? '';
            
            if ($item_id <= 0 || $quantity <= 0) {
                continue;
            }
            
            // Get current stock
            $stmt = $pdo->prepare("SELECT current_stock, unit_price FROM inventory_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item_data) {
                continue;
            }
            
            $current_stock = $item_data['current_stock'];
            $unit_price = $item_data['unit_price'];
            
            // Calculate new stock based on action
            $new_stock = $current_stock;
            $transaction_type = '';
            $reason = '';
            
            switch ($action) {
                case 'usage':
                    $new_stock = $current_stock - $quantity;
                    $transaction_type = 'out';
                    $reason = "Used in $location. $notes";
                    break;
                case 'restock':
                    $new_stock = $current_stock + $quantity;
                    $transaction_type = 'in';
                    $reason = "Restocked to $location. $notes";
                    break;
                case 'damage':
                    $new_stock = $current_stock - $quantity;
                    $transaction_type = 'out';
                    $reason = "Damaged/Discarded from $location. $notes";
                    break;
                case 'audit':
                    $new_stock = $quantity; // For audit, quantity is the new stock level
                    $transaction_type = 'adjustment';
                    $reason = "Audit adjustment for $location. $notes";
                    break;
                default:
                    continue 2;
            }
            
            // Update inventory
            $stmt = $pdo->prepare("
                UPDATE inventory_items 
                SET current_stock = ?, last_updated = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$new_stock, $item_id]);
            
            // Log transaction
            $stmt = $pdo->prepare("
                INSERT INTO inventory_transactions (
                    item_id,
                    transaction_type,
                    quantity,
                    unit_price,
                    total_value,
                    reason,
                    performed_by,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $quantity_change = abs($new_stock - $current_stock);
            $total_value = $quantity_change * $unit_price;
            
            $stmt->execute([
                $item_id,
                $transaction_type,
                $quantity_change,
                $unit_price,
                $total_value,
                $reason,
                $_SESSION['user_id']
            ]);
            
            $processed_count++;
        }
        
        $pdo->commit();
        
        return ['processed_count' => $processed_count];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>