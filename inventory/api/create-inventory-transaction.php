<?php
/**
 * Create Inventory Transaction
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
    $item_id = $_POST['item_id'] ?? 0;
    $transaction_type = $_POST['transaction_type'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $unit_price = $_POST['unit_price'] ?? 0;
    $reason = $_POST['reason'] ?? '';
    
    if ($item_id <= 0 || empty($transaction_type) || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }
    
    $result = createInventoryTransaction($item_id, $transaction_type, $quantity, $unit_price, $reason);
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaction created successfully',
        'transaction_id' => $result['transaction_id']
    ]);
    
} catch (Exception $e) {
    error_log("Error creating inventory transaction: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Create inventory transaction
 */
function createInventoryTransaction($item_id, $transaction_type, $quantity, $unit_price, $reason) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get current stock
        $stmt = $pdo->prepare("SELECT current_stock FROM inventory_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            throw new Exception('Item not found');
        }
        
        $current_stock = $item['current_stock'];
        $new_stock = $current_stock;
        
        // Calculate new stock based on transaction type
        switch ($transaction_type) {
            case 'in':
                $new_stock = $current_stock + $quantity;
                break;
            case 'out':
                $new_stock = $current_stock - $quantity;
                if ($new_stock < 0) {
                    throw new Exception('Insufficient stock');
                }
                break;
            case 'adjustment':
                $new_stock = $quantity; // For adjustment, quantity is the new stock level
                break;
            default:
                throw new Exception('Invalid transaction type');
        }
        
        // Update inventory stock
        $stmt = $pdo->prepare("
            UPDATE inventory_items 
            SET current_stock = ?, last_updated = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$new_stock, $item_id]);
        
        // Create transaction record
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
        
        $total_value = $quantity * $unit_price;
        
        $stmt->execute([
            $item_id,
            $transaction_type,
            $quantity,
            $unit_price,
            $total_value,
            $reason,
            $_SESSION['user_id']
        ]);
        
        $transaction_id = $pdo->lastInsertId();
        
        $pdo->commit();
        
        return ['transaction_id' => $transaction_id];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>
