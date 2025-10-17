<?php
/**
 * Get room inventory statistics
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get total rooms
    $stmt = $pdo->query("SELECT COUNT(*) as total_rooms FROM rooms");
    $total_rooms = $stmt->fetch()['total_rooms'];
    
    // Get room stock status counts
    $stmt = $pdo->query("
        SELECT 
            r.id,
            r.room_number,
            CASE 
                WHEN COUNT(ri.id) = 0 THEN 'no_inventory'
                WHEN SUM(CASE WHEN ri.quantity_current < ri.par_level THEN 1 ELSE 0 END) = 0 THEN 'fully_stocked'
                WHEN SUM(CASE WHEN ri.quantity_current = 0 THEN 1 ELSE 0 END) > 0 THEN 'critical_stock'
                ELSE 'needs_restocking'
            END as stock_status
        FROM rooms r
        LEFT JOIN room_inventory ri ON r.id = ri.room_id
        GROUP BY r.id, r.room_number
    ");
    $room_statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count rooms by status
    $fully_stocked = 0;
    $need_restocking = 0;
    $unknown_rooms = 0;
    
    foreach ($room_statuses as $room) {
        switch ($room['stock_status']) {
            case 'fully_stocked':
                $fully_stocked++;
                break;
            case 'needs_restocking':
                $need_restocking++;
                break;
            case 'critical_stock':
            case 'no_inventory':
            default:
                $unknown_rooms++;
                break;
        }
    }
    
    // Get total items across all rooms
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_items 
        FROM room_inventory ri
        JOIN inventory_items ii ON ri.item_id = ii.id
    ");
    $total_items = $stmt->fetch()['total_items'] ?? 0;
    
    // Get low stock items (items below par level)
    $stmt = $pdo->query("
        SELECT COUNT(*) as low_stock_items
        FROM room_inventory ri
        WHERE ri.quantity_current < ri.par_level
    ");
    $low_stock_items = $stmt->fetch()['low_stock_items'] ?? 0;
    
    // Get pending requests
    $stmt = $pdo->query("
        SELECT COUNT(*) as pending_requests
        FROM supply_requests
        WHERE status = 'pending'
    ");
    $pending_requests = $stmt->fetch()['pending_requests'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_rooms' => (int)$total_rooms,
            'fully_stocked' => (int)$fully_stocked,
            'need_restocking' => (int)$need_restocking,
            'unknown_rooms' => (int)$unknown_rooms,
            'total_items' => (int)$total_items,
            'missing_items' => (int)$low_stock_items
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-room-inventory-stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>