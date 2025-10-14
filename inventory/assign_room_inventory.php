<?php
// One-time room inventory assignment (schema-adaptive)
// Usage: visit /inventory/assign_room_inventory.php while logged in

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

try {
    global $pdo;

    // Detect rooms table
    $roomsTable = 'hotel_rooms';
    $rs = $pdo->query("SHOW TABLES LIKE 'hotel_rooms'");
    if ($rs->rowCount() === 0) {
        $roomsTable = 'rooms';
    }

    // Fetch rooms
    $rooms = $pdo->query("SELECT id FROM `{$roomsTable}` ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    if (!$rooms) {
        die('No rooms found.');
    }

    // Inventory items: pick up to 5 active items (schema-adaptive name/status)
    $cols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemName = in_array('item_name', $cols, true);
    $hasName     = in_array('name', $cols, true);
    $hasStatus   = in_array('status', $cols, true);

    $nameExpr = $hasItemName ? 'item_name' : ($hasName ? 'name' : 'id');
    $where    = $hasStatus ? "WHERE status = 'active'" : '';

    $items = $pdo->query("SELECT id, {$nameExpr} AS label FROM inventory_items {$where} ORDER BY {$nameExpr} LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    if (!$items) {
        die('No inventory items found to assign.');
    }

    // room_inventory_items existing columns
    $riiCols = $pdo->query("SHOW COLUMNS FROM room_inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasAllocated = in_array('quantity_allocated', $riiCols, true);
    $hasCurrent   = in_array('quantity_current', $riiCols, true);
    $hasPar       = in_array('par_level', $riiCols, true);
    $hasUpdated   = in_array('updated_at', $riiCols, true);

    $assignedCount = 0;

    foreach ($rooms as $roomId) {
        // Skip rooms that already have assignments
        $existsStmt = $pdo->prepare('SELECT COUNT(*) FROM room_inventory_items WHERE room_id = ?');
        $existsStmt->execute([$roomId]);
        if ($existsStmt->fetchColumn() > 0) {
            continue;
        }

        // Assign first 3 items with simple defaults
        $assignItems = array_slice($items, 0, min(3, count($items)));
        foreach ($assignItems as $it) {
            $fields = ['room_id', 'item_id'];
            $values = [$roomId, $it['id']];

            if ($hasAllocated) { $fields[] = 'quantity_allocated'; $values[] = 4; }
            if ($hasCurrent)   { $fields[] = 'quantity_current';   $values[] = 3; }
            if ($hasPar)       { $fields[] = 'par_level';          $values[] = 2; }
            if ($hasUpdated)   { $fields[] = 'updated_at';         $values[] = date('Y-m-d H:i:s'); }

            $ph = implode(',', array_fill(0, count($fields), '?'));
            $sql = 'INSERT INTO room_inventory_items (' . implode(',', $fields) . ') VALUES (' . $ph . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $assignedCount++;
        }
    }

    echo "Assigned items to rooms where missing. Total new assignments: {$assignedCount}";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
