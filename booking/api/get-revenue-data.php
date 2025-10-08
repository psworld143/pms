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
    // Get revenue data for the last 30 days
    $stmt = $pdo->query(
        "SELECT 
            DATE(created_at) AS date,
            SUM(total_amount) AS daily_revenue,
            COUNT(*) AS total_transactions,
            SUM(tax_amount) AS daily_tax,
            SUM(discount_amount) AS daily_discount
        FROM billing
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          AND payment_status = 'paid'
        GROUP BY DATE(created_at)
        ORDER BY date ASC"
    );
    
    $revenue_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly totals
    $stmt = $pdo->query(
        "SELECT 
            SUM(total_amount) AS monthly_revenue,
            SUM(tax_amount) AS monthly_taxes,
            SUM(discount_amount) AS monthly_discounts,
            COUNT(*) AS monthly_transactions
        FROM billing
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          AND payment_status = 'paid'"
    );
    
    $monthly_totals = $stmt->fetch();
    
    // Process data
    $processed_data = [];
    foreach ($revenue_data as $row) {
        $processed_data[] = [
            'date' => $row['date'],
            'revenue' => (float)$row['daily_revenue'],
            'transactions' => (int)$row['total_transactions'],
            'taxes' => (float)$row['daily_tax'],
            'discounts' => (float)$row['daily_discount']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $processed_data,
        'monthly_total' => (float)$monthly_totals['monthly_revenue'],
        'monthly_taxes' => (float)$monthly_totals['monthly_taxes'],
        'monthly_discounts' => (float)$monthly_totals['monthly_discounts'],
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
