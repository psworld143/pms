<?php
/**
 * Start Room Check for Housekeeping
 * Initiates a room check process for housekeeping users
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in and has housekeeping role
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'housekeeping') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Housekeeping role required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Log the room check activity
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
        VALUES (?, 'room_check_started', 'Housekeeping user started room check process', ?, ?, NOW())
    ");
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt->execute([$user_id, $ip_address, $user_agent]);
    
    // Update last check time for assigned rooms
    $stmt = $pdo->prepare("
        UPDATE rooms 
        SET last_housekeeping_check = NOW() 
        WHERE assigned_housekeeping = ? OR assigned_housekeeping IS NULL
    ");
    $stmt->execute([$user_id]);
    
    $affected_rooms = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => 'Room check started successfully',
        'rooms_checked' => $affected_rooms
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in start-room-check.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
