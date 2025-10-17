<?php
require_once '../includes/session-config.php';
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $refererPath = $referer ? parse_url($referer, PHP_URL_PATH) : '';
    if (!$refererPath || strpos($refererPath, '/booking/') === false) {
        error_log("Service Requests API - Unauthorized access attempt from: " . $referer);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $status_filter = $_GET['status'] ?? '';
    $type_filter = $_GET['type'] ?? '';
    
    $requests = getServiceRequests($status_filter, $type_filter);
    
    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);
    
} catch (Exception $e) {
    error_log("Error getting service requests: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
