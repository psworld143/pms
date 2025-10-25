<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest Communication API
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
    
    // Get guest communication history
    $sql = "
        SELECT 
            c.id,
            c.message,
            c.type,
            c.direction,
            c.created_at,
            c.status,
            u.first_name as staff_first_name,
            u.last_name as staff_last_name
        FROM guest_communications c
        LEFT JOIN users u ON c.staff_id = u.id
        WHERE c.guest_id = ?
        ORDER BY c.created_at DESC
        LIMIT 20
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $communications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedCommunications = array_map(function($item) {
        return [
            'id' => $item['id'],
            'message' => $item['message'],
            'type' => $item['type'],
            'direction' => $item['direction'],
            'created_at' => $item['created_at'],
            'status' => $item['status'],
            'staff_name' => $item['staff_first_name'] && $item['staff_last_name'] 
                ? $item['staff_first_name'] . ' ' . $item['staff_last_name'] 
                : 'System'
        ];
    }, $communications);
    
    echo json_encode([
        'success' => true,
        'communications' => $formattedCommunications
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest communication: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
