<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Task Details API
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

$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Task ID is required'
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            ht.*,
            r.room_number,
            u.name as staff_name
        FROM housekeeping_tasks ht
        LEFT JOIN rooms r ON ht.room_id = r.id
        LEFT JOIN users u ON ht.assigned_to = u.id
        WHERE ht.id = ?
    ");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        echo json_encode([
            'success' => false,
            'message' => 'Task not found'
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'task' => $task
    ]);
    
} catch (PDOException $e) {
    error_log('Error getting task details: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error getting task details: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
