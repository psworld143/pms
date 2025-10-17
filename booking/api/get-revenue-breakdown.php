<?php
/**
 * Revenue Breakdown API Endpoint
 * Returns revenue breakdown by segments for analytics dashboard
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

try {
// TEMPORARY: Bypass authentication for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'manager';
    $_SESSION['name'] = 'David Johnson';
}

// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => '../../login.php'
    ]);
    exit();
}

    // Get days from query parameter
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
    
    // Get revenue breakdown by room type
    $stmt = $pdo->prepare("
        SELECT 
            r.room_type as segment,
            COUNT(DISTINCT res.id) as reservations,
            AVG(r.rate) as adr,
            SUM(b.total_amount) as revenue,
            COUNT(DISTINCT res.id) as room_nights,
            ROUND((COUNT(DISTINCT res.id) / (SELECT COUNT(*) FROM rooms)) * 100, 2) as occupancy_pct,
            ROUND((SUM(b.total_amount) / COUNT(DISTINCT res.id)), 2) as revpar,
            ROUND((SUM(b.total_amount) / (SELECT SUM(total_amount) FROM bills WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY))) * 100, 2) as contribution_pct
        FROM rooms r
        LEFT JOIN reservations res ON r.id = res.room_id 
            AND res.check_in_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            AND res.status IN ('confirmed', 'checked_in')
        LEFT JOIN bills b ON res.id = b.reservation_id 
            AND b.status = 'paid'
            AND b.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY r.room_type
        HAVING revenue > 0
        ORDER BY revenue DESC
    ");
    $stmt->execute([$days, $days, $days]);
    $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'segments' => $breakdown
    ]);

} catch (Exception $e) {
    error_log('Revenue Breakdown API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading revenue breakdown: ' . $e->getMessage()
    ]);
}
?>