<?php
/**
 * Get request details for editing
 */

require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'housekeeping';

$request_id = $_GET['id'] ?? '';

if (empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
    exit();
}

try {
    global $pdo;
    
    // Get request details
    $stmt = $pdo->prepare("
        SELECT ir.*, u.name as requested_by_name
        FROM inventory_requests ir
        JOIN users u ON ir.requested_by = u.id
        WHERE ir.id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit();
    }
    
    // Check permissions - housekeeping can only edit their own requests
    if ($user_role === 'housekeeping' && $request['requested_by'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Access denied. You can only edit your own requests.']);
        exit();
    }
    
    // Get request items
    $stmt = $pdo->prepare("
        SELECT iri.*, ii.item_name as item_name, ii.unit
        FROM inventory_request_items iri
        JOIN inventory_items ii ON iri.item_id = ii.id
        WHERE iri.request_id = ?
    ");
    $stmt->execute([$request_id]);
    $items = $stmt->fetchAll();
    
    $request['items'] = $items;
    
    echo json_encode([
        'success' => true,
        'request' => $request
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
