<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';

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
        echo 'Unauthorized';
        exit();
    }
}
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

$bills = getBills($status_filter, $date_filter);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=invoices_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Invoice #', 'Guest', 'Room', 'Amount', 'Date', 'Due Date', 'Status']);

foreach ($bills as $bill) {
    fputcsv($output, [
        $bill['bill_number'] ?? '',
        $bill['guest_name'] ?? '',
        $bill['room_number'] ?? '',
        number_format((float)($bill['total_amount'] ?? 0), 2, '.', ''),
        $bill['bill_date'] ?? '',
        $bill['due_date'] ?? '',
        $bill['status'] ?? ''
    ]); }
fclose($output);
exit();
?>


