<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if (strtolower($user_role) !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers can view pending requests.']);
    exit();
}

try {
    global $pdo;
    
    // Build schema-tolerant projection
    $rqCols = $pdo->query("SHOW COLUMNS FROM inventory_requests")->fetchAll(PDO::FETCH_COLUMN, 0);
    $rqHas = array_flip($rqCols);
    $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
    $userNameCol = in_array('username', $userCols, true) ? 'username' : (in_array('name', $userCols, true) ? 'name' : 'id');

    $itemLabelExpr = isset($rqHas['item_name']) ? 'ir.item_name' : (
        (isset($rqHas['item_id']) ? (
            // derive from inventory_items name columns
            (function() use ($pdo) {
                $itemCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
                if (in_array('item_name', $itemCols, true)) return 'ii.item_name';
                if (in_array('name', $itemCols, true)) return 'ii.name';
                return "CONCAT('Item ', ii.id)";
            })()
        ) : "'Unknown'" )
    );

    $qtyExpr = isset($rqHas['quantity_requested']) ? 'ir.quantity_requested' : (isset($rqHas['quantity']) ? 'ir.quantity' : '0');
    $requestedAtExpr = isset($rqHas['requested_at']) ? 'ir.requested_at' : (isset($rqHas['created_at']) ? 'ir.created_at' : 'NOW()');

    $joinItems = isset($rqHas['item_id']) ? 'LEFT JOIN inventory_items ii ON ir.item_id = ii.id' : '';

    $sql = "SELECT ir.id, $itemLabelExpr AS item_name, $qtyExpr AS quantity_requested, ir.department, ir.priority, ir.status, $requestedAtExpr AS requested_at, ir.notes, u.$userNameCol AS requested_by_name FROM inventory_requests ir LEFT JOIN users u ON ir.requested_by = u.id $joinItems WHERE ir.status = 'pending' ORDER BY CASE ir.priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 END, $requestedAtExpr ASC";
    $stmt = $pdo->query($sql);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'requests' => $requests]);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
