<?php
/**
 * Export Inventory Report
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

try {
    global $pdo;

    // Pull items (basic) in a schema-adaptive manner
    $cols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $nameExpr = in_array('item_name', $cols, true) ? 'item_name' : (in_array('name', $cols, true) ? 'name' : 'id');
    $qtyCol   = in_array('current_stock', $cols, true) ? 'current_stock' : (in_array('quantity', $cols, true) ? 'quantity' : '0');
    $minCol   = in_array('minimum_stock', $cols, true) ? 'minimum_stock' : '0';
    $unitCol  = in_array('unit_price', $cols, true) ? 'unit_price' : '0';

    $sql = "SELECT id, $nameExpr AS name, $qtyCol AS quantity, $minCol AS minimum_stock, $unitCol AS unit_price FROM inventory_items ORDER BY $nameExpr LIMIT 1000";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $tmp = tempnam(sys_get_temp_dir(), 'inv_report_');
    $csv = fopen($tmp, 'w');
    fputcsv($csv, ['ID', 'Name', 'Quantity', 'Min Level', 'Unit Price', 'Total Value']);
    foreach ($rows as $r) {
        $total = (float)$r['unit_price'] * (float)$r['quantity'];
        fputcsv($csv, [$r['id'], $r['name'], $r['quantity'], $r['minimum_stock'], $r['unit_price'], $total]);
    }
    fclose($csv);

    $fileName = 'inventory_report_' . date('Ymd_His') . '.csv';
    $publicPath = '/tmp/' . $fileName; // adjust if you have a public tmp directory mapping
    // Move file to web-accessible tmp directory; fallback to returning absolute path
    $webTmpDir = dirname($_SERVER['SCRIPT_NAME']) . '/../../tmp';
    $realWebTmp = realpath(__DIR__ . '/../../tmp');
    if (!$realWebTmp) { @mkdir(__DIR__ . '/../../tmp', 0777, true); $realWebTmp = realpath(__DIR__ . '/../../tmp'); }
    if ($realWebTmp) {
        $dest = $realWebTmp . DIRECTORY_SEPARATOR . $fileName;
        @rename($tmp, $dest);
        $downloadUrl = dirname($_SERVER['SCRIPT_NAME']) . '/../../tmp/' . $fileName;
    } else {
        $downloadUrl = $publicPath; // may not be web accessible in some setups
    }

    echo json_encode(['success' => true, 'download_url' => $downloadUrl]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Export error: ' . $e->getMessage()]);
}
?>