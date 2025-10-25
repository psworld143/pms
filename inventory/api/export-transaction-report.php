<?php
/**
 * Export Transaction Report (PDF)
 * Hotel PMS Inventory Module
 */

// Keep output clean
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';
// Load Dompdf (vendor)
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Export failed', 'debug' => 'vendor/autoload.php not found']);
    exit();
}
require_once $autoloadPath;

use Dompdf\Dompdf;

header('Content-Type: application/json');

// Auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
$user_role = strtolower($_SESSION['user_role'] ?? '');
if ($user_role !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    $result = exportTransactionReportPdf();
    echo json_encode([
        'success' => true,
        'message' => 'Transaction report exported successfully',
        'download_url' => $result['download_url']
    ]);
} catch (Throwable $e) {
    error_log('Error exporting transaction report PDF: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Export failed', 'debug' => $e->getMessage()]);
}

function exportTransactionReportPdf(): array {
    global $pdo;

    // Schema-adaptive metadata
    $itemCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $txCols = $pdo->query("SHOW COLUMNS FROM inventory_transactions")->fetchAll(PDO::FETCH_COLUMN, 0);

    $itemNameExpr = in_array('item_name', $itemCols, true) ? 'ii.item_name' : (in_array('name', $itemCols, true) ? 'ii.name' : "CONCAT('Item ', ii.id)");
    $createdExpr = in_array('created_at', $txCols, true) ? 'it.created_at' : 'NOW()';
    $unitPriceExpr = in_array('unit_price', $txCols, true) ? 'it.unit_price' : '0';
    $reasonExpr = in_array('reason', $txCols, true) ? 'it.reason' : "''";
    $performedCol = in_array('performed_by', $txCols, true) ? 'it.performed_by' : null;
    $joinUsers = $performedCol ? ' LEFT JOIN users u ON ' . $performedCol . ' = u.id ' : '';
    $performedName = $performedCol ? 'u.name' : "''";

    $sql = "SELECT 
                $createdExpr AS transaction_date,
                it.transaction_type,
                $itemNameExpr AS item_name,
                it.quantity,
                $unitPriceExpr AS unit_price,
                (it.quantity * ($unitPriceExpr)) as total_value,
                $reasonExpr AS reason,
                $performedName as performed_by_user
            FROM inventory_transactions it
            JOIN inventory_items ii ON it.item_id = ii.id
            $joinUsers
            ORDER BY transaction_date DESC";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Build HTML
    $now = date('Y-m-d H:i');
    $title = 'Transaction Report';
    $thead = '<tr>
        <th>Date</th>
        <th>Type</th>
        <th>Item</th>
        <th style="text-align:right">Qty</th>
        <th style="text-align:right">Unit Price</th>
        <th style="text-align:right">Total</th>
        <th>Reason</th>
        <th>Performed By</th>
    </tr>';

    $tbody = '';
    foreach ($rows as $r) {
        $tbody .= '<tr>'
                . '<td>' . htmlspecialchars($r['transaction_date']) . '</td>'
                . '<td>' . htmlspecialchars($r['transaction_type']) . '</td>'
                . '<td>' . htmlspecialchars($r['item_name']) . '</td>'
                . '<td style="text-align:right">' . number_format((float)$r['quantity'], 0) . '</td>'
                . '<td style="text-align:right">' . number_format((float)$r['unit_price'], 2) . '</td>'
                . '<td style="text-align:right">' . number_format((float)$r['total_value'], 2) . '</td>'
                . '<td>' . htmlspecialchars($r['reason']) . '</td>'
                . '<td>' . htmlspecialchars($r['performed_by_user']) . '</td>'
                . '</tr>';
    }

    $html = '<html><head><meta charset="utf-8"><style>
        body{font-family: DejaVu Sans, Arial, sans-serif;}
        h1{font-size:18px;margin:0 0 6px 0}
        .meta{color:#666;font-size:12px;margin-bottom:10px}
        table{width:100%;border-collapse:collapse;font-size:12px}
        th,td{border:1px solid #ddd;padding:6px}
        th{background:#f5f5f5;text-align:left}
    </style></head><body>
        <h1>' . $title . '</h1>
        <div class="meta">Generated: ' . $now . '</div>
        <table><thead>' . $thead . '</thead><tbody>' . $tbody . '</tbody></table>
    </body></html>';

    // Try PDF first, fallback to CSV if any error occurs
    try {
        $dompdf = new Dompdf();
        if (method_exists($dompdf, 'set_option')) { $dompdf->set_option('isRemoteEnabled', false); }
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        $filename = 'transaction_report_' . date('Y-m-d_H-i-s') . '.pdf';
        $dir = __DIR__ . '/../tmp/';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        file_put_contents($dir . $filename, $pdfOutput);
        return ['download_url' => 'tmp/' . $filename];
    } catch (Throwable $e) {
        // Fallback to CSV to ensure export works
        $csv = "Transaction Date,Type,Item,Quantity,Unit Price,Total,Reason,Performed By\n";
        foreach ($rows as $r) {
            $csv .= '"' . str_replace('"','""',$r['transaction_date']) . '",' .
                    '"' . str_replace('"','""',$r['transaction_type']) . '",' .
                    '"' . str_replace('"','""',$r['item_name']) . '",' .
                    (float)$r['quantity'] . ',' .
                    number_format((float)$r['unit_price'], 2, '.', '') . ',' .
                    number_format((float)$r['total_value'], 2, '.', '') . ',' .
                    '"' . str_replace('"','""',$r['reason']) . '",' .
                    '"' . str_replace('"','""',$r['performed_by_user']) . '"' . "\n";
        }
        $dir = __DIR__ . '/../tmp/';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        $filename = 'transaction_report_' . date('Y-m-d_H-i-s') . '.csv';
        file_put_contents($dir . $filename, $csv);
        return ['download_url' => 'tmp/' . $filename];
    }
}
?>