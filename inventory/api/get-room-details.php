<?php
/**
 * Get Room Details with Inventory
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
    $room_id = $_GET['room_id'] ?? '';
    
    if (empty($room_id)) {
        echo json_encode(['success' => false, 'message' => 'Room ID is required']);
        exit();
    }
    
    $room = getRoomDetails($room_id);
    
    echo json_encode([
        'success' => true,
        'room' => $room
    ]);
    
} catch (Exception $e) {
    error_log("Error getting room details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get room details with inventory items
 */
function getRoomDetails($room_id) {
    global $pdo;
    
    try {
        // Get room basic information
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.room_number,
                r.room_type,
                r.status,
                r.max_occupancy,
                r.description,
                f.floor_name,
                f.floor_number
            FROM hotel_rooms r
            LEFT JOIN hotel_floors f ON r.floor_id = f.id
            WHERE r.id = ? AND r.active = 1
        ");
        
        $stmt->execute([$room_id]);
        $room = $stmt->fetch();
        
        if (!$room) {
            return null;
        }
        
        // Get room inventory items
        $stmt = $pdo->prepare("
            SELECT 
                ri.id,
                ri.quantity_allocated,
                ri.quantity_current,
                ri.par_level,
                ri.last_restocked,
                ri.last_audited,
                ri.notes,
                i.name as item_name,
                i.sku,
                i.description as item_description,
                i.unit,
                c.name as category_name,
                c.color as category_color,
                c.icon as category_icon
            FROM room_inventory_items ri
            LEFT JOIN inventory_items i ON ri.item_id = i.id
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE ri.room_id = ?
            ORDER BY c.name, i.name ASC
        ");
        
        $stmt->execute([$room_id]);
        $room['inventory_items'] = $stmt->fetchAll();
        
        return $room;
        
    } catch (PDOException $e) {
        error_log("Error getting room details: " . $e->getMessage());
        return null;
    }
}
?>
