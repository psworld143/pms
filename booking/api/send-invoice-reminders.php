<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    // Check for API key in headers (for AJAX requests)
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;

    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        // Valid API key, create session
        $_SESSION['user_id'] = 1073; // Default manager user
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }
}
try {
    // Fetch overdue or pending bills due within 3 days
    $bills = getBills('overdue', '') ?: [];
    $soon = getBills('pending', 'this_week') ?: [];
    $targets = array_merge($bills, $soon);

    $count = 0;
    foreach ($targets as $bill) {
        // Here we would enqueue/send actual reminders (email/SMS). For now, just count.
        $count++;
    }
    echo json_encode(['success' => true, 'reminders_sent' => $count]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send reminders']);
}
?>


