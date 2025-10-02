<?php
/**
 * Get Mobile Interface Statistics
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
    $user_id = $_SESSION['user_id'];
    $stats = getMobileStats($user_id);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error getting mobile stats: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get mobile interface statistics for housekeeping staff
 */
function getMobileStats($user_id) {
    global $pdo;
    
    try {
        // Get rooms assigned to this user (if any assignment system exists)
        // For now, we'll get all active rooms as this is a training system
        $stmt = $pdo->query("SELECT COUNT(*) as my_rooms FROM hotel_rooms WHERE active = 1");
        $my_rooms = $stmt->fetch()['my_rooms'];
        
        // Get rooms that need restocking
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT r.id) as need_restock
            FROM hotel_rooms r
            INNER JOIN room_inventory_items ri ON r.id = ri.room_id
            WHERE r.active = 1
            AND ri.quantity_current < ri.par_level
        ");
        $need_restock = $stmt->fetch()['need_restock'];
        
        return [
            'my_rooms' => (int)$my_rooms,
            'need_restock' => (int)$need_restock
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting mobile stats: " . $e->getMessage());
        return [
            'my_rooms' => 0,
            'need_restock' => 0
        ];
    }
}
?>
