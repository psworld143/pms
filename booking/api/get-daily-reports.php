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
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // Daily reservations
    $stmt = $pdo->prepare(
        "SELECT r.*, CONCAT(g.first_name, ' ', g.last_name) AS guest_name, g.email AS guest_email, g.phone AS guest_phone, rm.room_number, rm.room_type
         FROM reservations r
         JOIN guests g ON r.guest_id = g.id
         JOIN rooms rm ON r.room_id = rm.id
         WHERE DATE(r.created_at) = ?
         ORDER BY r.created_at DESC"
    );
    $stmt->execute([$date]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Daily check-ins
    $stmt = $pdo->prepare(
        "SELECT r.*, CONCAT(g.first_name, ' ', g.last_name) AS guest_name, rm.room_number
         FROM reservations r
         JOIN guests g ON r.guest_id = g.id
         JOIN rooms rm ON r.room_id = rm.id
         WHERE DATE(r.checked_in_at) = ? AND r.status = 'checked_in'
         ORDER BY r.checked_in_at DESC"
    );
    $stmt->execute([$date]);
    $check_ins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Daily check-outs
    $stmt = $pdo->prepare(
        "SELECT r.*, CONCAT(g.first_name, ' ', g.last_name) AS guest_name, rm.room_number
         FROM reservations r
         JOIN guests g ON r.guest_id = g.id
         JOIN rooms rm ON r.room_id = rm.id
         WHERE DATE(r.checked_out_at) = ? AND r.status = 'checked_out'
         ORDER BY r.checked_out_at DESC"
    );
    $stmt->execute([$date]);
    $check_outs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Daily revenue details
    $stmt = $pdo->prepare(
        "SELECT
             COALESCE(SUM(total_amount), 0) AS daily_revenue,
             COALESCE(SUM(tax_amount), 0) AS daily_taxes,
             COALESCE(SUM(discount_amount), 0) AS daily_discounts,
             COUNT(*) AS total_transactions
         FROM billing
         WHERE DATE(created_at) = ? AND payment_status = 'paid'"
    );
    $stmt->execute([$date]);
    $revenue_data = $stmt->fetch() ?: ['daily_revenue' => 0, 'daily_taxes' => 0, 'daily_discounts' => 0, 'total_transactions' => 0];

    // Occupancy counts
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS occupied_rooms
         FROM reservations
         WHERE ? BETWEEN check_in_date AND check_out_date
           AND status IN ('confirmed', 'checked_in')"
    );
    $stmt->execute([$date]);
    $occupancy_data = $stmt->fetch() ?: ['occupied_rooms' => 0];

    $stmt = $pdo->query("SELECT COUNT(*) AS total_rooms FROM rooms WHERE status != 'maintenance'");
    $total_rooms = (int)($stmt->fetch()['total_rooms'] ?? 0);
    $occupied_rooms = (int)($occupancy_data['occupied_rooms'] ?? 0);
    $occupancy_rate = $total_rooms > 0 ? round(($occupied_rooms / $total_rooms) * 100, 1) : 0;

    echo json_encode([
        'success' => true,
        'date' => $date,
        'summary' => [
            'total_reservations' => count($reservations),
            'check_ins' => count($check_ins),
            'check_outs' => count($check_outs),
            'daily_revenue' => (float)$revenue_data['daily_revenue'],
            'daily_taxes' => (float)$revenue_data['daily_taxes'],
            'daily_discounts' => (float)$revenue_data['daily_discounts'],
            'total_transactions' => (int)$revenue_data['total_transactions'],
            'occupied_rooms' => $occupied_rooms,
            'total_rooms' => $total_rooms,
            'occupancy_rate' => $occupancy_rate
        ],
        'reservations' => $reservations,
        'check_ins' => $check_ins,
        'check_outs' => $check_outs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching daily reports: ' . $e->getMessage()
    ]);
}
?>
