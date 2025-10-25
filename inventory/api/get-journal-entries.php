<?php
/**
 * Get Journal Entries
 * Hotel PMS Training System - Inventory Module
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$status_filter = $_GET['status'] ?? '';

try {
    global $pdo;
    
    // Create journal entries table if it doesn't exist
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS inventory_journal_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference_number VARCHAR(50),
            account_code VARCHAR(20),
            description TEXT,
            debit_amount DECIMAL(15,2) DEFAULT 0,
            credit_amount DECIMAL(15,2) DEFAULT 0,
            status ENUM('pending', 'posted', 'reversed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($create_table_sql);
    
    // Build query with status filter
    $sql = "SELECT * FROM inventory_journal_entries";
    $params = [];
    
    if ($status_filter) {
        $sql .= " WHERE status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no entries exist, create some sample data
    if (empty($entries)) {
        $sample_entries = [
            [
                'reference_number' => 'INV-001',
                'account_code' => '1200',
                'description' => 'Inventory Purchase - Bath Towels',
                'debit_amount' => 5000.00,
                'credit_amount' => 0.00,
                'status' => 'posted'
            ],
            [
                'reference_number' => 'INV-002',
                'account_code' => '5000',
                'description' => 'COGS - Room Service Supplies',
                'debit_amount' => 0.00,
                'credit_amount' => 2500.00,
                'status' => 'posted'
            ],
            [
                'reference_number' => 'INV-003',
                'account_code' => '2000',
                'description' => 'Accounts Payable - Supplier Invoice',
                'debit_amount' => 0.00,
                'credit_amount' => 5000.00,
                'status' => 'pending'
            ],
            [
                'reference_number' => 'INV-004',
                'account_code' => '5100',
                'description' => 'Supplies Expense - Cleaning Materials',
                'debit_amount' => 1200.00,
                'credit_amount' => 0.00,
                'status' => 'posted'
            ]
        ];
        
        $insert_sql = "
            INSERT INTO inventory_journal_entries 
            (reference_number, account_code, description, debit_amount, credit_amount, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $insert_stmt = $pdo->prepare($insert_sql);
        foreach ($sample_entries as $entry) {
            $insert_stmt->execute([
                $entry['reference_number'],
                $entry['account_code'],
                $entry['description'],
                $entry['debit_amount'],
                $entry['credit_amount'],
                $entry['status']
            ]);
        }
        
        // Fetch the newly created entries
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
