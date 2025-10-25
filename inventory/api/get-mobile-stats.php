<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

try {
    global $pdo;

    // Detect rooms table
    $roomsTable = 'hotel_rooms';
    $t = $pdo->query("SHOW TABLES LIKE 'hotel_rooms'");
    if ($t->rowCount() === 0) { $roomsTable = 'rooms'; }

    // Total rooms (as 'my rooms' for now; if you later add assignments, filter here)
    $myRooms = (int)$pdo->query("SELECT COUNT(*) FROM `{$roomsTable}`")->fetchColumn();

    // Need restock: rooms with any item below par
    $need = 0;
    $riiCols = $pdo->query("SHOW COLUMNS FROM room_inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    if (in_array('quantity_current', $riiCols, true) && in_array('par_level', $riiCols, true)) {
        $stmt = $pdo->query("SELECT COUNT(DISTINCT room_id) FROM room_inventory_items WHERE quantity_current < par_level");
        $need = (int)$stmt->fetchColumn();
    }

    echo json_encode(['success' => true, 'stats' => ['my_rooms' => $myRooms, 'need_restock' => $need]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
