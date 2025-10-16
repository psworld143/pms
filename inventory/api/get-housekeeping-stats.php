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
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $stats = [
        'usage_reports' => 0,
        'pending_requests' => 0,
        'approved_requests' => 0,
        'low_stock_items' => 0
    ];
    
    // Count usage reports submitted by this user
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM inventory_usage_reports 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['usage_reports'] = $result['count'] ?? 0;
    
    // Count pending requests by this user
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM inventory_requests 
        WHERE requested_by = ? AND status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending_requests'] = $result['count'] ?? 0;
    
    // Count approved requests by this user
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM inventory_requests 
        WHERE requested_by = ? AND status = 'approved'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['approved_requests'] = $result['count'] ?? 0;
    
    // Count low stock items (items with current_stock <= reorder_level)
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM inventory_items 
        WHERE current_stock <= reorder_level AND current_stock > 0
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['low_stock_items'] = $result['count'] ?? 0;
    
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
