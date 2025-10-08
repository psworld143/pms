<?php
require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
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
    $week = isset($_GET['week']) ? $_GET['week'] : date('Y-\WW');
    
    // Parse week parameter (format: YYYY-WW)
    $year = substr($week, 0, 4);
    $week_num = substr($week, 6, 2);

    // Get start and end dates for the week
    $start_date = date('Y-m-d', strtotime($year . 'W' . $week_num));
    $end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));

    // Get weekly revenue per day
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
    $weekly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get weekly totals and occupancy snapshot
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total_reservations,
                SUM(total_amount) AS total_revenue,
                SUM(tax_amount) AS total_taxes,
                SUM(discount_amount) AS total_discounts,
                AVG(total_amount) AS average_ticket
         FROM billing
         WHERE DATE(created_at) BETWEEN ? AND ?
           AND payment_status = 'paid'"
    );
    $stmt->execute([$start_date, $end_date]);
    $totals = $stmt->fetch() ?: [];

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS occupied_nights
         FROM reservations
         WHERE (check_in_date <= ? AND check_out_date >= ?) OR (check_in_date <= ? AND check_out_date >= ?)"
    );
    $stmt->execute([$end_date, $start_date, $end_date, $start_date]);
    $occupied_result = $stmt->fetch() ?: ['occupied_nights' => 0];

    $total_rooms_stmt = $pdo->query("SELECT COUNT(*) AS total_rooms FROM rooms WHERE status != 'maintenance'");
    $total_rooms = (int)($total_rooms_stmt->fetch()['total_rooms'] ?? 0);

    echo json_encode([
        'success' => true,
        'week' => $week,
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
        }, $weekly_data),
        'totals' => [
            'total_reservations' => (int)($totals['total_reservations'] ?? 0),
            'total_revenue' => (float)($totals['total_revenue'] ?? 0),
            'total_taxes' => (float)($totals['total_taxes'] ?? 0),
            'total_discounts' => (float)($totals['total_discounts'] ?? 0),
            'average_ticket' => (float)($totals['average_ticket'] ?? 0),
            'occupied_nights' => (int)$occupied_result['occupied_nights'],
            'total_rooms' => $total_rooms
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching weekly reports: ' . $e->getMessage()
    ]);
}
?>
