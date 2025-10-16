<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'housekeeping') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only housekeeping staff can acknowledge receipts.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'Missing request ID']);
    exit();
}

try {
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    // Check if request exists and belongs to this user
    $stmt = $pdo->prepare("
        SELECT id, status, item_name, quantity_requested 
        FROM inventory_requests 
        WHERE id = ? AND requested_by = ? AND status = 'approved'
    ");
    $stmt->execute([$request_id, $user_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or not approved']);
        exit();
    }
    
    $pdo->beginTransaction();
    
    // Update request status to completed
    $stmt = $pdo->prepare("
        UPDATE inventory_requests 
        SET status = 'completed', processed_at = NOW(), notes = CONCAT(COALESCE(notes, ''), '\nAcknowledged by housekeeping on ', NOW())
        WHERE id = ?
    ");
    $stmt->execute([$request_id]);
    
    // Find the item ID
    $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE item_name = ? LIMIT 1");
    $stmt->execute([$request['item_name']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        // Create transaction for the approved request
        $stmt = $pdo->prepare("
            INSERT INTO inventory_transactions (
                item_id,
                transaction_type,
                quantity,
                reason,
                performed_by,
                created_at
            ) VALUES (?, 'in', ?, ?, ?, NOW())
        ");
        $reason = "Approved supply request - Acknowledged by housekeeping";
        $stmt->execute([$item['id'], $request['quantity_requested'], $reason, $user_id]);
        
        // Update inventory stock
        $stmt = $pdo->prepare("
            UPDATE inventory_items 
            SET current_stock = current_stock + ?, last_updated = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$request['quantity_requested'], $item['id']]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Receipt acknowledged successfully']);
    
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
