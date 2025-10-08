<?php
require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
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
    $method_filter = $_GET['method'] ?? '';
    $date_filter = $_GET['date'] ?? '';

    $payments = getPayments($method_filter, $date_filter);

    $totals = [
        'count' => count($payments),
        'total_amount' => array_sum(array_map(function ($payment) {
            return (float)($payment['amount'] ?? 0);
        }, $payments))
    ];

    echo json_encode([
        'success' => true,
        'filters' => [
            'method' => $method_filter,
            'date' => $date_filter
        ],
        'totals' => $totals,
        'payments' => $payments
    ]);

} catch (Exception $e) {
    error_log('Error in get-payments.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading payments: ' . $e->getMessage()
    ]);
}
?>
