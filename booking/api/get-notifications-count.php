<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $count = getUnreadNotificationsCount($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
} catch (Exception $e) {
    error_log("Error getting notifications count: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving notifications count'
    ]);
}
?>
