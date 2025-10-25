<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Create Maintenance Request API
 */

session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and has access (manager or front_desk only)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'debug' => [
            'user_id' => $_SESSION['user_id'] ?? 'not_set',
            'user_role' => $_SESSION['user_role'] ?? 'not_set',
            'session_data' => $_SESSION
        ]
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
    // Check database connection
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    
    // Test database connection
    $pdo->query("SELECT 1");
    
    // Handle both FormData and JSON input
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
            exit();
        }
        
        $room_id = $input['room_id'] ?? null;
        $issue_type = $input['issue_type'] ?? null;
        $priority = $input['priority'] ?? 'normal';
        $description = $input['description'] ?? '';
        $estimated_cost = $input['estimated_cost'] ?? 0;
    } else {
        // Handle FormData input
        $room_id = $_POST['room_id'] ?? null;
        $issue_type = $_POST['issue_type'] ?? null;
        $priority = $_POST['priority'] ?? 'normal';
        $description = $_POST['description'] ?? '';
        $estimated_cost = $_POST['estimated_cost'] ?? 0;
    }
    
    if (!$room_id || !$issue_type || !$description) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields',
            'debug' => [
                'room_id' => $room_id,
                'issue_type' => $issue_type,
                'description' => $description,
                'priority' => $priority,
                'content_type' => $contentType
            ]
        ]);
        exit();
    }
    
    // Validate that the room exists
    $roomCheck = $pdo->prepare("SELECT id FROM rooms WHERE id = ?");
    $roomCheck->execute([$room_id]);
    if (!$roomCheck->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid room selected',
            'debug' => ['room_id' => $room_id]
        ]);
        exit();
    }
    
    // Create the maintenance request
    $stmt = $pdo->prepare("
        INSERT INTO maintenance_requests 
        (room_id, reported_by, issue_type, priority, description, estimated_cost, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'reported', NOW(), NOW())
    ");
    
    $result = $stmt->execute([
        $room_id,
        $_SESSION['user_id'],
        $issue_type,
        $priority,
        $description,
        $estimated_cost
    ]);
    
    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to execute database query',
            'debug' => [
                'error_info' => $stmt->errorInfo(),
                'room_id' => $room_id,
                'issue_type' => $issue_type,
                'priority' => $priority,
                'description' => $description,
                'estimated_cost' => $estimated_cost
            ]
        ]);
        exit();
    }
    
    if ($stmt->rowCount() > 0) {
        $request_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Maintenance request created successfully',
            'request_id' => $request_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create maintenance request'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Error creating maintenance request: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Error creating maintenance request: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'debug' => $e->getMessage()
    ]);
}
?>
