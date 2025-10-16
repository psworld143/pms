<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Avoid any PHP warnings/notices breaking JSON responses
@ini_set('display_errors', 0);
@error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

try {
    global $pdo;

    $nameExpr = "COALESCE(ii.item_name, ii.name, CAST(ii.id AS CHAR))";
    $qtyCol   = "COALESCE(ii.current_stock, ii.quantity, 0)";
    $skuCol   = "COALESCE(ii.sku, '')";

    $pdo->exec("CREATE TABLE IF NOT EXISTS reorder_rules (
        item_id INT NOT NULL PRIMARY KEY,
        min_level INT NOT NULL DEFAULT 0,
        reorder_qty INT NOT NULL DEFAULT 0,
        supplier_id INT NULL
    )");

    $sql = "SELECT ii.id, $nameExpr AS name, $skuCol AS sku, $qtyCol AS current, rr.min_level, rr.reorder_qty, rr.supplier_id,
                   s.name AS supplier
            FROM inventory_items ii
            LEFT JOIN reorder_rules rr ON ii.id = rr.item_id
            LEFT JOIN suppliers s ON rr.supplier_id = s.id
            WHERE rr.item_id IS NOT NULL AND $qtyCol < rr.min_level
            ORDER BY supplier, name";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // group items by supplier
    $groups = [];
    foreach ($rows as $r) {
        $supplier = $r['supplier'] ?: 'Unassigned';
        $groups[$supplier][] = [
            'id' => $r['id'],
            'name' => $r['name'],
            'sku' => $r['sku'],
            'current' => (int)$r['current'],
            'min' => (int)$r['min_level'],
            'order_qty' => max(0, (int)$r['reorder_qty'])
        ];
    }

    if (isset($_GET['format']) && $_GET['format']==='pdf') {
        // Attempt dompdf if installed; else output printable HTML
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Suggested PO</title>';
        $html .= '<style>body{font-family:sans-serif} h2{margin-bottom:4px} table{width:100%;border-collapse:collapse;margin-bottom:16px} th,td{border:1px solid #ddd;padding:8px} th{background:#f5f5f5;text-align:left}</style>';
        $html .= '</head><body>';
        if (!$groups){
            $html .= '<h2>Suggested Purchase Order</h2><p>No items below threshold.</p>';
        } else {
            foreach ($groups as $supplier => $items) {
                $html .= '<h2>Supplier: '.htmlspecialchars($supplier).'</h2>';
                $html .= '<table><thead><tr><th>Item</th><th>SKU</th><th>Current</th><th>Min</th><th>Order Qty</th></tr></thead><tbody>';
                foreach($items as $it){
                    $html .= '<tr><td>'.htmlspecialchars($it['name']).'</td><td>'.htmlspecialchars($it['sku']).'</td><td>'.$it['current'].'</td><td>'.$it['min'].'</td><td>'.$it['order_qty'].'</td></tr>';
                }
                $html .= '</tbody></table>';
            }
        }
        $html .= '</body></html>';

        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new Dompdf\\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('suggested_po_'.date('Ymd_His').'.pdf');
            exit;
        }
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success'=>true,'items'=>$rows]);
} catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
