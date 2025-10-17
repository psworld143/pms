<?php
/**
 * Get Journal Entry Details
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
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers can view journal entry details.']);
    exit();
}

try {
    global $pdo;
    
    $entry_id = $_GET['id'] ?? 0;
    
    if (!$entry_id) {
        echo json_encode(['success' => false, 'message' => 'Journal entry ID is required']);
        exit();
    }
    
    // Get journal entry details
    $stmt = $pdo->prepare("
        SELECT 
            id,
            reference_number,
            account_code,
            description,
            debit_amount,
            credit_amount,
            status,
            created_at
        FROM inventory_journal_entries 
        WHERE id = ?
    ");
    $stmt->execute([$entry_id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$entry) {
        echo json_encode(['success' => false, 'message' => 'Journal entry not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'entry' => $entry
    ]);
    
} catch (Exception $e) {
    error_log("Error getting journal entry details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
