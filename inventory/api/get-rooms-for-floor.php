<?php
/**
 * Get rooms for a specific floor
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$floor_id = $_GET['floor_id'] ?? '';

if (empty($floor_id)) {
    echo json_encode(['success' => false, 'message' => 'Floor ID required']);
    exit();
}

try {
    global $pdo;
    
    // Get rooms for the specified floor
    $stmt = $pdo->prepare("
        SELECT r.*, 
               COUNT(ri.id) as total_items,
               CASE 
                   WHEN COUNT(ri.id) = 0 THEN 'unknown'
                   WHEN COUNT(CASE WHEN ri.quantity_current < ri.par_level THEN 1 END) = 0 THEN 'fully_stocked'
                   WHEN COUNT(CASE WHEN ri.quantity_current = 0 THEN 1 END) > 0 THEN 'critical_stock'
                   ELSE 'needs_restocking'
               END as stock_status
        FROM hotel_rooms r
        LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
        WHERE r.floor_id = ?
        GROUP BY r.id
        ORDER BY r.room_number
    ");
    $stmt->execute([$floor_id]);
    $rooms = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>