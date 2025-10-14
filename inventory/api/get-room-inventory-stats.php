<?php
/**
 * Get room inventory statistics
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    global $pdo;
    
    // Get total rooms
    $stmt = $pdo->query("SELECT COUNT(*) as total_rooms FROM hotel_rooms");
    $total_rooms = $stmt->fetch()['total_rooms'];
    
    // Get fully stocked rooms (rooms with all items at or above par level)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT r.id) as fully_stocked
        FROM hotel_rooms r
        LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
        LEFT JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE r.id NOT IN (
            SELECT DISTINCT r2.id
            FROM hotel_rooms r2
            LEFT JOIN room_inventory_items ri2 ON r2.id = ri2.room_id
            LEFT JOIN inventory_items ii2 ON ri2.item_id = ii2.id
            WHERE ri2.quantity_current < ri2.par_level
        )
    ");
    $fully_stocked = $stmt->fetch()['fully_stocked'] ?? 0;
    
    // Get rooms needing restocking (rooms with some items below par level)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT r.id) as need_restocking
        FROM hotel_rooms r
        LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
        LEFT JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE ri.quantity_current < ri.par_level
        AND ri.quantity_current > 0
    ");
    $need_restocking = $stmt->fetch()['need_restocking'] ?? 0;
    
    // Get critical stock rooms (rooms with empty items)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT r.id) as critical_stock
        FROM hotel_rooms r
        LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
        LEFT JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE ri.quantity_current = 0
    ");
    $critical_stock = $stmt->fetch()['critical_stock'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'statistics' => [
            'total_rooms' => (int)$total_rooms,
            'fully_stocked' => (int)$fully_stocked,
            'need_restocking' => (int)$need_restocking,
            'critical_stock' => (int)$critical_stock
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>