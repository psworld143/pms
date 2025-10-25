<?php
/**
 * Get Inventory Transactions
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
    $transaction_type = $_GET['transaction_type'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    
    $transactions = getInventoryTransactions($transaction_type, $date_from, $date_to, $limit);
    
    echo json_encode([
        'success' => true,
        'transactions' => $transactions
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory transactions: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory transactions
 */
function getInventoryTransactions($transaction_type, $date_from, $date_to, $limit) {
    global $pdo;
    
    try {
        $sql = "
            SELECT 
                it.id,
                it.transaction_type,
                ii.item_name,
                it.quantity,
                it.unit_price,
                (it.quantity * it.unit_price) as total_value,
                it.reason,
                u.name as performed_by,
                it.created_at
            FROM inventory_transactions it
            JOIN inventory_items ii ON it.item_id = ii.id
            LEFT JOIN users u ON it.performed_by = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($transaction_type)) {
            $sql .= " AND it.transaction_type = ?";
            $params[] = $transaction_type;
        }
        
        if (!empty($date_from)) {
            $sql .= " AND DATE(it.created_at) >= ?";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $sql .= " AND DATE(it.created_at) <= ?";
            $params[] = $date_to;
        }
        
        $sql .= " ORDER BY it.created_at DESC LIMIT ?";
        $params[] = (int)$limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting inventory transactions: " . $e->getMessage());
        return [];
    }
}
?>
