<?php
/**
 * Add approval_notes column to supply_requests and inventory_requests tables
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Add approval_notes column to supply_requests if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM supply_requests LIKE 'approval_notes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE supply_requests ADD COLUMN approval_notes TEXT NULL AFTER notes");
    }
    
    // Add approval_notes column to inventory_requests if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_requests LIKE 'approval_notes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inventory_requests ADD COLUMN approval_notes TEXT NULL AFTER notes");
    }
    
    echo json_encode(['success' => true, 'message' => 'Approval notes columns added successfully']);
    
} catch (PDOException $e) {
    error_log("Database error in add-approval-notes-column.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
