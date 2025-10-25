<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Recent Housekeeping Tasks API
 * Returns recent housekeeping tasks with room and user information
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and has access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk', 'housekeeping'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit(); }
try {
    // Get filter parameters
    $status = $_GET['status'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20);
    $assigned_only = $_GET['assigned_only'] ?? false;
    
    // Build the query
    $where_conditions = ["1=1"];
    $params = [];
    
    // Status filter
    if (!empty($status)) {
        $where_conditions[] = "ht.status = ?";
        $params[] = $status; }
    // Assigned only filter
    if ($assigned_only) {
        $where_conditions[] = "ht.assigned_to IS NOT NULL";
    }
    $where_clause = implode(" AND ", $where_conditions);
    
    $sql = "
        SELECT 
            ht.id,
            ht.room_id,
            ht.task_type,
            ht.status,
            ht.assigned_to,
            ht.scheduled_time,
            ht.completed_time,
            ht.notes,
            ht.created_at,
            ht.updated_at,
            r.room_number,
            u.name as assigned_to_name,
            creator.name as created_by_name
        FROM housekeeping_tasks ht
        LEFT JOIN rooms r ON ht.room_id = r.id
        LEFT JOIN users u ON ht.assigned_to = u.id
        LEFT JOIN users creator ON ht.created_by = creator.id
        WHERE {$where_clause}
        ORDER BY ht.created_at DESC
        LIMIT " . (int)$limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formatted_tasks = [];
    foreach ($tasks as $task) {
        $formatted_tasks[] = [
            'id' => (int)$task['id'],
            'room_id' => (int)$task['room_id'],
            'room_number' => $task['room_number'],
            'task_type' => $task['task_type'],
            'status' => $task['status'],
            'assigned_to' => $task['assigned_to'] ? (int)$task['assigned_to'] : null,
            'assigned_to_name' => $task['assigned_to_name'],
            'scheduled_time' => $task['scheduled_time'],
            'completed_time' => $task['completed_time'],
            'notes' => $task['notes'],
            'created_by_name' => $task['created_by_name'],
            'created_at' => $task['created_at'],
            'updated_at' => $task['updated_at'],
            'is_unassigned' => empty($task['assigned_to'])
        ]; }
    echo json_encode([
        'success' => true,
        'tasks' => $formatted_tasks,
        'count' => count($formatted_tasks),
        'unassigned_count' => count(array_filter($formatted_tasks, function($task) {
            return $task['is_unassigned'];
        }))
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching housekeeping tasks: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error fetching housekeeping tasks: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]); }
?>
