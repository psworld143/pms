<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

try {
    global $pdo;

    $pdo->exec("CREATE TABLE IF NOT EXISTS reorder_rules (
        item_id INT NOT NULL PRIMARY KEY,
        min_level INT NOT NULL DEFAULT 0,
        reorder_qty INT NOT NULL DEFAULT 0,
        supplier_id INT NULL
    )");

    $pdo->beginTransaction();
    // Insert defaults for items without a rule
    $sql = "INSERT IGNORE INTO reorder_rules (item_id, min_level, reorder_qty)
            SELECT id, 10, 20 FROM inventory_items";
    $pdo->exec($sql);
    $pdo->commit();

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
