<?php
/**
 * Get Pending Supply Requests
 * Returns pending supply requests for managers to review
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Debug session data
error_log("Session data in get-pending-requests.php: " . print_r($_SESSION, true));

// Check if user is logged in and has manager role
if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session. Session data: " . print_r($_SESSION, true));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated', 'debug' => 'No user_id in session']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    error_log("User role is not manager. Role: " . $user_role . ", Session data: " . print_r($_SESSION, true));
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Manager role required', 'debug' => 'User role: ' . $user_role]);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // First, check if the new columns exist
    $check_columns = $pdo->query("SHOW COLUMNS FROM supply_requests LIKE 'room_number'");
    $has_room_number = $check_columns->rowCount() > 0;
    
    $check_columns = $pdo->query("SHOW COLUMNS FROM supply_requests LIKE 'reason'");
    $has_reason = $check_columns->rowCount() > 0;
    
    // Build query based on available columns
    if ($has_room_number && $has_reason) {
        // New schema with room_number and reason columns
        $stmt = $pdo->prepare("
            SELECT 
                sr.id,
                sr.item_id,
                sr.quantity_requested,
                sr.room_number,
                sr.reason,
                sr.notes,
                sr.requested_by,
                sr.status,
                sr.created_at,
                ii.item_name,
                ii.unit,
                u.username as requested_by_name,
                u.name as requested_by_full_name
            FROM supply_requests sr
            JOIN inventory_items ii ON sr.item_id = ii.id
            JOIN users u ON sr.requested_by = u.id
            WHERE sr.status = 'pending'
            ORDER BY sr.created_at ASC
        ");
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get request statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_pending,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_requests,
                COUNT(CASE WHEN reason = 'missing' THEN 1 END) as missing_requests,
                COUNT(CASE WHEN reason = 'damaged' THEN 1 END) as damaged_requests
            FROM supply_requests 
            WHERE status = 'pending'
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Old schema without room_number and reason columns
        $stmt = $pdo->prepare("
            SELECT 
                sr.id,
                sr.item_id,
                sr.quantity_requested,
                'N/A' as room_number,
                'general' as reason,
                sr.notes,
                sr.requested_by,
                sr.status,
                sr.created_at,
                ii.item_name,
                ii.unit,
                u.username as requested_by_name,
                u.name as requested_by_full_name
            FROM supply_requests sr
            JOIN inventory_items ii ON sr.item_id = ii.id
            JOIN users u ON sr.requested_by = u.id
            WHERE sr.status = 'pending'
            ORDER BY sr.created_at ASC
        ");
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get request statistics (simplified for old schema)
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_pending,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_requests,
                0 as missing_requests,
                0 as damaged_requests
            FROM supply_requests 
            WHERE status = 'pending'
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'statistics' => $stats
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-pending-requests.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
}
?>