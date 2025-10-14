<?php
/**
 * Start room restocking process
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    global $pdo;
    
    $pdo->beginTransaction();
    
    // Get rooms that need restocking
    $stmt = $pdo->query("
        SELECT ri.*, ii.item_name, ii.current_stock as main_stock
        FROM room_inventory_items ri
        JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE ri.quantity_current < ri.par_level
    ");
    $items_to_restock = $stmt->fetchAll();
    
    $restocked_count = 0;
    
    foreach ($items_to_restock as $item) {
        $needed_quantity = $item['par_level'] - $item['quantity_current'];
        
        // Check if main inventory has enough stock
        if ($item['main_stock'] >= $needed_quantity) {
            // Update room inventory
            $stmt = $pdo->prepare("
                UPDATE room_inventory_items 
                SET quantity_current = par_level, last_updated = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$item['id']]);
            
            // Reduce main inventory
            $stmt = $pdo->prepare("
                UPDATE inventory_items 
                SET current_stock = current_stock - ?
                WHERE id = ?
            ");
            $stmt->execute([$needed_quantity, $item['item_id']]);
            
            // Log transaction
            $stmt = $pdo->prepare("
                INSERT INTO inventory_transactions (item_id, transaction_type, quantity, reason, user_id, performed_by, created_at)
                VALUES (?, 'out', ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $item['item_id'],
                $needed_quantity,
                "Room restock: {$item['item_name']} - {$needed_quantity} units",
                $_SESSION['user_id'],
                $_SESSION['user_id']
            ]);
            
            $restocked_count++;
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Room restocking completed. {$restocked_count} items restocked."
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>