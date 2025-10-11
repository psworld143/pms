<?php
/**
 * Create Housekeeping Task API
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json');

// Debug session info
error_log('Session debug - user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
error_log('Session debug - user_role: ' . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET'));

// Check if user is logged in and has access (manager or front_desk only)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access - Session: ' . (isset($_SESSION['user_id']) ? 'user_id=' . $_SESSION['user_id'] . ', role=' . $_SESSION['user_role'] : 'no session')
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
    // Handle both JSON and form data
    $input = null;
    
    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        $input = $_POST;
    }
    
    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data received'
        ]);
        exit();
    }
    
    $room_id = $input['room_id'] ?? null;
    $task_type = $input['task_type'] ?? null;
    $assigned_to = $input['assigned_to'] ?? null;
    $scheduled_time = $input['scheduled_time'] ?? null;
    $notes = $input['notes'] ?? '';
    $created_by = $_SESSION['user_id'] ?? null;
    
    if (!$room_id || !$task_type || !$assigned_to || !$scheduled_time || !$created_by) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: room_id, task_type, assigned_to, scheduled_time, and created_by are required'
        ]);
        exit();
    }
    
    // Validate task_type against allowed values
    $allowed_task_types = ['daily_cleaning', 'turn_down', 'deep_cleaning', 'maintenance', 'inspection'];
    if (!in_array($task_type, $allowed_task_types)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid task type. Allowed types: ' . implode(', ', $allowed_task_types)
        ]);
        exit();
    }
    
    // Create the task
    $stmt = $pdo->prepare("
        INSERT INTO housekeeping_tasks 
        (room_id, task_type, assigned_to, scheduled_time, notes, status, created_by, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $room_id,
        $task_type,
        $assigned_to,
        $scheduled_time,
        $notes,
        $created_by
    ]);
    
    if ($stmt->rowCount() > 0) {
        $task_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully',
            'task_id' => $task_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create task'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Error creating housekeeping task: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error creating housekeeping task: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
