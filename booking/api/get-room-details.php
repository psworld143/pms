<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Room Details API
 */

session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and has access (manager or front_desk only)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$room_id = $_GET['id'] ?? null;

if (!$room_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Room ID is required'
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            room_number,
            room_type,
            floor,
            capacity,
            rate,
            status,
            housekeeping_status,
            amenities
        FROM rooms 
        WHERE id = ?
    ");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$room) {
        echo json_encode([
            'success' => false,
            'message' => 'Room not found'
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'room' => $room
    ]);
    
} catch (PDOException $e) {
    error_log('Error getting room details: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error getting room details: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>