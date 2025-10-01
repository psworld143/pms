<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get revenue data for the last 30 days
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            SUM(total_amount) as daily_revenue,
            COUNT(*) as total_transactions
        FROM billing 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND payment_status = 'paid'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    
    $revenue_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly totals
    $stmt = $pdo->query("
        SELECT 
            SUM(total_amount) as monthly_revenue,
            COUNT(*) as monthly_transactions
        FROM billing 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND payment_status = 'paid'
    ");
    
    $monthly_totals = $stmt->fetch();
    
    // Process data
    $processed_data = [];
    foreach ($revenue_data as $row) {
        $processed_data[] = [
            'date' => $row['date'],
            'revenue' => (float)$row['daily_revenue'],
            'transactions' => (int)$row['total_transactions']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $processed_data,
        'monthly_total' => (float)$monthly_totals['monthly_revenue'],
        'monthly_transactions' => (int)$monthly_totals['monthly_transactions']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching revenue data: ' . $e->getMessage()
    ]);
}
?>
