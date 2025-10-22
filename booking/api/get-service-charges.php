<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
require_once '../includes/session-config.php';
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $refererPath = $referer ? parse_url($referer, PHP_URL_PATH) : '';
    if (!$refererPath || strpos($refererPath, '/booking/') === false) {
        error_log("Service Charges API - Unauthorized access attempt from: " . $referer);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $date_filter = $_GET['date'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    
    $charges = getServiceCharges($date_filter, $status_filter);
    
    echo json_encode([
        'success' => true,
        'charges' => $charges
    ]);
    
} catch (Exception $e) {
    error_log("Error getting service charges: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
