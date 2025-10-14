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
    
    // Log audit activity
    $stmt = $pdo->prepare("
        INSERT INTO inventory_transactions (item_id, transaction_type, quantity, reason, user_id, performed_by, created_at)
        VALUES (NULL, 'adjustment', 0, 'Room audit completed for all rooms', ?, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    
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