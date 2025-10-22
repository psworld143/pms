<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Maintenance Request API
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

$request_id = $_GET['id'] ?? null;

if (!$request_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Request ID is required'
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            mr.*,
            r.room_number,
            u.name as reported_by_name
        FROM maintenance_requests mr
        LEFT JOIN rooms r ON mr.room_id = r.id
        LEFT JOIN users u ON mr.reported_by = u.id
        WHERE mr.id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode([
            'success' => false,
            'message' => 'Maintenance request not found'
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'request' => $request
    ]);
    
} catch (PDOException $e) {
    error_log('Error getting maintenance request: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error getting maintenance request: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
