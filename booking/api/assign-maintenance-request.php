<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Assign Maintenance Request API
 */

session_start();
require_once dirname(__DIR__, 2) . '/config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager'])) {
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
    
    $request_id = $input['request_id'] ?? null;
    $technician_id = $input['technician_id'] ?? null;
    
    if (!$request_id || !$technician_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Request ID and technician ID are required'
        ]);
        exit();
    }
    
    // Update the maintenance request
    $stmt = $pdo->prepare("
        UPDATE maintenance_requests 
        SET assigned_to = ?, status = 'assigned', updated_at = NOW()
        WHERE id = ? AND status = 'reported'
    ");
    
    $stmt->execute([$technician_id, $request_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Maintenance request assigned successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to assign maintenance request or request not found'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Error assigning maintenance request: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error assigning maintenance request: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
