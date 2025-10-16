<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

@ini_set('display_errors', 0);
@error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');

try {
    global $pdo;

    // Ensure rules table exists (safe)
    $pdo->exec("CREATE TABLE IF NOT EXISTS reorder_rules (
        item_id INT NOT NULL PRIMARY KEY,
        min_level INT NOT NULL DEFAULT 0,
        reorder_qty INT NOT NULL DEFAULT 0,
        supplier_id INT NULL
    )");

    // Detect columns in inventory_items
    $iCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemName = in_array('item_name', $iCols, true);
    $hasName = in_array('name', $iCols, true);
    $hasSku = in_array('sku', $iCols, true);
    $hasCurrent = in_array('current_stock', $iCols, true);
    $hasQuantity = in_array('quantity', $iCols, true);

    $nameExpr = $hasItemName ? 'ii.item_name' : ($hasName ? 'ii.name' : 'CAST(ii.id AS CHAR)');
    $skuExpr = $hasSku ? 'ii.sku' : "''";
    $currentExpr = $hasCurrent ? 'ii.current_stock' : ($hasQuantity ? 'ii.quantity' : '0');

    // Detect columns in reorder_rules (older DBs might not have our exact names)
    $rCols = $pdo->query("SHOW COLUMNS FROM reorder_rules")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasMin = in_array('min_level', $rCols, true);
    $hasReorder = in_array('reorder_qty', $rCols, true);
    $hasSupplier = in_array('supplier_id', $rCols, true);

    $minExpr = $hasMin ? 'rr.min_level' : '0';
    $reorderExpr = $hasReorder ? 'rr.reorder_qty' : '0';
    $supplierExpr = $hasSupplier ? 'rr.supplier_id' : 'NULL';

    // Suppliers (optional)
    $suppliers = [];
    try {
        $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $suppliers = [];
    }

    $sql = "SELECT 
                ii.id,
                $nameExpr AS name,
                $skuExpr AS sku,
                $currentExpr AS current,
                $minExpr AS min_level,
                $reorderExpr AS reorder_qty,
                $supplierExpr AS supplier_id
            FROM inventory_items ii
            LEFT JOIN reorder_rules rr ON ii.id = rr.item_id
            ORDER BY name
            LIMIT 1000";

    $stmt = $pdo->query($sql);
    $items = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    // Build stats and ensure numeric fields
    $stats = ['items_with_rules' => 0, 'below_threshold' => 0, 'suppliers' => count($suppliers)];
    foreach ($items as &$i) {
        $i['current'] = (int)($i['current'] ?? 0);
        $i['min_level'] = (int)($i['min_level'] ?? 0);
        $i['reorder_qty'] = (int)($i['reorder_qty'] ?? 0);
        if ($i['min_level'] > 0 || $i['reorder_qty'] > 0 || !empty($i['supplier_id'])) {
            $stats['items_with_rules']++;
        }
        if ($i['current'] < $i['min_level']) {
            $stats['below_threshold']++;
        }
    }

    echo json_encode(['success' => true, 'items' => $items, 'suppliers' => $suppliers, 'stats' => $stats]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: '.$e->getMessage()]);
}
