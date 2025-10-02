<?php
/**
 * Export Inventory Report
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $items = getInventoryReportData();
    $csvContent = generateInventoryCSVContent($items);
    $filename = 'inventory_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    // Save CSV file
    $filepath = '../exports/' . $filename;
    if (!file_exists('../exports/')) {
        mkdir('../exports/', 0755, true);
    }
    
    file_put_contents($filepath, $csvContent);
    
    echo json_encode([
        'success' => true,
        'download_url' => 'exports/' . $filename,
        'filename' => $filename
    ]);
    
} catch (Exception $e) {
    error_log("Error exporting inventory report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory report data
 */
function getInventoryReportData() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                i.id,
                i.name,
                i.sku,
                i.description,
                i.quantity,
                i.minimum_stock,
                i.maximum_stock,
                i.unit_price,
                i.cost_price,
                i.supplier,
                i.location,
                i.unit,
                i.status,
                i.created_at,
                c.name as category_name,
                CASE 
                    WHEN i.quantity = 0 THEN 'Out of Stock'
                    WHEN i.quantity <= i.minimum_stock THEN 'Low Stock'
                    ELSE 'In Stock'
                END as stock_status
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE i.status = 'active'
            ORDER BY c.name, i.name ASC
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting inventory report data: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate CSV content
 */
function generateInventoryCSVContent($items) {
    $csv = "Item ID,Name,SKU,Description,Category,Quantity,Min Stock,Max Stock,Unit Price,Cost Price,Supplier,Location,Unit,Stock Status,Created Date\n";
    
    foreach ($items as $item) {
        $csv .= sprintf(
            "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
            $item['id'],
            '"' . str_replace('"', '""', $item['name']) . '"',
            $item['sku'],
            '"' . str_replace('"', '""', $item['description']) . '"',
            '"' . str_replace('"', '""', $item['category_name']) . '"',
            $item['quantity'],
            $item['minimum_stock'],
            $item['maximum_stock'],
            $item['unit_price'],
            $item['cost_price'],
            '"' . str_replace('"', '""', $item['supplier']) . '"',
            '"' . str_replace('"', '""', $item['location']) . '"',
            $item['unit'],
            $item['stock_status'],
            $item['created_at']
        );
    }
    
    return $csv;
}
?>
