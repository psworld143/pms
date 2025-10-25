<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Assign Housekeeping Task API
 * Assigns a housekeeping task to a specific user
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit(); }
// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit(); }
try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    $task_id = $input['task_id'] ?? null;
    $user_id = $input['user_id'] ?? null;
    
    // Validate required fields
    if (!$task_id || !$user_id) {
        throw new Exception('Missing required fields: task_id and user_id');
    }
    // Validate task exists and is not already assigned
    $stmt = $pdo->prepare("
        SELECT ht.*, r.room_number, u.name as assigned_to_name
        FROM housekeeping_tasks ht
        LEFT JOIN rooms r ON ht.room_id = r.id
        LEFT JOIN users u ON ht.assigned_to = u.id
        WHERE ht.id = ?
    ");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        throw new Exception('Task not found');
    }
    if ($task['assigned_to'] && $task['assigned_to'] != $user_id) {
        throw new Exception('Task is already assigned to another user');
    }
    // Validate user exists and has housekeeping role
    $stmt = $pdo->prepare("
        SELECT id, name, role, is_active
        FROM users
        WHERE id = ? AND role = 'housekeeping' AND is_active = 1
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found or not authorized for housekeeping tasks');
    }
    // Update the task assignment
    $stmt = $pdo->prepare("
        UPDATE housekeeping_tasks 
        SET assigned_to = ?, 
            status = CASE 
                WHEN status = 'pending' THEN 'in_progress'
                ELSE status
            END,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$user_id, $task_id]);
    
    if ($stmt->rowCount() > 0) {
        // Log the activity
        if (function_exists('logActivity')) {
            logActivity($_SESSION['user_id'], 'task_assigned', "Assigned housekeeping task #{$task_id} (Room {$task['room_number']}) to {$user['name']}"); }
        echo json_encode([
            'success' => true,
            'message' => "Task #{$task_id} assigned to {$user['name']} successfully",
            'task_id' => (int)$task_id,
            'user_id' => (int)$user_id,
            'user_name' => $user['name'],
            'room_number' => $task['room_number']
        ]);
    } else {
        throw new Exception('Failed to assign task');
    }
} catch (PDOException $e) {
    error_log("Error assigning housekeeping task: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error assigning housekeeping task: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]); }
?>





