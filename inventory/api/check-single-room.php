<?php
/**
 * Check Single Room for Housekeeping
 * Initiates a room check process for a specific room
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in and has housekeeping role
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['housekeeping', 'manager'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Housekeeping or Manager role required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Validate required fields
if (!isset($_POST['room_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Room ID is required']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $room_id = (int)$_POST['room_id'];
    
    // Debug logging
    error_log("Check single room API called for room_id: $room_id by user_id: $user_id");
    
    // Get room details
    $stmt = $pdo->prepare("
        SELECT r.*, ri.id as inventory_id, ri.item_id, ri.quantity_current, ri.quantity_allocated, ri.par_level,
               ii.item_name, ii.sku
        FROM rooms r
        LEFT JOIN room_inventory ri ON r.id = ri.room_id
        LEFT JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE r.id = ?
    ");
    $stmt->execute([$room_id]);
    $room_data = $stmt->fetchAll();
    
    if (empty($room_data)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit();
    }
    
    $room = $room_data[0];
    $room_number = $room['room_number'];
    
    // Log the room check activity
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
        VALUES (?, 'single_room_check_started', ?, ?, ?, NOW())
    ");
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $details = ucfirst($user_role) . " user started room check for Room {$room_number}";
    
    $stmt->execute([$user_id, $details, $ip_address, $user_agent]);
    
    // Update last updated time for the specific room (using existing column)
    // This is optional and won't fail the entire operation if it doesn't work
    try {
        $stmt = $pdo->prepare("
            UPDATE rooms 
            SET updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$room_id]);
        error_log("Room timestamp updated successfully for room_id: $room_id");
    } catch (PDOException $e) {
        // Log the error but don't fail the entire operation
        error_log("Could not update room timestamp (this is optional): " . $e->getMessage());
    }
    
    // Create a room check transaction record (using first inventory item if available)
    $check_id = null;
    try {
        $first_item_id = null;
        if (!empty($room_data) && $room_data[0]['item_id']) {
            $first_item_id = $room_data[0]['item_id'];
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO room_inventory_transactions (
                room_id, 
                item_id, 
                transaction_type, 
                quantity_change, 
                quantity_before, 
                quantity_after, 
                reason, 
                notes, 
                user_id, 
                created_at
            ) VALUES (?, ?, 'audit', 0, 0, 0, 'room_check_started', ?, ?, NOW())
        ");
        $stmt->execute([$room_id, $first_item_id, "Room check started for Room {$room_number}", $user_id]);
        
        $check_id = $pdo->lastInsertId();
    } catch (PDOException $e) {
        // Log the error but don't fail the entire operation
        error_log("Could not create room check transaction record: " . $e->getMessage());
        $check_id = 0; // Use 0 as fallback
    }
    
    // Initialize inventory check items for this room
    $inventory_items = [];
    foreach ($room_data as $item) {
        if ($item['inventory_id']) {
            $inventory_items[] = [
                'inventory_id' => $item['inventory_id'],
                'item_name' => $item['item_name'],
                'sku' => $item['sku'],
                'quantity_current' => $item['quantity_current'],
                'quantity_allocated' => $item['quantity_allocated'],
                'par_level' => $item['par_level']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Room check started successfully',
        'room_number' => $room_number,
        'check_id' => $check_id,
        'inventory_items' => $inventory_items,
        'total_items' => count($inventory_items)
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in check-single-room.php: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("Error Info: " . print_r($e->errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in check-single-room.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred',
        'debug' => $e->getMessage()
    ]);
}
?>
