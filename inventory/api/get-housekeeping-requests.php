<?php
require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if (strtolower($user_role) !== 'housekeeping') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    // Get usage reports and requests for this housekeeping user
    $requests = [];
    
    // Dynamically select item name based on schema
    $nameCols = $pdo->query("SHOW COLUMNS FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    $nameExpr = in_array('item_name', $nameCols, true) ? 'ii.item_name' : (in_array('name', $nameCols, true) ? 'ii.name' : "CONCAT('Item ', ii.id)");

    // Get usage reports
    $stmt = $pdo->prepare("SELECT 'usage' AS type, iur.id, COALESCE(iur.created_at, iur.date_used) AS created_at, $nameExpr AS item_name, iur.quantity, iur.room, 'completed' AS status, iur.notes FROM inventory_usage_reports iur LEFT JOIN inventory_items ii ON iur.item_id = ii.id WHERE iur.user_id = ? ORDER BY COALESCE(iur.created_at, iur.date_used) DESC LIMIT 50");
    $stmt->execute([$user_id]);
    $usage_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get supply requests (schema tolerant)
    $rqCols = $pdo->query("SHOW COLUMNS FROM inventory_requests")->fetchAll(PDO::FETCH_COLUMN, 0);
    $rqHas = array_flip($rqCols);
    $hasItemId = isset($rqHas['item_id']);
    $itemLabel = isset($rqHas['item_name']) ? 'ir.item_name' : ($hasItemId ? $nameExpr : "'Unknown'");
    $qtyExpr = isset($rqHas['quantity_requested']) ? 'ir.quantity_requested' : (isset($rqHas['quantity']) ? 'ir.quantity' : '0');
    $dateExpr = isset($rqHas['requested_at']) ? 'ir.requested_at' : (isset($rqHas['created_at']) ? 'ir.created_at' : 'NOW()');
    $joinItems = $hasItemId ? 'LEFT JOIN inventory_items ii ON ir.item_id = ii.id' : '';
    $stmt = $pdo->prepare("SELECT 'request' AS type, ir.id, $dateExpr AS created_at, $itemLabel AS item_name, $qtyExpr AS quantity, NULL AS room, ir.status, ir.notes FROM inventory_requests ir $joinItems WHERE ir.requested_by = ? ORDER BY $dateExpr DESC LIMIT 50");
    $stmt->execute([$user_id]);
    $supply_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine and sort by date
    $all_requests = array_merge($usage_reports, $supply_requests);
    usort($all_requests, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo json_encode(['success' => true, 'requests' => $all_requests]);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
