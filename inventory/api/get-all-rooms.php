<?php
/**
 * Get all rooms for dropdown selection
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get all rooms
    $stmt = $pdo->query("
        SELECT id, room_number, room_type, floor, capacity, status
        FROM rooms 
        ORDER BY room_number ASC
    ");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-all-rooms.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
