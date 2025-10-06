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
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Get daily reservations
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            CONCAT(g.first_name, ' ', g.last_name) as guest_name,
            g.email as guest_email,
            g.phone as guest_phone,
            rm.room_number,
            rm.room_type
        FROM reservations r
        JOIN guests g ON r.guest_id = g.id
        JOIN rooms rm ON r.room_id = rm.id
        WHERE DATE(r.created_at) = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$date]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get daily check-ins (from reservations with checked_in status)
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            CONCAT(g.first_name, ' ', g.last_name) as guest_name,
            rm.room_number
        FROM reservations r
        JOIN guests g ON r.guest_id = g.id
        JOIN rooms rm ON r.room_id = rm.id
        WHERE DATE(r.checked_in_at) = ? AND r.status = 'checked_in'
        ORDER BY r.checked_in_at DESC
    ");
    $stmt->execute([$date]);
    $check_ins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get daily check-outs (from reservations with checked_out status)
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            CONCAT(g.first_name, ' ', g.last_name) as guest_name,
            rm.room_number
        FROM reservations r
        JOIN guests g ON r.guest_id = g.id
        JOIN rooms rm ON r.room_id = rm.id
        WHERE DATE(r.checked_out_at) = ? AND r.status = 'checked_out'
        ORDER BY r.checked_out_at DESC
    ");
    $stmt->execute([$date]);
    $check_outs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get daily revenue
    $stmt = $pdo->prepare("
        SELECT 
            SUM(total_amount) as daily_revenue,
            COUNT(*) as total_transactions
        FROM billing 
        WHERE DATE(created_at) = ? AND payment_status = 'paid'
    ");
    $stmt->execute([$date]);
    $revenue_data = $stmt->fetch();
    
    // Get room occupancy for the day
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as occupied_rooms
        FROM reservations 
        WHERE ? BETWEEN check_in_date AND check_out_date 
        AND status IN ('confirmed', 'checked_in')
    ");
    $stmt->execute([$date]);
    $occupancy_data = $stmt->fetch();
    
    // Get total rooms
    $stmt = $pdo->query("SELECT COUNT(*) as total_rooms FROM rooms WHERE status != 'maintenance'");
    $total_rooms = $stmt->fetch()['total_rooms'];
    
    $occupancy_rate = $total_rooms > 0 ? round(($occupancy_data['occupied_rooms'] / $total_rooms) * 100, 1) : 0;
    
    echo json_encode([
        'success' => true,
        'date' => $date,
        'summary' => [
            'total_reservations' => count($reservations),
            'check_ins' => count($check_ins),
            'check_outs' => count($check_outs),
            'daily_revenue' => (float)$revenue_data['daily_revenue'],
            'total_transactions' => (int)$revenue_data['total_transactions'],
            'occupied_rooms' => (int)$occupancy_data['occupied_rooms'],
            'total_rooms' => (int)$total_rooms,
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
