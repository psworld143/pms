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
    $week = isset($_GET['week']) ? $_GET['week'] : date('Y-\WW');
    
    // Parse week parameter (format: YYYY-WW)
    $year = substr($week, 0, 4);
    $week_num = substr($week, 6, 2);
    
    // Get start and end dates for the week
    $start_date = date('Y-m-d', strtotime($year . 'W' . $week_num));
    $end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));
    
    // Get weekly reservations
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
    $weekly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get weekly totals
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reservations,
            SUM(total_amount) as total_revenue
        FROM billing 
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND payment_status = 'paid'
    ");
    $stmt->execute([$start_date, $end_date]);
    $totals = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'week' => $week,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'data' => $weekly_data,
        'totals' => [
            'total_reservations' => (int)$totals['total_reservations'],
            'total_revenue' => (float)$totals['total_revenue']
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
