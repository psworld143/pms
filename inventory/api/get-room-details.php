<?php
/**
 * Get detailed room information with inventory items
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

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
    global $pdo;
    
    // Get room details from hotel_rooms table
    $stmt = $pdo->prepare("
        SELECT r.*, 
               COUNT(ri.id) as total_items,
               CASE 
                   WHEN COUNT(ri.id) = 0 THEN 'unknown'
                   WHEN COUNT(CASE WHEN ri.quantity_current < ri.par_level THEN 1 END) = 0 THEN 'fully_stocked'
                   WHEN COUNT(CASE WHEN ri.quantity_current = 0 THEN 1 END) > 0 THEN 'critical_stock'
                   ELSE 'needs_restocking'
               END as stock_status
        FROM hotel_rooms r
        LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
        WHERE r.id = ?
        GROUP BY r.id
    ");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    // The max_occupancy field already exists in hotel_rooms table
    // No need to map from capacity field
    
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

    // Get room inventory items (using schema-adaptive fields)
    $sqlItems = "
        SELECT ri.*, 
               $nameExpr AS item_name,
               $skuExpr,
               $unitExpr,
               ri.quantity_allocated,
               ri.quantity_current,
               ri.par_level,
               ri.updated_at as last_updated
        FROM room_inventory_items ri
        JOIN inventory_items ii ON ri.item_id = ii.id
        WHERE ri.room_id = ?
        ORDER BY $orderBy
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
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>