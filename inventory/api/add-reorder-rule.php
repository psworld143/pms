<?php
/**
 * Add Reorder Rule
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
    $item_id = $_POST['item_id'] ?? 0;
    $reorder_point = $_POST['reorder_point'] ?? 0;
    $reorder_quantity = $_POST['reorder_quantity'] ?? 0;
    $lead_time_days = $_POST['lead_time_days'] ?? 7;
    $supplier_id = $_POST['supplier_id'] ?? null;
    $auto_generate_po = $_POST['auto_generate_po'] ?? 0;
    
    if ($item_id <= 0 || $reorder_point < 0 || $reorder_quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }
    
    $result = addReorderRule($item_id, $reorder_point, $reorder_quantity, $lead_time_days, $supplier_id, $auto_generate_po);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reorder rule added successfully',
        'rule_id' => $result['rule_id']
    ]);
    
} catch (Exception $e) {
    error_log("Error adding reorder rule: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Add reorder rule
 */
function addReorderRule($item_id, $reorder_point, $reorder_quantity, $lead_time_days, $supplier_id, $auto_generate_po) {
    global $pdo;
    
    try {
        // Check if reorder_rules table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'reorder_rules'");
        $table_exists = $stmt->rowCount() > 0;
        
        if (!$table_exists) {
            // Create table if it doesn't exist
            $pdo->exec("
                CREATE TABLE reorder_rules (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    item_id INT NOT NULL,
                    reorder_point INT NOT NULL,
                    reorder_quantity INT NOT NULL,
                    lead_time_days INT NOT NULL,
                    supplier_id INT NULL,
                    auto_generate_po TINYINT(1) DEFAULT 0,
                    active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
                )
            ");
        }
        
        // Insert reorder rule
        $stmt = $pdo->prepare("
            INSERT INTO reorder_rules (
                item_id, 
                reorder_point, 
                reorder_quantity, 
                lead_time_days, 
                supplier_id, 
                auto_generate_po
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $item_id,
            $reorder_point,
            $reorder_quantity,
            $lead_time_days,
            $supplier_id,
            $auto_generate_po
        ]);
        
        $rule_id = $pdo->lastInsertId();
        
        return ['rule_id' => $rule_id];
        
    } catch (PDOException $e) {
        error_log("Error adding reorder rule: " . $e->getMessage());
        throw $e;
    }
}
?>
