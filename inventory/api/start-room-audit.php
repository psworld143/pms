<?php
/**
 * Start room audit process
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

try {
    global $pdo;
    
    // Determine correct rooms table in this environment
    $roomsTable = 'hotel_rooms';
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'hotel_rooms'");
    if ($tableCheck->rowCount() === 0) {
        $roomsTable = 'rooms';
    }

    // Only set last_audited if the column exists (production may not have it)
    $colCheck = $pdo->query("SHOW COLUMNS FROM `{$roomsTable}` LIKE 'last_audited'");
    if ($colCheck->rowCount() > 0) {
        $pdo->exec("UPDATE `{$roomsTable}` SET last_audited = NOW() WHERE id IN (SELECT DISTINCT room_id FROM room_inventory_items)");
    }
    
    // Log audit activity (schema-adaptive insert)
    $colsStmt = $pdo->query("SHOW COLUMNS FROM inventory_transactions");
    $available = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $available = array_flip($available);

    $fields = [];
    $values = [];

    if (isset($available['item_id'])) { $fields[] = 'item_id'; $values[] = null; }
    if (isset($available['transaction_type'])) { $fields[] = 'transaction_type'; $values[] = 'adjustment'; }
    if (isset($available['quantity'])) { $fields[] = 'quantity'; $values[] = 0; }
    if (isset($available['reason'])) { $fields[] = 'reason'; $values[] = 'Room audit completed for all rooms'; }
    if (isset($available['user_id'])) { $fields[] = 'user_id'; $values[] = $_SESSION['user_id']; }
    if (isset($available['performed_by'])) { $fields[] = 'performed_by'; $values[] = $_SESSION['user_id']; }
    if (isset($available['created_at'])) { $fields[] = 'created_at'; $values[] = date('Y-m-d H:i:s'); }

    if (!empty($fields)) {
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $sql = 'INSERT INTO inventory_transactions (' . implode(',', $fields) . ') VALUES (' . $placeholders . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Room audit completed successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>