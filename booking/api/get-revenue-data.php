<?php
/**
 * Get Revenue Data API
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and has access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    // Get revenue data for the last 30 days
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            SUM(total_amount) as daily_revenue,
            COUNT(*) as transaction_count
        FROM bills 
        WHERE status = 'paid' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly revenue breakdown
    $stmt = $pdo->query("
        SELECT 
            MONTH(created_at) as month,
            YEAR(created_at) as year,
            SUM(total_amount) as monthly_revenue,
            COUNT(*) as transaction_count
        FROM bills 
        WHERE status = 'paid' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY year ASC, month ASC
    ");
    $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get revenue by source
    $stmt = $pdo->query("
        SELECT 
            'Room Revenue' as source,
            SUM(CASE WHEN bill_type = 'room' THEN total_amount ELSE 0 END) as amount
        FROM bills 
        WHERE status = 'paid' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        UNION ALL
        SELECT 
            'Service Revenue' as source,
            SUM(CASE WHEN bill_type = 'service' THEN total_amount ELSE 0 END) as amount
        FROM bills 
        WHERE status = 'paid' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        UNION ALL
        SELECT 
            'Other Revenue' as source,
            SUM(CASE WHEN bill_type NOT IN ('room', 'service') THEN total_amount ELSE 0 END) as amount
        FROM bills 
        WHERE status = 'paid' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $revenueBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'daily' => $revenueData,
            'monthly' => $monthlyData,
            'breakdown' => $revenueBreakdown
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Error getting revenue data: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error getting revenue data: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>