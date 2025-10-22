<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'], true)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => booking_base() . 'login.php'
    ]);
    exit();
}

header('Content-Type: application/json');

try {
    $status_filter = $_GET['status'] ?? '';
    $date_filter = $_GET['date'] ?? '';

    $bills = getBills($status_filter, $date_filter);

    $totals = [
        'count' => count($bills),
        'total_amount' => array_sum(array_map(function ($bill) {
            return (float)($bill['total_amount'] ?? 0);
        }, $bills))
    ];

    echo json_encode([
        'success' => true,
        'filters' => [
            'status' => $status_filter,
            'date' => $date_filter
        ],
        'totals' => $totals,
        'bills' => $bills
    ]);

} catch (Exception $e) {
    error_log('Error in get-bills.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading bills: ' . $e->getMessage()
    ]);
}
?>
