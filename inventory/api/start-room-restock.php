<?php
/**
 * Start Room Restock
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
    $result = startRoomRestock();
    
    echo json_encode([
        'success' => true,
        'message' => 'Room restocking started successfully',
        'rooms_restocked' => $result['rooms_restocked'],
        'items_restocked' => $result['items_restocked']
    ]);
    
} catch (Exception $e) {
    error_log("Error starting room restock: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Start room restock process
 */
function startRoomRestock() {
    global $pdo;
    
    try {
        // Get rooms that need restocking
        $stmt = $pdo->query("
            SELECT DISTINCT r.id, r.room_number, f.floor_name
            FROM hotel_rooms r
            LEFT JOIN hotel_floors f ON r.floor_id = f.id
            INNER JOIN room_inventory_items ri ON r.id = ri.room_id
            WHERE r.active = 1 
            AND ri.quantity_current < ri.par_level
        ");
        $rooms = $stmt->fetchAll();
        
        $rooms_restocked = 0;
        $items_restocked = 0;
        
        foreach ($rooms as $room) {
            // Get items that need restocking in this room
            $stmt = $pdo->prepare("
                SELECT ri.id, ri.item_id, ri.par_level, ri.quantity_current, i.name as item_name
                FROM room_inventory_items ri
                LEFT JOIN inventory_items i ON ri.item_id = i.id
                WHERE ri.room_id = ? AND ri.quantity_current < ri.par_level
            ");
            $stmt->execute([$room['id']]);
            $items = $stmt->fetchAll();
            
            $room_items_restocked = 0;
            
            foreach ($items as $item) {
                // Restock to par level
                $restock_quantity = $item['par_level'] - $item['quantity_current'];
                
                $stmt = $pdo->prepare("
                    UPDATE room_inventory_items 
                    SET quantity_current = par_level, last_restocked = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$item['id']]);
                
                // Create restock transaction record
                $stmt = $pdo->prepare("
                    INSERT INTO room_inventory_transactions 
                    (room_id, item_id, transaction_type, quantity, reason, created_by, created_at) 
                    VALUES (?, ?, 'restock', ?, 'Automatic restock to par level', ?, NOW())
                ");
                $stmt->execute([$room['id'], $item['item_id'], $restock_quantity, $_SESSION['user_id']]);
                
                $room_items_restocked++;
                $items_restocked++;
            }
            
            if ($room_items_restocked > 0) {
                $rooms_restocked++;
            }
        }
        
        return [
            'rooms_restocked' => $rooms_restocked,
            'items_restocked' => $items_restocked
        ];
        
    } catch (PDOException $e) {
        error_log("Error starting room restock: " . $e->getMessage());
        throw new Exception("Database error during room restock");
    }
}
?>
