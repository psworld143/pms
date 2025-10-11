<?php
/**
 * Get Occupancy Data API
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
    // Get occupancy data for the last 30 days
    $stmt = $pdo->query("
        SELECT 
            DATE(check_in_date) as date,
            COUNT(*) as occupied_rooms,
            (SELECT COUNT(*) FROM rooms) as total_rooms,
            ROUND((COUNT(*) / (SELECT COUNT(*) FROM rooms)) * 100, 2) as occupancy_rate
        FROM reservations 
        WHERE check_in_date <= CURDATE() 
        AND check_out_date >= CURDATE()
        AND status IN ('confirmed', 'checked_in')
        AND check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(check_in_date)
        ORDER BY date ASC
    ");
    $occupancyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get occupancy by room type
    $stmt = $pdo->query("
        SELECT 
            r.room_type,
            COUNT(r.id) as total_rooms,
            COUNT(CASE WHEN res.id IS NOT NULL THEN 1 END) as occupied_rooms,
            ROUND((COUNT(CASE WHEN res.id IS NOT NULL THEN 1 END) / COUNT(r.id)) * 100, 2) as occupancy_rate
        FROM rooms r
        LEFT JOIN reservations res ON r.id = res.room_id 
            AND res.check_in_date <= CURDATE() 
            AND res.check_out_date >= CURDATE()
            AND res.status IN ('confirmed', 'checked_in')
        GROUP BY r.room_type
        ORDER BY occupancy_rate DESC
    ");
    $occupancyByType = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly occupancy trend
    $stmt = $pdo->query("
        SELECT 
            MONTH(check_in_date) as month,
            YEAR(check_in_date) as year,
            AVG(occupancy_rate) as avg_occupancy_rate
        FROM (
            SELECT 
                check_in_date,
                ROUND((COUNT(*) / (SELECT COUNT(*) FROM rooms)) * 100, 2) as occupancy_rate
            FROM reservations 
            WHERE status IN ('confirmed', 'checked_in')
            AND check_in_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE(check_in_date)
        ) as daily_occupancy
        GROUP BY YEAR(check_in_date), MONTH(check_in_date)
        ORDER BY year ASC, month ASC
    ");
    $monthlyOccupancy = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'daily' => $occupancyData,
            'by_type' => $occupancyByType,
            'monthly' => $monthlyOccupancy
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Error getting occupancy data: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error getting occupancy data: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>