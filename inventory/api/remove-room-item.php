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

    // Get item details before deletion
    $stmt = $pdo->prepare("SELECT * FROM {$riTable} WHERE id = ?");
    $stmt->execute([$itemId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Room item not found']);
        exit();
    }

    $roomId = (int)$row['room_id'];
    $itemIdActual = (int)$row['item_id'];
    $quantityBefore = (int)($row['quantity_current'] ?? 0);

    // Delete the item
    $stmt = $pdo->prepare("DELETE FROM {$riTable} WHERE id = ?");
    $stmt->execute([$itemId]);

    // Log transaction if table exists
    try {
        $pdo->prepare("INSERT INTO room_inventory_transactions (room_id, item_id, transaction_type, quantity_change, quantity_before, quantity_after, user_id, notes) VALUES (?, ?, 'remove', ?, ?, 0, ?, ?)")
            ->execute([$roomId, $itemIdActual, -$quantityBefore, $quantityBefore, $userId, 'Removed from room by manager']);
    } catch (Throwable $e) {
        // ignore if table/columns absent
    }

    echo json_encode(['success' => true, 'message' => 'Item removed successfully']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'detail' => $e->getMessage()]);
}

?>