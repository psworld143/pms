<?php
/**
 * Audit a single room's inventory
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$room_id = $_POST['room_id'] ?? '';

if (empty($room_id)) {
    echo json_encode(['success' => false, 'message' => 'Room ID required']);
    exit();
}

try {
    global $pdo;
    
    $pdo->beginTransaction();

    // Determine correct rooms table (hotel_rooms preferred)
    $roomsTable = 'hotel_rooms';
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'hotel_rooms'");
    if ($tableCheck->rowCount() === 0) {
        $roomsTable = 'rooms';
    }
    
    // Update last audited timestamp for the specific room only if column exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM `{$roomsTable}` LIKE 'last_audited'");
    if ($colCheck->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE `{$roomsTable}` SET last_audited = NOW() WHERE id = ?");
        $stmt->execute([$room_id]);
    }
    
    // Get room details for logging
    $stmt = $pdo->prepare("SELECT room_number FROM `{$roomsTable}` WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        throw new Exception('Room not found');
    }
    
    // Log audit activity
    $stmt = $pdo->prepare("
        INSERT INTO inventory_transactions (item_id, transaction_type, quantity, reason, user_id, performed_by, created_at)
        VALUES (NULL, 'adjustment', 0, 'Room audit completed for Room {$room['room_number']}', ?, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    
    // Get room inventory items for summary
    $stmt = $pdo->prepare("
        SELECT ri.*, ii.id AS inv_id
        FROM room_inventory_items ri
        JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE ri.room_id = ?
    ");
    $stmt->execute([$room_id]);
    $inventory_items = $stmt->fetchAll();
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Room {$room['room_number']} audit completed successfully",
        'room_number' => $room['room_number'],
        'items_audited' => count($inventory_items)
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
