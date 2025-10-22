<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    if (empty($input['user_id'])) {
        throw new Exception('User ID is required');
    }
    
    $user_id = intval($input['user_id']);
    
    // Prevent deleting own account
    if ($user_id === $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }
    
    // Get user details for logging
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Check if user has created reservations or other data
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM reservations WHERE created_by = ?
        UNION ALL
        SELECT COUNT(*) as count FROM reservations WHERE checked_in_by = ?
        UNION ALL
        SELECT COUNT(*) as count FROM reservations WHERE checked_out_by = ?
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $results = $stmt->fetchAll();
    
    $total_records = array_sum(array_column($results, 'count'));
    
    if ($total_records > 0) {
        // Soft delete - deactivate user
        $stmt = $pdo->prepare("
            UPDATE users 
            SET is_active = FALSE,
                username = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', username),
                email = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', email),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        $action = 'deactivated';
    } else {
        // Hard delete if no related records
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $action = 'deleted';
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'user_deleted', "{$action} user: {$user['username']}");
    
    echo json_encode([
        'success' => true,
        'message' => "User {$action} successfully"
    ]);
    
} catch (Exception $e) {
    error_log("Error deleting user: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
