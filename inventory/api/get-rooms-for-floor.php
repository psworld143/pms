<?php
/**
 * Get Rooms for Floor
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
    $floor_id = $_GET['floor_id'] ?? '';
    
    if (empty($floor_id)) {
        echo json_encode(['success' => false, 'message' => 'Floor ID is required']);
        exit();
    }
    
    $rooms = getRoomsForFloor($floor_id);
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
    
} catch (Exception $e) {
    error_log("Error getting rooms for floor: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get rooms for a specific floor
 */
function getRoomsForFloor($floor_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.room_number,
                r.room_type,
                r.status,
                r.max_occupancy,
                r.description,
                f.floor_name,
                f.floor_number,
                COUNT(ri.id) as total_items,
                CASE 
                    WHEN COUNT(ri.id) = 0 THEN 'fully_stocked'
                    WHEN SUM(CASE WHEN ri.quantity_current = 0 THEN 1 ELSE 0 END) > 0 THEN 'critical_stock'
                    WHEN SUM(CASE WHEN ri.quantity_current < ri.par_level THEN 1 ELSE 0 END) > 0 THEN 'needs_restocking'
                    ELSE 'fully_stocked'
                END as stock_status
            FROM hotel_rooms r
            LEFT JOIN hotel_floors f ON r.floor_id = f.id
            LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
            WHERE r.floor_id = ? AND r.active = 1
            GROUP BY r.id, r.room_number, r.room_type, r.status, r.max_occupancy, r.description, f.floor_name, f.floor_number
            ORDER BY r.room_number ASC
        ");
        
        $stmt->execute([$floor_id]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting rooms for floor: " . $e->getMessage());
        return [];
    }
}
?>
