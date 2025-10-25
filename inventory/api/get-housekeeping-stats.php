<?php
/**
 * Get Housekeeping Statistics
 * Returns statistics specific to housekeeping users
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Debug session data
error_log("Session data in get-housekeeping-stats.php: " . print_r($_SESSION, true));

// Check if user is logged in and has housekeeping role
if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session. Session data: " . print_r($_SESSION, true));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated', 'debug' => 'No user_id in session']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'housekeeping') {
    error_log("User role is not housekeeping. Role: " . $user_role . ", Session data: " . print_r($_SESSION, true));
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Housekeeping role required', 'debug' => 'User role: ' . $user_role]);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Check if assigned_housekeeping column exists
    $check_columns = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'assigned_housekeeping'");
    $has_assigned_housekeeping = $check_columns->rowCount() > 0;
    
    // Get rooms assigned to this housekeeping user
    if ($has_assigned_housekeeping) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as my_rooms 
            FROM rooms r 
            WHERE r.assigned_housekeeping = ? OR r.assigned_housekeeping IS NULL
        ");
        $stmt->execute([$user_id]);
    } else {
        // Fallback: count all rooms if column doesn't exist
        $stmt = $pdo->query("SELECT COUNT(*) as my_rooms FROM rooms");
    }
    $my_rooms = $stmt->fetch()['my_rooms'];
    
    // Get items used today by this user (using your existing table structure)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as items_used 
        FROM room_inventory_transactions rit
        WHERE rit.user_id = ? 
        AND DATE(rit.created_at) = CURDATE()
        AND rit.transaction_type = 'usage'
    ");
    $stmt->execute([$user_id]);
    $items_used = $stmt->fetch()['items_used'];
    
    // Get missing items in assigned rooms
    if ($has_assigned_housekeeping) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as missing_items 
            FROM room_inventory ri
            JOIN rooms r ON ri.room_id = r.id
            WHERE (r.assigned_housekeeping = ? OR r.assigned_housekeeping IS NULL)
            AND ri.quantity_current < ri.par_level
        ");
        $stmt->execute([$user_id]);
    } else {
        // Fallback: count all rooms with low stock
        $stmt = $pdo->query("
            SELECT COUNT(*) as missing_items 
            FROM room_inventory ri
            WHERE ri.quantity_current < ri.par_level
        ");
    }
    $missing_items = $stmt->fetch()['missing_items'];
    
    // Get pending requests by this user
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as my_requests 
        FROM supply_requests sr
        WHERE sr.requested_by = ? 
        AND sr.status IN ('pending', 'approved', 'in_progress')
    ");
    $stmt->execute([$user_id]);
    $my_requests = $stmt->fetch()['my_requests'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_rooms' => (int)$my_rooms,
            'total_items' => (int)$items_used,
            'missing_items' => (int)$missing_items,
            'pending_requests' => (int)$my_requests
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-housekeeping-stats.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
}
?>