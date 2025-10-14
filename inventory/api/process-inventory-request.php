<?php
/**
 * Process Inventory Request
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
    $request_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if ($request_id <= 0 || empty($action)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }
    
    $result = processInventoryRequest($request_id, $action, $notes);
    
    echo json_encode([
        'success' => true,
        'message' => 'Request processed successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error processing inventory request: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Process inventory request
 */
function processInventoryRequest($request_id, $action, $notes) {
    global $pdo;
    
    try {
        // Check if inventory_requests table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_requests'");
        $table_exists = $stmt->rowCount() > 0;
        
        if (!$table_exists) {
            return ['success' => true];
        }
        
        $pdo->beginTransaction();
        
        // Update request status
        $stmt = $pdo->prepare("
            UPDATE inventory_requests 
            SET status = ?, processed_by = ?, processed_at = NOW(), notes = ? 
            WHERE id = ?
        ");
        $stmt->execute([$action, $_SESSION['user_id'], $notes, $request_id]);
        
        // If approved, create inventory transaction
        if ($action === 'approved') {
            // Get request details
            $stmt = $pdo->prepare("
                SELECT item_name, quantity_requested, department 
                FROM inventory_requests 
                WHERE id = ?
            ");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                // Find matching inventory item
                $stmt = $pdo->prepare("
                    SELECT id, unit_price 
                    FROM inventory_items 
                    WHERE item_name = ? 
                    LIMIT 1
                ");
                $stmt->execute([$request['item_name']]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($item) {
                    // Create transaction
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
                        ) VALUES (?, 'in', ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $quantity = $request['quantity_requested'];
                    $unit_price = $item['unit_price'] ?: 0;
                    $total_value = $quantity * $unit_price;
                    $reason = "Approved request from " . $request['department'];
                    
                    $stmt->execute([
                        $item['id'],
                        $quantity,
                        $unit_price,
                        $total_value,
                        $reason,
                        $_SESSION['user_id']
                    ]);
                    
                    // Update inventory stock
                    $stmt = $pdo->prepare("
                        UPDATE inventory_items 
                        SET current_stock = current_stock + ?, last_updated = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$quantity, $item['id']]);
                }
            }
        }
        
        $pdo->commit();
        
        return ['success' => true];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>
