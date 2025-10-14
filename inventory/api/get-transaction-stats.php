<?php
/**
 * Get Transaction Statistics
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
    $stats = getTransactionStatistics();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error getting transaction statistics: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get transaction statistics
 */
function getTransactionStatistics() {
    global $pdo;
    
    try {
        // Get total transactions count
        $stmt = $pdo->query("SELECT COUNT(*) as total_transactions FROM inventory_transactions");
        $total_transactions = $stmt->fetch()['total_transactions'];
        
        // Get stock in count
        $stmt = $pdo->query("
            SELECT COUNT(*) as stock_in 
            FROM inventory_transactions 
            WHERE transaction_type = 'in' OR quantity > 0
        ");
        $stock_in = $stmt->fetch()['stock_in'];
        
        // Get stock out count
        $stmt = $pdo->query("
            SELECT COUNT(*) as stock_out 
            FROM inventory_transactions 
            WHERE transaction_type = 'out' OR quantity < 0
        ");
        $stock_out = $stmt->fetch()['stock_out'];
        
        // Get total transaction value
        $stmt = $pdo->query("
            SELECT COALESCE(SUM(ABS(quantity) * COALESCE(unit_price, 0)), 0) as total_value 
            FROM inventory_transactions
        ");
        $total_value = $stmt->fetch()['total_value'];
        
        // Get transfer counts
        $stmt = $pdo->query("
            SELECT COUNT(*) as between_locations 
            FROM inventory_transactions 
            WHERE transaction_type = 'transfer' AND reason LIKE '%location%'
        ");
        $between_locations = $stmt->fetch()['between_locations'];
        
        $stmt = $pdo->query("
            SELECT COUNT(*) as external_transfers 
            FROM inventory_transactions 
            WHERE transaction_type = 'transfer' AND reason LIKE '%external%'
        ");
        $external_transfers = $stmt->fetch()['external_transfers'];
        
        $stmt = $pdo->query("
            SELECT COUNT(*) as loan_transfers 
            FROM inventory_transactions 
            WHERE transaction_type = 'transfer' AND reason LIKE '%loan%'
        ");
        $loan_transfers = $stmt->fetch()['loan_transfers'];
        
        return [
            'total_transactions' => (int)$total_transactions,
            'stock_in' => (int)$stock_in,
            'stock_out' => (int)$stock_out,
            'total_value' => (float)$total_value,
            'between_locations' => (int)$between_locations,
            'external_transfers' => (int)$external_transfers,
            'loan_transfers' => (int)$loan_transfers
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting transaction statistics: " . $e->getMessage());
        return [
            'total_transactions' => 0,
            'stock_in' => 0,
            'stock_out' => 0,
            'total_value' => 0,
            'between_locations' => 0,
            'external_transfers' => 0,
            'loan_transfers' => 0
        ];
    }
}
?>