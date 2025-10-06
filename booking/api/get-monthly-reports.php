<?php
session_start();
require_once '../../includes/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
    
    // Get start and end dates for the month
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    // Get monthly reservations by day
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as daily_reservations,
            SUM(total_amount) as daily_revenue
        FROM billing 
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND payment_status = 'paid'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$start_date, $end_date]);
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly totals
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reservations,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_reservation_value
        FROM billing 
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND payment_status = 'paid'
    ");
    $stmt->execute([$start_date, $end_date]);
    $totals = $stmt->fetch();
    
    // Get room type performance
    $stmt = $pdo->prepare("
        SELECT 
            rm.room_type,
            COUNT(*) as reservations,
            SUM(b.total_amount) as revenue
        FROM billing b
        JOIN reservations r ON b.reservation_id = r.id
        JOIN rooms rm ON r.room_id = rm.id
        WHERE DATE(b.created_at) BETWEEN ? AND ?
        AND b.payment_status = 'paid'
        GROUP BY rm.room_type
        ORDER BY revenue DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $room_type_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'month' => $month,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'data' => $monthly_data,
        'totals' => [
            'total_reservations' => (int)$totals['total_reservations'],
            'total_revenue' => (float)$totals['total_revenue'],
            'average_reservation_value' => (float)$totals['average_reservation_value']
        ],
        'room_type_performance' => $room_type_data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching monthly reports: ' . $e->getMessage()
    ]);
}
?>
