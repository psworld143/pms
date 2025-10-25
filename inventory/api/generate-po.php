<?php
// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Direct database connection without debug output
try {
    // Use the same database configuration as the main system
    require_once __DIR__ . '/../../includes/database.php';
    
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection not established');
    }
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Clean any output before sending JSON
ob_clean();

try {

    $nameExpr = "ii.item_name";
    $qtyCol   = "ii.current_stock";
    $skuCol   = "COALESCE(ii.sku, '')";

    // Use existing reorder_rules table with correct column names
    $sql = "SELECT ii.id, $nameExpr AS name, $skuCol AS sku, $qtyCol AS current, rr.reorder_point AS min_level, rr.reorder_quantity AS reorder_qty, rr.supplier_id,
                   COALESCE(s.name, 'Unassigned') AS supplier
            FROM inventory_items ii
            LEFT JOIN reorder_rules rr ON ii.id = rr.item_id
            LEFT JOIN inventory_suppliers s ON rr.supplier_id = s.id
            WHERE rr.item_id IS NOT NULL AND $qtyCol < rr.reorder_point
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
                'min_level' => (int)$r['min_level'],
                'reorder_qty' => max(0, (int)$r['reorder_qty']),
                'min' => (int)$r['min_level'], // For PDF compatibility
                'order_qty' => max(0, (int)$r['reorder_qty']) // For PDF compatibility
            ];
        }

    if (isset($_GET['format']) && $_GET['format']==='pdf') {
        // Generate a proper PDF using a simple approach
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Suggested Purchase Order</title>';
        $html .= '<style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                color: #333;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #333;
                padding-bottom: 20px;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                color: #2c3e50;
            }
            .header p {
                margin: 5px 0 0 0;
                color: #666;
            }
            h2 {
                margin: 30px 0 15px 0;
                color: #2c3e50;
                font-size: 18px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
                page-break-inside: avoid;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 12px 8px;
                text-align: left;
            }
            th {
                background-color: #f8f9fa;
                font-weight: bold;
                color: #2c3e50;
            }
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .no-items {
                text-align: center;
                color: #666;
                font-style: italic;
                padding: 40px;
            }
            .print-btn {
                background: #007bff;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin: 20px 0;
            }
            .print-btn:hover {
                background: #0056b3;
            }
        </style>';
        $html .= '</head><body>';
        
        // Header
        $html .= '<div class="header">';
        $html .= '<h1>Suggested Purchase Order</h1>';
        $html .= '<p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>';
        $html .= '</div>';
        
        if (!$groups){
            $html .= '<div class="no-items">No items below threshold.</div>';
        } else {
            foreach ($groups as $supplier => $items) {
                $html .= '<h2>Supplier: '.htmlspecialchars($supplier).'</h2>';
                $html .= '<table>';
                $html .= '<thead><tr><th>Item Name</th><th>SKU</th><th>Current Stock</th><th>Min Level</th><th>Order Quantity</th></tr></thead>';
                $html .= '<tbody>';
                foreach($items as $it){
                    $html .= '<tr>';
                    $html .= '<td>'.htmlspecialchars($it['name']).'</td>';
                    $html .= '<td>'.htmlspecialchars($it['sku']).'</td>';
                    $html .= '<td>'.$it['current'].'</td>';
                    $html .= '<td>'.$it['min'].'</td>';
                    $html .= '<td><strong>'.$it['order_qty'].'</strong></td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
            }
        }
        
        // Print button
        $html .= '<button class="print-btn no-print" onclick="window.print()">Print / Save as PDF</button>';
        $html .= '</body></html>';

        // Try Dompdf first
        if (class_exists('Dompdf\\Dompdf')) {
            try {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream('suggested_po_'.date('Ymd_His').'.pdf', ['Attachment' => 1]);
                exit;
            } catch (Exception $e) {
                // Fall back to HTML if Dompdf fails
            }
        }
        
        // Fallback: Output HTML with print-friendly styling
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
