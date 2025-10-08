<?php
require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
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
    $days = isset($_GET['days']) ? max(1, (int)$_GET['days']) : 30;
    $segments = getRevenueBreakdown($days);

    echo json_encode([
        'success' => true,
        'days' => $days,
        'segments' => $segments
    ]);
} catch (Throwable $e) {
    error_log('Revenue breakdown error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load revenue breakdown.'
    ]);
}
