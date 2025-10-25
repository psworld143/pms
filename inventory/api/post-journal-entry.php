<?php
/**
 * Post Journal Entry
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
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers can post journal entries.']);
    exit();
}

try {
    global $pdo;
    
    $entry_id = $_POST['id'] ?? 0;
    
    if (!$entry_id) {
        echo json_encode(['success' => false, 'message' => 'Journal entry ID is required']);
        exit();
    }
    
    $pdo->beginTransaction();
    
    // Update journal entry status to posted
    $stmt = $pdo->prepare("
        UPDATE inventory_journal_entries 
        SET status = 'posted', posted_at = NOW() 
        WHERE id = ? AND status = 'pending'
    ");
    $stmt->execute([$entry_id]);
    
    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Journal entry not found or already posted']);
        exit();
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Journal entry posted successfully']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error posting journal entry: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
