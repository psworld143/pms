<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest Statistics API
 * Hotel PMS - Guest Management Module
 */

session_start();
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get total guests
    $totalGuestsSql = "SELECT COUNT(*) as total FROM guests";
    $totalStmt = $pdo->prepare($totalGuestsSql);
    $totalStmt->execute();
    $totalGuests = $totalStmt->fetch()['total'];
    
    // Get VIP guests
    $vipGuestsSql = "SELECT COUNT(*) as total FROM guests WHERE is_vip = 1";
    $vipStmt = $pdo->prepare($vipGuestsSql);
    $vipStmt->execute();
    $vipGuests = $vipStmt->fetch()['total'];
    
    // Get regular guests
    $regularGuests = $totalGuests - $vipGuests;
    
    // Get guests with active reservations
    $activeGuestsSql = "
        SELECT COUNT(DISTINCT g.id) as total
        FROM guests g
        INNER JOIN reservations r ON g.id = r.guest_id
        WHERE r.status IN ('confirmed', 'checked_in')
    ";
    $activeStmt = $pdo->prepare($activeGuestsSql);
    $activeStmt->execute();
    $activeGuests = $activeStmt->fetch()['total'];
    
    // Get new guests this month
    $newGuestsSql = "
        SELECT COUNT(*) as total
        FROM guests
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ";
    $newStmt = $pdo->prepare($newGuestsSql);
    $newStmt->execute();
    $newGuests = $newStmt->fetch()['total'];
    
    // Get top spending guests
    $topSpendersSql = "
        SELECT 
            g.id,
            g.first_name,
            g.last_name,
            g.is_vip,
            COALESCE(SUM(r.total_amount), 0) as total_spent
        FROM guests g
        LEFT JOIN reservations r ON g.id = r.guest_id
        GROUP BY g.id
        ORDER BY total_spent DESC
        LIMIT 5
    ";
    $topSpendersStmt = $pdo->prepare($topSpendersSql);
    $topSpendersStmt->execute();
    $topSpenders = $topSpendersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get guest growth over last 6 months
    $growthSql = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM guests
        WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ";
    $growthStmt = $pdo->prepare($growthSql);
    $growthStmt->execute();
    $growthData = $growthStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_guests' => (int)$totalGuests,
            'vip_guests' => (int)$vipGuests,
            'regular_guests' => (int)$regularGuests,
            'active_guests' => (int)$activeGuests,
            'new_guests_this_month' => (int)$newGuests,
            'top_spenders' => $topSpenders,
            'growth_data' => $growthData
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest statistics: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
