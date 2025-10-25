<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

try {
    global $pdo;

    // Detect columns
    $cols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemName = in_array('item_name', $cols, true);
    $hasName     = in_array('name', $cols, true);
    $hasStatus   = in_array('status', $cols, true);

    $nameExpr = $hasItemName ? 'item_name' : ($hasName ? 'name' : 'id');
    $where    = $hasStatus ? "WHERE status = 'active'" : '';

    $stmt = $pdo->query("SELECT id, {$nameExpr} AS label FROM inventory_items {$where} ORDER BY {$nameExpr} LIMIT 500");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
