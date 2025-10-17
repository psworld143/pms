<?php
/**
 * Add item to room inventory
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in and is manager
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$room_id = $_POST['room_id'] ?? '';
$item_id = $_POST['item_id'] ?? '';
$quantity_allocated = $_POST['quantity_allocated'] ?? 1;
$quantity_current = $_POST['quantity_current'] ?? 1;
$par_level = $_POST['par_level'] ?? 2;

if (empty($room_id) || empty($item_id)) {
    echo json_encode(['success' => false, 'message' => 'Room ID and Item ID are required']);
    exit();
}

try {
    // Check if item already exists in room
    $stmt = $pdo->prepare("
        SELECT id FROM room_inventory 
        WHERE room_id = ? AND item_id = ?
    ");
    $stmt->execute([$room_id, $item_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Item already exists in this room']);
        exit();
    }
    
    // Add item to room
    $stmt = $pdo->prepare("
        INSERT INTO room_inventory (room_id, item_id, quantity_allocated, quantity_current, par_level, last_updated)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$room_id, $item_id, $quantity_allocated, $quantity_current, $par_level]);
    
    // Log the transaction
    $stmt = $pdo->prepare("
        INSERT INTO room_inventory_transactions (room_id, item_id, transaction_type, quantity_change, quantity_before, quantity_after, user_id, notes)
        VALUES (?, ?, 'add', ?, 0, ?, ?, ?)
    ");
    $stmt->execute([
        $room_id, 
        $item_id, 
        $quantity_allocated, 
        $quantity_allocated,
        $_SESSION['user_id'], 
        "Item added to room by manager"
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to room successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in add-room-item.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
