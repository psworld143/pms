<?php
/**
 * Get rooms for a specific floor
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$floor = $_GET['floor'] ?? $_GET['floor_id'] ?? '';

if (empty($floor)) {
    echo json_encode(['success' => false, 'message' => 'Floor required']);
    exit();
}

try {
    // Get rooms for the specified floor (including capacity from booking system)
    $stmt = $pdo->prepare("
        SELECT r.*, 
               r.capacity,
               COUNT(ri.id) as total_items,
               CASE 
                   WHEN COUNT(ri.id) = 0 THEN 'unknown'
                   WHEN COUNT(CASE WHEN ri.quantity_current < ri.par_level THEN 1 END) = 0 THEN 'fully_stocked'
                   WHEN COUNT(CASE WHEN ri.quantity_current = 0 THEN 1 END) > 0 THEN 'critical_stock'
                   ELSE 'needs_restocking'
               END as stock_status
        FROM rooms r
        LEFT JOIN room_inventory ri ON r.id = ri.room_id
        WHERE r.floor = ?
        GROUP BY r.id
        ORDER BY r.room_number
    ");
    $stmt->execute([$floor]);
    $rooms = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-rooms-for-floor.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>