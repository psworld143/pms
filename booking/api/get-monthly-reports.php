<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../../includes/database.php';
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
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

    // Get start and end dates for the month
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));

    // Get monthly revenue by day
    $stmt = $pdo->prepare(
        "SELECT DATE(created_at) AS date,
                SUM(total_amount) AS daily_revenue,
                SUM(tax_amount) AS daily_taxes,
                SUM(discount_amount) AS daily_discounts,
                COUNT(*) AS transactions
         FROM billing
         WHERE DATE(created_at) BETWEEN ? AND ?
           AND payment_status = 'paid'
         GROUP BY DATE(created_at)
         ORDER BY date ASC"
    );
    $stmt->execute([$start_date, $end_date]);
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get monthly totals
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total_reservations,
                SUM(total_amount) AS total_revenue,
                SUM(tax_amount) AS total_taxes,
                SUM(discount_amount) AS total_discounts,
                AVG(total_amount) AS average_reservation_value
         FROM billing
         WHERE DATE(created_at) BETWEEN ? AND ?
           AND payment_status = 'paid'"
    );
    $stmt->execute([$start_date, $end_date]);
    $totals = $stmt->fetch() ?: [];

    // Get room type performance
    $stmt = $pdo->prepare(
        "SELECT rm.room_type,
                COUNT(*) AS reservations,
                SUM(b.total_amount) AS revenue,
                SUM(b.tax_amount) AS taxes,
                SUM(b.discount_amount) AS discounts
         FROM billing b
         JOIN reservations r ON b.reservation_id = r.id
         JOIN rooms rm ON r.room_id = rm.id
         WHERE DATE(b.created_at) BETWEEN ? AND ?
           AND b.payment_status = 'paid'
         GROUP BY rm.room_type
         ORDER BY revenue DESC"
    );
    $stmt->execute([$start_date, $end_date]);
    $room_type_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'month' => $month,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'data' => array_map(function ($row) {
            return [
                'date' => $row['date'],
                'revenue' => (float)$row['daily_revenue'],
                'taxes' => (float)$row['daily_taxes'],
                'discounts' => (float)$row['daily_discounts'],
                'transactions' => (int)$row['transactions']
            ];
        }, $monthly_data),
        'totals' => [
            'total_reservations' => (int)($totals['total_reservations'] ?? 0),
            'total_revenue' => (float)($totals['total_revenue'] ?? 0),
            'total_taxes' => (float)($totals['total_taxes'] ?? 0),
            'total_discounts' => (float)($totals['total_discounts'] ?? 0),
            'average_reservation_value' => (float)($totals['average_reservation_value'] ?? 0)
        ],
        'room_type_performance' => array_map(function ($row) {
            return [
                'room_type' => $row['room_type'],
                'reservations' => (int)$row['reservations'],
                'revenue' => (float)$row['revenue'],
                'taxes' => (float)$row['taxes'],
                'discounts' => (float)$row['discounts']
            ];
        }, $room_type_data)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching monthly reports: ' . $e->getMessage()
    ]);
}
?>
