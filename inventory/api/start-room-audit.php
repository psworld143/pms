<?php
/**
 * Start Room Audit
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
    $result = startRoomAudit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Room audit started successfully',
        'rooms_audited' => $result['rooms_audited']
    ]);
    
} catch (Exception $e) {
    error_log("Error starting room audit: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Start room audit process
 */
function startRoomAudit() {
    global $pdo;
    
    try {
        // Get all active rooms
        $stmt = $pdo->query("
            SELECT r.id, r.room_number, f.floor_name
            FROM hotel_rooms r
            LEFT JOIN hotel_floors f ON r.floor_id = f.id
            WHERE r.active = 1
        ");
        $rooms = $stmt->fetchAll();
        
        $rooms_audited = 0;
        
        foreach ($rooms as $room) {
            // Update last audited timestamp for room inventory items
            $stmt = $pdo->prepare("
                UPDATE room_inventory_items 
                SET last_audited = NOW() 
                WHERE room_id = ?
            ");
            $stmt->execute([$room['id']]);
            
            // Create audit transaction record
            $stmt = $pdo->prepare("
                INSERT INTO room_inventory_transactions 
                (room_id, transaction_type, reason, created_by, created_at) 
                VALUES (?, 'audit', 'Room audit completed', ?, NOW())
            ");
            $stmt->execute([$room['id'], $_SESSION['user_id']]);
            
            $rooms_audited++;
        }
        
        return [
            'rooms_audited' => $rooms_audited
        ];
        
    } catch (PDOException $e) {
        error_log("Error starting room audit: " . $e->getMessage());
        throw new Exception("Database error during room audit");
    }
}
?>
