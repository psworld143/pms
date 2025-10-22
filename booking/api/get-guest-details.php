<?php
/**
 * Get Guest Details API
 * Hotel PMS - Guest Management Module
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $guestId = $_GET['id'] ?? null;
    
    if (!$guestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
        exit();
    }
    
    // Get guest details
    $sql = "
        SELECT 
            g.*,
            COUNT(r.id) as total_stays,
            MAX(r.check_out_date) as last_visit,
            COALESCE(SUM(r.total_amount), 0) as total_spent,
            AVG(r.total_amount) as average_spent
        FROM guests g
        LEFT JOIN reservations r ON g.id = r.guest_id
        WHERE g.id = ?
        GROUP BY g.id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$guest) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guest not found']);
        exit();
    }
    
    // Get recent reservations
    $reservationsSql = "
        SELECT 
            r.id,
            r.check_in_date,
            r.check_out_date,
            r.status,
            r.total_amount,
            rm.room_number,
            rm.room_type
        FROM reservations r
        LEFT JOIN rooms rm ON r.room_id = rm.id
        WHERE r.guest_id = ?
        ORDER BY r.check_in_date DESC
        LIMIT 10
    ";
    
    $reservationsStmt = $pdo->prepare($reservationsSql);
    $reservationsStmt->execute([$guestId]);
    $reservations = $reservationsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedGuest = [
        'id' => $guest['id'],
        'first_name' => $guest['first_name'],
        'last_name' => $guest['last_name'],
        'email' => $guest['email'],
        'phone' => $guest['phone'],
        'is_vip' => (bool)$guest['is_vip'],
        'id_number' => $guest['id_number'],
        'address' => $guest['address'],
        'date_of_birth' => $guest['date_of_birth'],
        'nationality' => $guest['nationality'],
        'preferences' => $guest['preferences'],
        'service_notes' => $guest['service_notes'],
        'created_at' => $guest['created_at'],
        'total_stays' => (int)$guest['total_stays'],
        'last_visit' => $guest['last_visit'],
        'total_spent' => (float)$guest['total_spent'],
        'average_spent' => (float)$guest['average_spent'],
        'recent_reservations' => $reservations
    ];
    
    echo json_encode([
        'success' => true,
        'guest' => $formattedGuest
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>