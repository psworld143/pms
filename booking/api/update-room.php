<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    if (empty($input['room_id'])) {
        throw new Exception('Room ID is required');
    }
    
    $room_id = intval($input['room_id']);
    
    // Check if room exists
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        throw new Exception('Room not found');
    }
    
    // Validate room type if provided
    if (isset($input['room_type'])) {
        $valid_room_types = ['standard', 'deluxe', 'suite', 'presidential'];
        if (!in_array($input['room_type'], $valid_room_types)) {
            throw new Exception('Invalid room type');
        }
    }
    
    // Check if room number already exists (excluding current room)
    if (isset($input['room_number'])) {
        $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
        $stmt->execute([$input['room_number'], $room_id]);
        if ($stmt->fetch()) {
            throw new Exception('Room number already exists');
        }
    }
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];
    
    if (isset($input['room_number'])) {
        $update_fields[] = "room_number = ?";
        $params[] = $input['room_number'];
    }
    
    if (isset($input['room_type'])) {
        $update_fields[] = "room_type = ?";
        $params[] = $input['room_type'];
    }
    
    if (isset($input['floor'])) {
        $update_fields[] = "floor = ?";
        $params[] = intval($input['floor']);
    }
    
    if (isset($input['capacity'])) {
        $update_fields[] = "capacity = ?";
        $params[] = intval($input['capacity']);
    }
    
    if (isset($input['rate'])) {
        $update_fields[] = "rate = ?";
        $params[] = floatval($input['rate']);
    }
    
    if (isset($input['status'])) {
        $update_fields[] = "status = ?";
        $params[] = $input['status'];
    }
    
    if (isset($input['housekeeping_status'])) {
        $update_fields[] = "housekeeping_status = ?";
        $params[] = $input['housekeeping_status'];
    }
    
    if (isset($input['amenities'])) {
        $update_fields[] = "amenities = ?";
        $params[] = $input['amenities'];
    }
    
    if (empty($update_fields)) {
        throw new Exception('No fields to update');
    }
    
    $update_fields[] = "updated_at = NOW()";
    $params[] = $room_id;
    
    $sql = "UPDATE rooms SET " . implode(', ', $update_fields) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Log activity
    logActivity($_SESSION['user_id'], 'room_updated', "Updated room: {$room['room_number']}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Room updated successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error updating room: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
