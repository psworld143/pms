<?php
/**
 * Get detailed room information with inventory items
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$room_id = $_GET['room_id'] ?? '';

if (empty($room_id)) {
    echo json_encode(['success' => false, 'message' => 'Room ID required']);
    exit();
}

try {
    
    // Get room details from rooms table (including capacity from booking system)
    $stmt = $pdo->prepare("
        SELECT r.*, 
               r.capacity,
               COUNT(ri.id) as total_items,
               CASE 
                   WHEN COUNT(ri.id) = 0 THEN 'unknown'
                   WHEN COUNT(CASE WHEN ri.quantity_current < ri.par_level THEN 1 END) = 0 THEN 'fully_stocked'
                   WHEN COUNT(CASE WHEN ri.quantity_current = 0 THEN 1 END) > 0 THEN 'critical_stock'
                   ELSE 'needs_restocking'
               END as stock_status
        FROM rooms r
        LEFT JOIN room_inventory ri ON r.id = ri.room_id
        WHERE r.id = ?
        GROUP BY r.id
    ");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit();
    }
    
    // Build schema-adaptive columns for inventory_items
    $colsStmt = $pdo->query("SHOW COLUMNS FROM inventory_items");
    $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemName = in_array('item_name', $cols, true);
    $hasName = in_array('name', $cols, true);
    $hasSku = in_array('sku', $cols, true);
    $hasUnit = in_array('unit', $cols, true);

    $nameExpr = $hasItemName ? 'ii.item_name' : ($hasName ? 'ii.name' : 'ii.id');
    $skuExpr = $hasSku ? 'ii.sku' : "'' AS sku";
    $unitExpr = $hasUnit ? 'ii.unit' : "'' AS unit";
    $orderBy = $hasItemName ? 'ii.item_name' : ($hasName ? 'ii.name' : 'ii.id');

    // Get room inventory items (using schema-adaptive approach)
    $sqlItems = "
        SELECT ri.*, 
               {$nameExpr} as item_name,
               {$skuExpr} as sku,
               {$unitExpr} as unit,
               ri.quantity_allocated,
               ri.quantity_current,
               ri.par_level,
               ri.last_updated,
               ii.unit_price,
               ii.current_stock as main_stock,
               ii.description,
               ii.status as item_status
        FROM room_inventory ri
        JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE ri.room_id = ?
        ORDER BY {$orderBy}
    ";
    $stmt = $pdo->prepare($sqlItems);
    $stmt->execute([$room_id]);
    $inventory_items = $stmt->fetchAll();
    
    $room['inventory_items'] = $inventory_items;
    
    echo json_encode([
        'success' => true,
        'room' => $room
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-room-details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>