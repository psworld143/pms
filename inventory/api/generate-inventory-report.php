<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

try {
    global $pdo;

    $reportType = $_GET['report_type'] ?? 'stock_level';
    $dateRange  = $_GET['date_range'] ?? 'last_30';
    $category   = $_GET['category'] ?? '';

    // inventory_items schema discovery
    $cols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $nameExpr = in_array('item_name', $cols, true) ? 'item_name' : (in_array('name', $cols, true) ? 'name' : 'id');
    $qtyCol   = in_array('current_stock', $cols, true) ? 'current_stock' : (in_array('quantity', $cols, true) ? 'quantity' : '0');
    $minCol   = in_array('minimum_stock', $cols, true) ? 'minimum_stock' : '0';
    $unitCol  = in_array('unit_price', $cols, true) ? 'unit_price' : '0';
    $catCol   = in_array('category_name', $cols, true) ? 'category_name' : (in_array('category', $cols, true) ? 'category' : "''");

    $where = [];
    $params = [];
    if ($category && $category !== 'All Categories') {
        $where[] = "$catCol = ?"; $params[] = $category;
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "SELECT id, $nameExpr AS name, $qtyCol AS quantity, $minCol AS minimum_stock, $unitCol AS unit_price, $catCol AS category_name FROM inventory_items $whereSql ORDER BY $nameExpr LIMIT 1000";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build summary
    $summary = [
        'total_items'   => 0,
        'total_value'   => 0,
        'in_stock'      => 0,
        'low_stock'     => 0,
        'out_of_stock'  => 0,
        'high_value_total' => 0,
    ];

    foreach ($items as $it) {
        $summary['total_items']++;
        $qty = (int)($it['quantity'] ?? 0);
        $min = (int)($it['minimum_stock'] ?? 0);
        $price = (float)($it['unit_price'] ?? 0);
        $summary['total_value'] += $qty * $price;
        if ($qty <= 0) $summary['out_of_stock']++; else if ($qty < $min) $summary['low_stock']++; else $summary['in_stock']++;
    }

    // Simple high value estimate = top 10% by total line value
    $sorted = $items;
    usort($sorted, function($a,$b){ return ($b['unit_price']*$b['quantity']) <=> ($a['unit_price']*$a['quantity']); });
    $topN = max(1, (int)floor(count($sorted)*0.1));
    $summary['high_value_total'] = 0;
    for ($i=0; $i<$topN && $i<count($sorted); $i++) {
        $summary['high_value_total'] += (float)$sorted[$i]['unit_price'] * (int)$sorted[$i]['quantity'];
    }

    echo json_encode(['success' => true, 'summary' => $summary, 'items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Report error: ' . $e->getMessage()]);
}
