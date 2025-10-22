<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest Reservations API
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
    $guestId = $_GET['guest_id'] ?? null;
    
    if (!$guestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
        exit();
    }
    
    // Get guest reservations
    $sql = "
        SELECT 
            r.id,
            r.check_in_date,
            r.check_out_date,
            r.status,
            r.total_amount,
            r.created_at,
            rm.room_number,
            rt.name as room_type,
            rt.rate as room_rate
        FROM reservations r
        LEFT JOIN rooms rm ON r.room_id = rm.id
        LEFT JOIN room_types rt ON rm.room_type_id = rt.id
        WHERE r.guest_id = ?
        ORDER BY r.check_in_date DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedReservations = array_map(function($reservation) {
        return [
            'id' => $reservation['id'],
            'check_in_date' => $reservation['check_in_date'],
            'check_out_date' => $reservation['check_out_date'],
            'status' => $reservation['status'],
            'total_amount' => (float)$reservation['total_amount'],
            'created_at' => $reservation['created_at'],
            'room_number' => $reservation['room_number'],
            'room_type' => $reservation['room_type'],
            'room_rate' => (float)$reservation['room_rate']
        ];
    }, $reservations);
    
    echo json_encode([
        'success' => true,
        'reservations' => $formattedReservations
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest reservations: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
