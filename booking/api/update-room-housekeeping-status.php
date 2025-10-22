<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Update Room Housekeeping Status API
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit();
    }
    
    $room_id = $input['room_id'] ?? null;
    $housekeeping_status = $input['housekeeping_status'] ?? null;
    
    if (!$room_id || !$housekeeping_status) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit();
    }
    
    // Validate housekeeping status
    $valid_statuses = ['clean', 'dirty', 'maintenance', 'cleaning'];
    if (!in_array($housekeeping_status, $valid_statuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid housekeeping status'
        ]);
        exit();
    }
    
    // Update the room's housekeeping status
    $stmt = $pdo->prepare("
        UPDATE rooms 
        SET housekeeping_status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    
    $stmt->execute([$housekeeping_status, $room_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Room housekeeping status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Room not found or no changes made'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Error updating room housekeeping status: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error updating room housekeeping status: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
