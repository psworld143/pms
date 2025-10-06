<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Only managers can assign
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) { throw new Exception('Invalid payload'); }

    $task_id = (int)($input['task_id'] ?? 0);
    $user_id = (int)($input['user_id'] ?? 0);

    if ($task_id <= 0 || $user_id <= 0) { throw new Exception('Task and user are required'); }

    // Verify task exists and is not completed
    $stmt = $pdo->prepare("SELECT id, status FROM housekeeping_tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    if (!$task) { throw new Exception('Task not found'); }
    if ($task['status'] === 'completed' || $task['status'] === 'verified') {
        throw new Exception('Task is already completed');
    }

    // Verify user is housekeeping
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'housekeeping' AND is_active = 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) { throw new Exception('Invalid housekeeping user'); }

    // Assign task
    $stmt = $pdo->prepare("UPDATE housekeeping_tasks SET assigned_to = ?, status = 'in_progress', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$user_id, $task_id]);

    // Create notification to the assigned user (if notifications table supports title column)
    try {
        $test = $pdo->query("SHOW COLUMNS FROM notifications LIKE 'title'");
        $hasTitle = $test && $test->rowCount() > 0;
    } catch (Exception $e) { $hasTitle = false; }

    if ($hasTitle) {
        $ins = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, 'New Task Assigned', CONCAT('You have been assigned to housekeeping task #', ?), 'info', NOW())");
        $ins->execute([$user_id, $task_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, CONCAT('New task assigned #', ?), 'info', NOW())");
        $ins->execute([$user_id, $task_id]);
    }

    // Log activity
    logActivity($_SESSION['user_id'], 'task_assigned', "Assigned housekeeping task #{$task_id} to user {$user_id}");

    echo json_encode(['success' => true, 'message' => 'Task assigned successfully']);
} catch (Exception $e) {
    error_log('assign-housekeeping-task error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
