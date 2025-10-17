<?php
// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Direct database connection without debug output
try {
    $host = 'localhost';
    $dbname = 'pms_pms_hotel';
    $username = 'pms_pms_hotel';
    $password = '020894HotelPMS';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Clean any output before sending JSON
ob_clean();
header('Content-Type: application/json');

try {

    // Use existing table structure - reorder_rules table already exists with correct columns
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

    // Use correct column names for existing reorder_rules table
    $minExpr = 'rr.reorder_point';
    $reorderExpr = 'rr.reorder_quantity';
    $supplierExpr = 'rr.supplier_id';

    // Suppliers (optional) - try inventory_suppliers first, then fallback to suppliers
    $suppliers = [];
    try {
        $suppliers = $pdo->query("SELECT id, name FROM inventory_suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        // If no suppliers in inventory_suppliers, use suppliers table
        if (empty($suppliers)) {
            $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        // Fallback to suppliers table
        try {
            $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e2) {
            $suppliers = [];
        }
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
