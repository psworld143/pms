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
// Both manager and housekeeping can mark status
if (!in_array($userRole, ['manager', 'housekeeping'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized role']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$roomId = (int)($_POST['room_id'] ?? 0);
$itemId = (int)($_POST['item_id'] ?? 0);
$status = trim($_POST['status'] ?? ''); // used, missing, damaged, ok
$delta = isset($_POST['delta']) ? (int)$_POST['delta'] : 0; // optional change to current quantity
$notes = trim($_POST['notes'] ?? '');

if (!$roomId || !$itemId || $status === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'room_id, item_id, and status are required']);
    exit();
}

try {
    $riTable = 'room_inventory';
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$riTable]);
    if (!$stmt->fetchColumn()) { $riTable = 'room_inventory_items'; }

    // read current
    $stmt = $pdo->prepare("SELECT quantity_current FROM {$riTable} WHERE room_id = ? AND item_id = ?");
    $stmt->execute([$roomId, $itemId]);
    $current = (int)($stmt->fetchColumn() ?: 0);
    $before = $current;
    $after = max(0, $current + $delta);

    if ($delta !== 0) {
        $pdo->prepare("UPDATE {$riTable} SET quantity_current = ? WHERE room_id = ? AND item_id = ?")
            ->execute([$after, $roomId, $itemId]);
    }

    // log status/transaction
    try {
        $pdo->prepare("INSERT INTO room_inventory_transactions (room_id, item_id, transaction_type, quantity_change, quantity_before, quantity_after, user_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$roomId, $itemId, $status, $after - $before, $before, $after, $userId, $notes ?: 'Status update']);
    } catch (Throwable $e) { /* ignore */ }

    echo json_encode(['success' => true, 'quantity_after' => $after]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'detail' => $e->getMessage()]);
}

?>


