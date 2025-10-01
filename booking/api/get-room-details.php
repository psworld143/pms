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
    $room_id = $_GET['id'] ?? null;
    
    if (!$room_id) {
        throw new Exception('Room ID is required');
    }
    
    $room = getRoomDetails($room_id);
    
    if (!$room) {
        throw new Exception('Room not found');
    }
    
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
 * Get detailed room information
 */
function getRoomDetails($room_id) {
    global $pdo;
    
    try {
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
            WHERE r.id = ?
        ");
        $stmt->execute([$room_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error getting room details: " . $e->getMessage());
        return null;
    }
}
?>
