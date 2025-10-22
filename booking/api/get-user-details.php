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

try {
    $user_id = $_GET['id'] ?? null;
    
    if (!$user_id) {
        throw new Exception('User ID is required');
    }
    
    $user = getUserDetails($user_id);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    error_log("Error getting user details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get detailed user information
 */
function getUserDetails($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, username, email, role, is_active, created_at, updated_at
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error getting user details: " . $e->getMessage());
        return null;
    }
}
?>
