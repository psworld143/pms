<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers can delete transactions.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : 0;

if (!$transaction_id) {
    echo json_encode(['success' => false, 'message' => 'Missing transaction ID']);
    exit();
}

try {
    global $pdo;
    
    $pdo->beginTransaction();
    
    // Get transaction details before deletion
    $stmt = $pdo->prepare("
        SELECT item_id, transaction_type, quantity 
        FROM inventory_transactions 
        WHERE id = ?
    ");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        exit();
    }
    
    // Delete the transaction
    $stmt = $pdo->prepare("DELETE FROM inventory_transactions WHERE id = ?");
    $stmt->execute([$transaction_id]);
    
    // Reverse the inventory change
    $reverse_quantity = $transaction['transaction_type'] === 'out' ? 
        $transaction['quantity'] : -$transaction['quantity'];
    
    $stmt = $pdo->prepare("
        UPDATE inventory_items 
        SET current_stock = current_stock + ?, last_updated = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$reverse_quantity, $transaction['item_id']]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
    
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
