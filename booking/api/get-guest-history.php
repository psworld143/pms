<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest History API
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
    
    // Get guest history (reservations, feedback, etc.)
    $sql = "
        SELECT 
            'reservation' as type,
            r.id as record_id,
            r.check_in_date as date,
            r.status,
            r.total_amount as amount,
            rm.room_number,
            rt.name as room_type,
            NULL as rating,
            NULL as comments
        FROM reservations r
        LEFT JOIN rooms rm ON r.room_id = rm.id
        LEFT JOIN room_types rt ON rm.room_type_id = rt.id
        WHERE r.guest_id = ?
        
        UNION ALL
        
        SELECT 
            'feedback' as type,
            f.id as record_id,
            f.created_at as date,
            f.status,
            NULL as amount,
            NULL as room_number,
            NULL as room_type,
            f.rating,
            f.comments
        FROM feedback f
        LEFT JOIN reservations r ON f.reservation_id = r.id
        WHERE r.guest_id = ?
        
        ORDER BY date DESC
        LIMIT 50
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId, $guestId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedHistory = array_map(function($item) {
        return [
            'type' => $item['type'],
            'record_id' => $item['record_id'],
            'date' => $item['date'],
            'status' => $item['status'],
            'amount' => $item['amount'] ? (float)$item['amount'] : null,
            'room_number' => $item['room_number'],
            'room_type' => $item['room_type'],
            'rating' => $item['rating'] ? (int)$item['rating'] : null,
            'comments' => $item['comments']
        ];
    }, $history);
    
    echo json_encode([
        'success' => true,
        'history' => $formattedHistory
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest history: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
