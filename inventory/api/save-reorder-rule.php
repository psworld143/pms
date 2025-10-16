<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

@ini_set('display_errors', 0);
@error_reporting(EALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');

try {
    global $pdo;

    $item_id = (int)($_POST['item_id'] ?? 0);
    $min = (int)($_POST['min_level'] ?? 0);
    $qty = (int)($_POST['reorder_qty'] ?? 0);
    $supplier_id = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;

    if (!$item_id) { echo json_encode(['success'=>false,'message'=>'Item required']); exit; }

    $pdo->exec("CREATE TABLE IF NOT EXISTS reorder_rules (
        item_id INT NOT NULL PRIMARY KEY,
        min_level INT NOT NULL DEFAULT 0,
        reorder_qty INT NOT NULL DEFAULT 0,
        supplier_id INT NULL
    )");

    $stmt = $pdo->prepare("REPLACE INTO reorder_rules (item_id, min_level, reorder_qty, supplier_id) VALUES (?,?,?,?)");
    $stmt->execute([$item_id, $min, $qty, $supplier_id]);

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
