<?php
/**
 * Export Transaction Report
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
    $transactions = getTransactionReportData();
    $csvContent = generateCSVContent($transactions);
    $filename = 'transaction_report_' . date('Y-m-d_H-i-s') . '.csv';
    
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
    error_log("Error exporting transaction report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get transaction report data
 */
function getTransactionReportData() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                t.id,
                t.transaction_type,
                t.quantity,
                t.unit_price,
                t.total_value,
                t.reason,
                t.created_at,
                i.name as item_name,
                i.sku,
                c.name as category_name,
                u.username as created_by
            FROM inventory_transactions t
            LEFT JOIN inventory_items i ON t.item_id = i.id
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            LEFT JOIN users u ON t.created_by = u.id
            ORDER BY t.created_at DESC
            LIMIT 1000
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting transaction report data: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate CSV content
 */
function generateCSVContent($transactions) {
    $csv = "Transaction ID,Type,Item Name,SKU,Category,Quantity,Unit Price,Total Value,Reason,Created Date,Created By\n";
    
    foreach ($transactions as $transaction) {
        $csv .= sprintf(
            "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
            $transaction['id'],
            $transaction['transaction_type'],
            '"' . str_replace('"', '""', $transaction['item_name']) . '"',
            $transaction['sku'],
            '"' . str_replace('"', '""', $transaction['category_name']) . '"',
            $transaction['quantity'],
            $transaction['unit_price'],
            $transaction['total_value'],
            '"' . str_replace('"', '""', $transaction['reason']) . '"',
            $transaction['created_at'],
            $transaction['created_by']
        );
    }
    
    return $csv;
}
?>
