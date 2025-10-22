<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $type_filter = $_GET['type'] ?? '';
    
    // Get discounts with filters
    $discounts = getDiscounts($type_filter);
    
    echo json_encode([
        'success' => true,
        'discounts' => $discounts
    ]);
    
} catch (Exception $e) {
    error_log("Error in get-discounts.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading discounts: ' . $e->getMessage()
    ]);
}
?>
