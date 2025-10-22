<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Service Request Details API
 */

session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and has access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager', 'housekeeping'])) {
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

try {
    $request_id = $_GET['id'] ?? null;
    
    if (!$request_id) {
        throw new Exception('Request ID is required');
    }
    
    $stmt = $pdo->prepare("
        SELECT mr.*, 
               r.room_number,
               CONCAT(u1.name, ' (', u1.role, ')') as reported_by_name,
               u2.name as assigned_to_name,
               CONCAT(g.first_name, ' ', g.last_name) as guest_name,
               g.phone as guest_phone
        FROM maintenance_requests mr
        LEFT JOIN rooms r ON mr.room_id = r.id
        LEFT JOIN users u1 ON mr.reported_by = u1.id
        LEFT JOIN users u2 ON mr.assigned_to = u2.id
        LEFT JOIN reservations res ON r.id = res.room_id AND res.status IN ('checked_in', 'confirmed')
        LEFT JOIN guests g ON res.guest_id = g.id
        WHERE mr.id = ?
    ");
    
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        throw new Exception('Service request not found');
    }
    
    echo json_encode([
        'success' => true,
        'request' => $request
    ]);
    
} catch (Exception $e) {
    error_log("Error getting service request: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>