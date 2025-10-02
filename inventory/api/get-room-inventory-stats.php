<?php
/**
 * Get Room Inventory Statistics
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
    $stats = getRoomInventoryStatistics();
    
    echo json_encode([
        'success' => true,
        'statistics' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error getting room inventory statistics: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get room inventory statistics
 */
function getRoomInventoryStatistics() {
    global $pdo;
    
    try {
        // Get total rooms count
        $stmt = $pdo->query("SELECT COUNT(*) as total_rooms FROM hotel_rooms WHERE active = 1");
        $total_rooms = $stmt->fetch()['total_rooms'];
        
        // Get fully stocked rooms (all items at or above par level)
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT r.id) as fully_stocked
            FROM hotel_rooms r
            LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
            WHERE r.active = 1
            AND (ri.room_id IS NULL OR ri.quantity_current >= ri.par_level)
            AND r.id NOT IN (
                SELECT DISTINCT room_id 
                FROM room_inventory_items 
                WHERE quantity_current < par_level
            )
        ");
        $fully_stocked = $stmt->fetch()['fully_stocked'];
        
        // Get rooms needing restocking (some items below par level but not empty)
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT r.id) as need_restocking
            FROM hotel_rooms r
            INNER JOIN room_inventory_items ri ON r.id = ri.room_id
            WHERE r.active = 1
            AND ri.quantity_current < ri.par_level
            AND ri.quantity_current > 0
        ");
        $need_restocking = $stmt->fetch()['need_restocking'];
        
        // Get rooms with critical stock (some items completely empty)
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT r.id) as critical_stock
            FROM hotel_rooms r
            INNER JOIN room_inventory_items ri ON r.id = ri.room_id
            WHERE r.active = 1
            AND ri.quantity_current = 0
        ");
        $critical_stock = $stmt->fetch()['critical_stock'];
        
        return [
            'total_rooms' => (int)$total_rooms,
            'fully_stocked' => (int)$fully_stocked,
            'need_restocking' => (int)$need_restocking,
            'critical_stock' => (int)$critical_stock
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting room inventory statistics: " . $e->getMessage());
        return [
            'total_rooms' => 0,
            'fully_stocked' => 0,
            'need_restocking' => 0,
            'critical_stock' => 0
        ];
    }
}
?>
