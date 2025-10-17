<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$room_id   = $_POST['room_id']   ?? null;
$item_id   = $_POST['item_id']   ?? null;
$allocated = (int)($_POST['allocated'] ?? 0);
$current   = (int)($_POST['current']   ?? 0);
$par       = (int)($_POST['par']       ?? 0);

if (!$room_id || !$item_id) {
    echo json_encode(['success' => false, 'message' => 'Room ID and Item ID are required']);
    exit();
}

try {
    global $pdo;

    // Check room exists (support hotel_rooms or rooms)
    $roomsTable = 'hotel_rooms';
    $t = $pdo->query("SHOW TABLES LIKE 'hotel_rooms'");
    if ($t->rowCount() === 0) { $roomsTable = 'rooms'; }

    $stmt = $pdo->prepare("SELECT id FROM `{$roomsTable}` WHERE id = ?");
    $stmt->execute([$room_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit();
    }

    // room_inventory_items columns detection
    $cols = $pdo->query("SHOW COLUMNS FROM room_inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasAllocated = in_array('quantity_allocated', $cols, true);
    $hasCurrent   = in_array('quantity_current',   $cols, true);
    $hasPar       = in_array('par_level',          $cols, true);
    $hasUpdated   = in_array('updated_at',         $cols, true);

    // If record exists for this room+item, update instead of insert
    $exists = $pdo->prepare('SELECT id FROM room_inventory_items WHERE room_id = ? AND item_id = ?');
    $exists->execute([$room_id, $item_id]);
    $rowId = $exists->fetchColumn();

    if ($rowId) {
        $sets = [];
        $vals = [];
        if ($hasAllocated) { $sets[] = 'quantity_allocated = ?'; $vals[] = $allocated; }
        if ($hasCurrent)   { $sets[] = 'quantity_current   = ?'; $vals[] = $current; }
        if ($hasPar)       { $sets[] = 'par_level          = ?'; $vals[] = $par; }
        if ($hasUpdated)   { $sets[] = 'updated_at         = ?'; $vals[] = date('Y-m-d H:i:s'); }
        $vals[] = $rowId;

        if ($sets) {
            $sql = 'UPDATE room_inventory_items SET ' . implode(',', $sets) . ' WHERE id = ?';
            $pdo->prepare($sql)->execute($vals);
        }
    } else {
        $fields = ['room_id', 'item_id'];
        $values = [$room_id, $item_id];
        if ($hasAllocated) { $fields[] = 'quantity_allocated'; $values[] = $allocated; }
        if ($hasCurrent)   { $fields[] = 'quantity_current';   $values[] = $current; }
        if ($hasPar)       { $fields[] = 'par_level';          $values[] = $par; }
        if ($hasUpdated)   { $fields[] = 'updated_at';         $values[] = date('Y-m-d H:i:s'); }

        $ph = implode(',', array_fill(0, count($fields), '?'));
        $sql = 'INSERT INTO room_inventory_items (' . implode(',', $fields) . ') VALUES (' . $ph . ')';
        $pdo->prepare($sql)->execute($values);
    }

    echo json_encode(['success' => true, 'message' => 'Item assigned successfully']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
