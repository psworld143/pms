<?php
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $room_type = $_GET['room_type'] ?? '';
    $status = $_GET['status'] ?? '';
    $floor = $_GET['floor'] ?? '';
    
    $rooms = getRooms($room_type, $status, $floor);
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
    
} catch (Exception $e) {
    error_log("Error getting rooms: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get rooms with filters
 */
function getRooms($room_type = '', $status = '', $floor = '') {
    global $pdo;
    
    try {
        $where_conditions = ["1=1"];
        $params = [];
        
        // Room type filter
        if (!empty($room_type)) {
            $where_conditions[] = "room_type = ?";
            $params[] = $room_type;
        }
        
        // Status filter
        if (!empty($status)) {
            $where_conditions[] = "status = ?";
            $params[] = $status;
        }
        
        // Floor filter
        if (!empty($floor)) {
            $where_conditions[] = "floor = ?";
            $params[] = $floor;
        }
        
        $where_clause = implode(" AND ", $where_conditions);
        
        $stmt = $pdo->prepare("
            SELECT r.*, 
                   CASE r.room_type 
                       WHEN 'standard' THEN 'Standard Room'
                       WHEN 'deluxe' THEN 'Deluxe Room'
                       WHEN 'suite' THEN 'Suite'
                       WHEN 'presidential' THEN 'Presidential Suite'
                       ELSE r.room_type
                   END as room_type_name
            FROM rooms r
            WHERE {$where_clause}
            ORDER BY r.room_number ASC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting rooms: " . $e->getMessage());
        return [];
    }
}
?>
