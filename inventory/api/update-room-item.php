<?php
require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';

// Only managers may update quantities/par levels
if ($userRole !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Manager role required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$itemId = (int)($_POST['item_id'] ?? 0);
$allocated = isset($_POST['allocated']) ? (int)$_POST['allocated'] : null;
$current = isset($_POST['current']) ? (int)$_POST['current'] : null;
$par = isset($_POST['par']) ? (int)$_POST['par'] : null;

if (!$itemId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'item_id is required']);
    exit();
}

try {
    // Ensure room_inventory table name
    $riTable = 'room_inventory';
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$riTable]);
    if (!$stmt->fetchColumn()) { $riTable = 'room_inventory_items'; }

    // Fetch existing row by item_id (assuming it's the room_inventory.id)
    $stmt = $pdo->prepare("SELECT * FROM {$riTable} WHERE id = ?");
    $stmt->execute([$itemId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Room item not found']);
        exit();
    }

    $roomId = (int)$row['room_id'];
    $fields = [];
    $params = [];
    $quantityBefore = (int)($row['quantity_current'] ?? 0);
    $quantityAfter = $quantityBefore;
    $notes = [];

    if ($allocated !== null) { $fields[] = 'quantity_allocated = ?'; $params[] = $allocated; $notes[] = "allocated={$allocated}"; }
    if ($current !== null) { $fields[] = 'quantity_current = ?'; $params[] = $current; $quantityAfter = $current; $notes[] = "current={$current}"; }
    if ($par !== null) { $fields[] = 'par_level = ?'; $params[] = $par; $notes[] = "par={$par}"; }

    if (!$fields) {
        echo json_encode(['success' => true, 'message' => 'No changes']);
        exit();
    }

    $params[] = $itemId;
    $sql = "UPDATE {$riTable} SET " . implode(', ', $fields) . " WHERE id = ?";
    $pdo->prepare($sql)->execute($params);

    // Log transaction in room_inventory_transactions if available
    try {
        $change = $quantityAfter - $quantityBefore;
        $pdo->prepare("INSERT INTO room_inventory_transactions (room_id, item_id, transaction_type, quantity_change, quantity_before, quantity_after, user_id, notes) VALUES (?, ?, 'update', ?, ?, ?, ?, ?)")
            ->execute([$roomId, (int)$row['item_id'], $change, $quantityBefore, $quantityAfter, $userId, 'Manager update: ' . implode(', ', $notes)]);
    } catch (Throwable $e) {
        // ignore if table/columns absent
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'detail' => $e->getMessage()]);
}

?>


