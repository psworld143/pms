<?php
/**
 * Update Item Status
 * Allows housekeeping users to update item status (used, missing, damaged)
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
if ($user_role !== 'housekeeping') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Housekeeping role required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Validate required fields
if (!isset($_POST['item_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$valid_statuses = ['used', 'missing', 'damaged'];
$status = trim($_POST['status']);
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status. Must be: ' . implode(', ', $valid_statuses)]);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $item_id = (int)$_POST['item_id'];
    
    // Get room inventory item details
    $stmt = $pdo->prepare("
        SELECT ri.*, r.room_number, ii.item_name 
        FROM room_inventory ri
        JOIN rooms r ON ri.room_id = r.id
        JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE ri.id = ?
    ");
    $stmt->execute([$item_id]);
    $room_item = $stmt->fetch();
    
    if (!$room_item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Room inventory item not found']);
        exit();
    }
    
    // Update the item status and quantity based on the status
    $new_quantity = $room_item['quantity_current'];
    $transaction_type = '';
    
    switch ($status) {
        case 'used':
            $new_quantity = max(0, $room_item['quantity_current'] - 1);
            $transaction_type = 'usage';
            break;
        case 'missing':
            $new_quantity = 0;
            $transaction_type = 'missing';
            break;
        case 'damaged':
            $new_quantity = max(0, $room_item['quantity_current'] - 1);
            $transaction_type = 'damaged';
            break;
    }
    
    // Update room inventory
    $stmt = $pdo->prepare("
        UPDATE room_inventory 
        SET quantity_current = ?, last_updated = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$new_quantity, $item_id]);
    
    // Record transaction using your existing table structure
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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $quantity_changed = $room_item['quantity_current'] - $new_quantity;
    $notes = "Item marked as $status by housekeeping";
    
    $stmt->execute([
        $room_item['room_id'],
        $room_item['item_id'],
        $transaction_type,
        $quantity_changed,
        $room_item['quantity_current'],
        $new_quantity,
        $status,
        $notes,
        $user_id
    ]);
    
    // Log the activity
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
        VALUES (?, 'item_status_updated', ?, ?, ?, NOW())
    ");
    
    $details = "Item '{$room_item['item_name']}' in Room {$room_item['room_number']} marked as $status";
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt->execute([$user_id, $details, $ip_address, $user_agent]);
    
    echo json_encode([
        'success' => true,
        'message' => "Item marked as $status successfully",
        'new_quantity' => $new_quantity
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in update-item-status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
