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
    
    // Schema-adaptive insert into inventory_transactions
    $colsStmt = $pdo->query("SHOW COLUMNS FROM inventory_transactions");
    $available = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $available = array_flip($available);

    $fields = [];
    $values = [];

    if (isset($available['item_id'])) { $fields[] = 'item_id'; $values[] = null; }
    if (isset($available['transaction_type'])) { $fields[] = 'transaction_type'; $values[] = 'adjustment'; }
    if (isset($available['quantity'])) { $fields[] = 'quantity'; $values[] = 0; }
    if (isset($available['reason'])) { $fields[] = 'reason'; $values[] = 'Room audit completed for Room ' . $room['room_number']; }
    if (isset($available['user_id'])) { $fields[] = 'user_id'; $values[] = $_SESSION['user_id']; }
    if (isset($available['performed_by'])) { $fields[] = 'performed_by'; $values[] = $_SESSION['user_id']; }
    if (isset($available['created_at'])) { $fields[] = 'created_at'; $values[] = date('Y-m-d H:i:s'); }

    if (!empty($fields)) {
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $sql = 'INSERT INTO inventory_transactions (' . implode(',', $fields) . ') VALUES (' . $placeholders . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
    
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
        'message' => 'Room ' . $room['room_number'] . ' audit completed successfully',
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
