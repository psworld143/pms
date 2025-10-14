<?php
/**
 * Export Inventory Report
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

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
    $result = exportInventoryReport();
    
    echo json_encode([
        'success' => true,
        'message' => 'Inventory report exported successfully',
        'download_url' => $result['download_url']
    ]);
    
} catch (Exception $e) {
    error_log("Error exporting inventory report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Export inventory report
 */
function exportInventoryReport() {
    global $pdo;
    
    try {
        // Get inventory data
        $stmt = $pdo->query("
            SELECT 
                ii.item_name,
                ic.name as category_name,
                ii.sku,
                ii.description,
                ii.current_stock,
                ii.minimum_stock,
                ii.unit_price,
                (ii.current_stock * ii.unit_price) as total_value,
                CASE 
                    WHEN ii.current_stock = 0 THEN 'Out of Stock'
                    WHEN ii.current_stock <= ii.minimum_stock THEN 'Low Stock'
                    ELSE 'In Stock'
                END as stock_status,
                ii.created_at,
                ii.last_updated
            FROM inventory_items ii
            LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
            ORDER BY ii.item_name ASC
        ");
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create CSV content
        $csv_content = "Item Name,Category,SKU,Description,Current Stock,Minimum Stock,Unit Price,Total Value,Stock Status,Created At,Last Updated\n";
        
        foreach ($items as $item) {
            $csv_content .= '"' . str_replace('"', '""', $item['item_name']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $item['category_name']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $item['sku']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $item['description']) . '",';
            $csv_content .= $item['current_stock'] . ',';
            $csv_content .= $item['minimum_stock'] . ',';
            $csv_content .= $item['unit_price'] . ',';
            $csv_content .= $item['total_value'] . ',';
            $csv_content .= '"' . $item['stock_status'] . '",';
            $csv_content .= '"' . $item['created_at'] . '",';
            $csv_content .= '"' . $item['last_updated'] . '"';
            $csv_content .= "\n";
        }
        
        // Generate filename
        $filename = 'inventory_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Save to temp directory
        $temp_dir = __DIR__ . '/../../temp/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        
        $filepath = $temp_dir . $filename;
        file_put_contents($filepath, $csv_content);
        
        return [
            'download_url' => '../../temp/' . $filename
        ];
        
    } catch (PDOException $e) {
        error_log("Error exporting inventory report: " . $e->getMessage());
        throw $e;
    }
}
?>