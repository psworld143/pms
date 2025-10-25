<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest Alerts API
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
    
    // Get guest alerts
    $sql = "
        SELECT 
            id,
            alert_type,
            message,
            priority,
            status,
            created_at,
            resolved_at
        FROM guest_alerts
        WHERE guest_id = ?
        ORDER BY created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedAlerts = array_map(function($item) {
        return [
            'id' => $item['id'],
            'alert_type' => $item['alert_type'],
            'message' => $item['message'],
            'priority' => $item['priority'],
            'status' => $item['status'],
            'created_at' => $item['created_at'],
            'resolved_at' => $item['resolved_at']
        ];
    }, $alerts);
    
    echo json_encode([
        'success' => true,
        'alerts' => $formattedAlerts
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest alerts: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
