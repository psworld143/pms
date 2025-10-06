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
    // Get occupancy data for the last 30 days
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as total_reservations,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_reservations,
            SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as checked_in_reservations
        FROM reservations 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    
    $occupancy_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total rooms count
    $stmt = $pdo->query("SELECT COUNT(*) as total_rooms FROM rooms WHERE status != 'maintenance'");
    $total_rooms = $stmt->fetch()['total_rooms'];
    
    // Calculate occupancy rates
    $processed_data = [];
    foreach ($occupancy_data as $row) {
        $occupancy_rate = $total_rooms > 0 ? round(($row['checked_in_reservations'] / $total_rooms) * 100, 1) : 0;
        $processed_data[] = [
            'date' => $row['date'],
            'occupancy_rate' => $occupancy_rate,
            'total_reservations' => (int)$row['total_reservations'],
            'confirmed_reservations' => (int)$row['confirmed_reservations'],
            'checked_in_reservations' => (int)$row['checked_in_reservations']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $processed_data,
        'total_rooms' => (int)$total_rooms
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching occupancy data: ' . $e->getMessage()
    ]);
}
?>
