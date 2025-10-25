<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

try {
    global $pdo;

    $txCols = $pdo->query("SHOW COLUMNS FROM inventory_transactions")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemId = in_array('item_id', $txCols, true);

    $nameCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $nameExpr = in_array('item_name', $nameCols, true) ? 'ii.item_name' : (in_array('name', $nameCols, true) ? 'ii.name' : "CONCAT('Item ', ii.id)");

    if ($hasItemId) {
        $sql = "SELECT it.*, $nameExpr AS item_name FROM inventory_transactions it LEFT JOIN inventory_items ii ON it.item_id = ii.id ORDER BY it.created_at DESC, it.id DESC LIMIT 100";
    } else {
        $sql = "SELECT it.*, NULL AS item_name FROM inventory_transactions it ORDER BY it.created_at DESC, it.id DESC LIMIT 100";
    }

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'transactions' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
