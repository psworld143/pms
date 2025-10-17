<?php
/**
 * Export Transaction Report
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
    $result = exportTransactionReport();
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaction report exported successfully',
        'download_url' => $result['download_url']
    ]);
    
} catch (Exception $e) {
    error_log("Error exporting transaction report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Export transaction report
 */
function exportTransactionReport() {
    global $pdo;
    
    try {
        // Get transaction data
        $stmt = $pdo->query("
            SELECT 
                it.created_at as transaction_date,
                it.transaction_type,
                ii.item_name,
                it.quantity,
                it.unit_price,
                (it.quantity * it.unit_price) as total_value,
                it.reason,
                u.name as performed_by_user
            FROM inventory_transactions it
            JOIN inventory_items ii ON it.item_id = ii.id
            LEFT JOIN users u ON it.performed_by = u.id
            ORDER BY it.created_at DESC
        ");
        
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create CSV content
        $csv_content = "Transaction Date,Type,Item Name,Quantity,Unit Price,Total Value,Reason,Performed By\n";
        
        foreach ($transactions as $transaction) {
            $csv_content .= '"' . $transaction['transaction_date'] . '",';
            $csv_content .= '"' . $transaction['transaction_type'] . '",';
            $csv_content .= '"' . str_replace('"', '""', $transaction['item_name']) . '",';
            $csv_content .= $transaction['quantity'] . ',';
            $csv_content .= $transaction['unit_price'] . ',';
            $csv_content .= $transaction['total_value'] . ',';
            $csv_content .= '"' . str_replace('"', '""', $transaction['reason']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $transaction['performed_by_user']) . '"';
            $csv_content .= "\n";
        }
        
        // Generate filename
        $filename = 'transaction_report_' . date('Y-m-d_H-i-s') . '.csv';
        
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
        error_log("Error exporting transaction report: " . $e->getMessage());
        throw $e;
    }
}
?>