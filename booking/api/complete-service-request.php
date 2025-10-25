<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Complete Service Request API
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $request_id = $input['id'] ?? null;
    
    if (!$request_id) {
        throw new Exception('Request ID is required');
    }
    
    // Update the service request status to completed
    $stmt = $pdo->prepare("
        UPDATE maintenance_requests 
        SET status = 'completed',
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$request_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Service request not found');
    }
    
    // Log the activity
    logActivity($_SESSION['user_id'], 'service_request_completed', "Completed service request #{$request_id}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Service request completed successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error completing service request: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>