<?php
/**
 * Get Guest Feedback API
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
    $guestId = $_GET['guest_id'] ?? null;
    
    if (!$guestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
        exit();
    }
    
    // Get guest feedback
    $sql = "
        SELECT 
            f.id,
            f.rating,
            f.comments,
            f.created_at,
            f.status,
            r.id as reservation_id,
            r.check_in_date,
            r.check_out_date
        FROM feedback f
        LEFT JOIN reservations r ON f.reservation_id = r.id
        WHERE r.guest_id = ?
        ORDER BY f.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedFeedback = array_map(function($item) {
        return [
            'id' => $item['id'],
            'rating' => (int)$item['rating'],
            'comments' => $item['comments'],
            'created_at' => $item['created_at'],
            'status' => $item['status'],
            'reservation_id' => $item['reservation_id'],
            'check_in_date' => $item['check_in_date'],
            'check_out_date' => $item['check_out_date']
        ];
    }, $feedback);
    
    echo json_encode([
        'success' => true,
        'feedback' => $formattedFeedback
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest feedback: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>