<?php
/**
 * Save Account Mapping
 * Hotel PMS Training System - Inventory Module
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers can save account mappings.']);
    exit();
}

try {
    global $pdo;
    
    // Get mapping data
    $inventory = $_POST['inventory'] ?? [];
    $expense = $_POST['expense'] ?? [];
    $liability = $_POST['liability'] ?? [];
    
    // Create or update account mapping table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inventory_account_mapping (
            id INT AUTO_INCREMENT PRIMARY KEY,
            account_type VARCHAR(50) NOT NULL,
            account_name VARCHAR(100) NOT NULL,
            account_code VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_mapping (account_type, account_name)
        )
    ");
    
    $pdo->beginTransaction();
    
    // Clear existing mappings
    $pdo->exec("DELETE FROM inventory_account_mapping");
    
    // Insert inventory accounts
    if (!empty($inventory)) {
        $stmt = $pdo->prepare("
            INSERT INTO inventory_account_mapping (account_type, account_name, account_code) 
            VALUES ('inventory', ?, ?)
        ");
        
        if (isset($inventory['raw_materials'])) {
            $stmt->execute(['Raw Materials', $inventory['raw_materials']]);
        }
        if (isset($inventory['finished_goods'])) {
            $stmt->execute(['Finished Goods', $inventory['finished_goods']]);
        }
        if (isset($inventory['supplies'])) {
            $stmt->execute(['Supplies', $inventory['supplies']]);
        }
    }
    
    // Insert expense accounts
    if (!empty($expense)) {
        $stmt = $pdo->prepare("
            INSERT INTO inventory_account_mapping (account_type, account_name, account_code) 
            VALUES ('expense', ?, ?)
        ");
        
        if (isset($expense['cogs'])) {
            $stmt->execute(['Cost of Goods Sold', $expense['cogs']]);
        }
        if (isset($expense['supplies_expense'])) {
            $stmt->execute(['Supplies Expense', $expense['supplies_expense']]);
        }
        if (isset($expense['waste_expense'])) {
            $stmt->execute(['Waste Expense', $expense['waste_expense']]);
        }
    }
    
    // Insert liability accounts
    if (!empty($liability)) {
        $stmt = $pdo->prepare("
            INSERT INTO inventory_account_mapping (account_type, account_name, account_code) 
            VALUES ('liability', ?, ?)
        ");
        
        if (isset($liability['accounts_payable'])) {
            $stmt->execute(['Accounts Payable', $liability['accounts_payable']]);
        }
        if (isset($liability['accrued_expenses'])) {
            $stmt->execute(['Accrued Expenses', $liability['accrued_expenses']]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Account mapping saved successfully']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error saving account mapping: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
