<?php
/**
 * Sync with Accounting System
 * Hotel PMS Training System - Inventory Module
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    global $pdo;
    
    // Get recent transactions that haven't been synced to accounting
    $stmt = $pdo->query("
        SELECT it.*, ii.item_name, ii.unit_price
        FROM inventory_transactions it
        LEFT JOIN inventory_items ii ON it.item_id = ii.id
        WHERE it.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY it.created_at DESC
        LIMIT 20
    ");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $synced_count = 0;
    
    foreach ($transactions as $transaction) {
        // Create journal entries based on transaction type
        if ($transaction['transaction_type'] === 'in') {
            // Inventory purchase - Debit Inventory, Credit Accounts Payable
            createJournalEntry($pdo, [
                'reference_number' => 'INV-' . $transaction['id'],
                'account_code' => '1200', // Inventory
                'description' => 'Inventory Purchase - ' . $transaction['item_name'],
                'debit_amount' => abs($transaction['quantity']) * ($transaction['unit_cost'] ?? $transaction['unit_price']),
                'credit_amount' => 0,
                'status' => 'posted'
            ]);
            
            createJournalEntry($pdo, [
                'reference_number' => 'INV-' . $transaction['id'],
                'account_code' => '2000', // Accounts Payable
                'description' => 'Accounts Payable - ' . $transaction['item_name'],
                'debit_amount' => 0,
                'credit_amount' => abs($transaction['quantity']) * ($transaction['unit_cost'] ?? $transaction['unit_price']),
                'status' => 'posted'
            ]);
            
        } elseif ($transaction['transaction_type'] === 'out') {
            // Inventory usage - Debit COGS, Credit Inventory
            createJournalEntry($pdo, [
                'reference_number' => 'INV-' . $transaction['id'],
                'account_code' => '5000', // COGS
                'description' => 'COGS - ' . $transaction['item_name'],
                'debit_amount' => abs($transaction['quantity']) * ($transaction['unit_cost'] ?? $transaction['unit_price']),
                'credit_amount' => 0,
                'status' => 'posted'
            ]);
            
            createJournalEntry($pdo, [
                'reference_number' => 'INV-' . $transaction['id'],
                'account_code' => '1200', // Inventory
                'description' => 'Inventory Usage - ' . $transaction['item_name'],
                'debit_amount' => 0,
                'credit_amount' => abs($transaction['quantity']) * ($transaction['unit_cost'] ?? $transaction['unit_price']),
                'status' => 'posted'
            ]);
        }
        
        $synced_count++;
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully synced {$synced_count} transactions with accounting system",
        'synced_count' => $synced_count
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function createJournalEntry($pdo, $entry) {
    $sql = "
        INSERT INTO inventory_journal_entries 
        (reference_number, account_code, description, debit_amount, credit_amount, status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $entry['reference_number'],
        $entry['account_code'],
        $entry['description'],
        $entry['debit_amount'],
        $entry['credit_amount'],
        $entry['status']
    ]);
}
?>
