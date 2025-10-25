<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_role = strtolower($_SESSION['user_role'] ?? '');

try {
    global $pdo;

    // Determine item name expression
    $itemCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $itemNameExpr = in_array('item_name', $itemCols, true) ? 'ii.item_name' : (in_array('name', $itemCols, true) ? 'ii.name' : "CONCAT('Item ', ii.id)");

    $where = '';
    $params = [];
    if ($user_role === 'housekeeping') {
        $where = 'WHERE iur.user_id = ?';
        $params[] = $user_id;
    }

    $sql = "SELECT iur.id, iur.item_id, $itemNameExpr AS item_name, iur.user_id, iur.quantity, iur.room, iur.date_used, COALESCE(iur.created_at, iur.date_used) AS created_at, iur.notes FROM inventory_usage_reports iur LEFT JOIN inventory_items ii ON iur.item_id = ii.id $where ORDER BY COALESCE(iur.created_at, iur.date_used) DESC, iur.id DESC LIMIT 200";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reports' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
