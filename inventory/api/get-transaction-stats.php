<?php
/**
 * Get Transaction Statistics
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
    $stats = getTransactionStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error getting transaction stats: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get transaction statistics
 */
function getTransactionStats() {
    global $pdo;
    
    try {
        // Get total transaction value (last 30 days)
        $stmt = $pdo->query("
            SELECT SUM(total_value) as total_value
            FROM inventory_transactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch();
        $total_value = $result['total_value'] ?? 0;
        
        // Get between locations transfers count
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM inventory_transactions
            WHERE transaction_type = 'transfer' 
            AND reason LIKE '%location%'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $between_locations = $stmt->fetch()['count'] ?? 0;
        
        // Get external transfers count
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM inventory_transactions
            WHERE transaction_type = 'transfer' 
            AND reason LIKE '%external%'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $external_transfers = $stmt->fetch()['count'] ?? 0;
        
        // Get loan transfers count
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM inventory_transactions
            WHERE transaction_type = 'transfer' 
            AND reason LIKE '%loan%'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $loan_transfers = $stmt->fetch()['count'] ?? 0;
        
        return [
            'total_value' => (float)$total_value,
            'between_locations' => (int)$between_locations,
            'external_transfers' => (int)$external_transfers,
            'loan_transfers' => (int)$loan_transfers
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting transaction stats: " . $e->getMessage());
        return [
            'total_value' => 0,
            'between_locations' => 0,
            'external_transfers' => 0,
            'loan_transfers' => 0
        ];
    }
}
?>
