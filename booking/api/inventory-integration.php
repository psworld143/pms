<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Booking-Inventory Integration API
 * Connects booking system with inventory management
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access',
            'redirect' => '../../login.php'
        ]);
        exit(); }
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $user_role = $_SESSION['user_role'];

    switch ($action) {
        case 'get_room_inventory':
            getRoomInventory();
            break;
        case 'update_room_inventory':
            updateRoomInventory();
            break;
        case 'get_inventory_status':
            getInventoryStatus();
            break;
        case 'record_inventory_usage':
            recordInventoryUsage();
            break;
        case 'get_low_stock_alerts':
            getLowStockAlerts();
            break;
        case 'sync_booking_inventory':
            syncBookingInventory();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]); }
} catch (Exception $e) {
    error_log('Inventory Integration API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]); }
/**
 * Get room inventory for a specific room
 */
function getRoomInventory() {
    global $pdo;
    
    $room_id = $_GET['room_id'] ?? 0;
    
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
        return; }
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ri.id,
                ri.room_id,
                ri.item_id,
                ri.quantity_allocated,
                ri.quantity_current,
                ri.par_level,
                ri.last_restocked,
                ri.last_audited,
                ri.notes,
                ii.item_name,
                ii.sku,
                ii.unit,
                ii.current_stock as main_stock,
                ii.minimum_stock,
                ii.status as item_status
            FROM room_inventory ri
            JOIN inventory_items ii ON ri.item_id = ii.id
            WHERE ri.room_id = ?
            ORDER BY ii.item_name
        ");
        
        $stmt->execute([$room_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'room_id' => $room_id,
            'items' => $items
        ]);
        
    } catch (PDOException $e) {
        error_log('Error getting room inventory: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']); }
}

/**
 * Update room inventory
 */
function updateRoomInventory() {
    global $pdo;
    
    $room_id = $_POST['room_id'] ?? 0;
    $item_id = $_POST['item_id'] ?? 0;
    $quantity_current = $_POST['quantity_current'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    if ($room_id <= 0 || $item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return; }
    try {
        $pdo->beginTransaction();
        
        // Get current quantity
        $stmt = $pdo->prepare("
            SELECT quantity_current FROM room_inventory 
            WHERE room_id = ? AND item_id = ?
        ");
        $stmt->execute([$room_id, $item_id]);
        $current = $stmt->fetch();
        
        if (!$current) {
            throw new Exception('Error occurred'); }
        $quantity_before = $current['quantity_current'];
        $quantity_change = $quantity_current - $quantity_before;
        
        // Update room inventory
        $stmt = $pdo->prepare("
            UPDATE room_inventory 
            SET quantity_current = ?, notes = ?, last_audited = NOW()
            WHERE room_id = ? AND item_id = ?
        ");
        $stmt->execute([$quantity_current, $notes, $room_id, $item_id]);
        
        // Record transaction
        $stmt = $pdo->prepare("
            INSERT INTO room_inventory_transactions (
                room_id, item_id, transaction_type, quantity_change, 
                quantity_before, quantity_after, reason, user_id, notes
            ) VALUES (?, ?, 'adjustment', ?, ?, ?, 'Manual update', ?, ?)
        ");
        $stmt->execute([
            $room_id, $item_id, $quantity_change, 
            $quantity_before, $quantity_current, 
            $_SESSION['user_id'], $notes
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Room inventory updated successfully'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Error updating room inventory: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
}

/**
 * Get inventory status for dashboard
 */
function getInventoryStatus() {
    global $pdo;
    
    try {
        // Get low stock items
        $stmt = $pdo->query("
            SELECT COUNT(*) as count 
            FROM inventory_items 
            WHERE current_stock <= minimum_stock AND status = 'active'
        ");
        $low_stock_count = $stmt->fetch()['count'];
        
        // Get total inventory value
        $stmt = $pdo->query("
            SELECT SUM(current_stock * cost_price) as total_value 
            FROM inventory_items 
            WHERE status = 'active'
        ");
        $total_value = $stmt->fetch()['total_value'] ?? 0;
        
        // Get rooms needing restocking
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT ri.room_id) as count
            FROM room_inventory ri
            JOIN inventory_items ii ON ri.item_id = ii.id
            WHERE ri.quantity_current < ri.par_level
        ");
        $rooms_needing_restock = $stmt->fetch()['count'];
        
        echo json_encode([
            'success' => true,
            'low_stock_items' => $low_stock_count,
            'total_inventory_value' => number_format($total_value, 2),
            'rooms_needing_restock' => $rooms_needing_restock
        ]);
        
    } catch (PDOException $e) {
        error_log('Error getting inventory status: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']); }
}

/**
 * Record inventory usage (for housekeeping/frontdesk)
 */
function recordInventoryUsage() {
    global $pdo;
    
    $room_id = $_POST['room_id'] ?? 0;
    $item_id = $_POST['item_id'] ?? 0;
    $quantity_used = $_POST['quantity_used'] ?? 0;
    $reason = $_POST['reason'] ?? 'Room usage';
    
    if ($room_id <= 0 || $item_id <= 0 || $quantity_used <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return; }
    try {
        $pdo->beginTransaction();
        
        // Get current room inventory
        $stmt = $pdo->prepare("
            SELECT quantity_current FROM room_inventory 
            WHERE room_id = ? AND item_id = ?
        ");
        $stmt->execute([$room_id, $item_id]);
        $current = $stmt->fetch();
        
        if (!$current) {
            throw new Exception('Error occurred'); }
        $quantity_before = $current['quantity_current'];
        $quantity_after = max(0, $quantity_before - $quantity_used);
        
        // Update room inventory
        $stmt = $pdo->prepare("
            UPDATE room_inventory 
            SET quantity_current = ?, last_audited = NOW()
            WHERE room_id = ? AND item_id = ?
        ");
        $stmt->execute([$quantity_after, $room_id, $item_id]);
        
        // Record transaction
        $stmt = $pdo->prepare("
            INSERT INTO room_inventory_transactions (
                room_id, item_id, transaction_type, quantity_change, 
                quantity_before, quantity_after, reason, user_id, notes
            ) VALUES (?, ?, 'usage', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $room_id, $item_id, -$quantity_used, 
            $quantity_before, $quantity_after, 
            $reason, $_SESSION['user_id'], "Used by " . $_SESSION['user_role']
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Inventory usage recorded successfully',
            'quantity_remaining' => $quantity_after
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Error recording inventory usage: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
}

/**
 * Get low stock alerts
 */
function getLowStockAlerts() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                ii.id,
                ii.item_name,
                ii.sku,
                ii.current_stock,
                ii.minimum_stock,
                ii.unit,
                COUNT(ri.room_id) as rooms_affected
            FROM inventory_items ii
            LEFT JOIN room_inventory ri ON ii.id = ri.item_id
            WHERE ii.current_stock <= ii.minimum_stock 
            AND ii.status = 'active'
            GROUP BY ii.id
            ORDER BY (ii.current_stock - ii.minimum_stock) ASC
        ");
        
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts
        ]);
        
    } catch (PDOException $e) {
        error_log('Error getting low stock alerts: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']); }
}

/**
 * Sync booking system with inventory system
 */
function syncBookingInventory() {
    global $pdo;
    
    try {
        // Get reservations that need inventory updates
        $stmt = $pdo->query("
            SELECT r.id, r.room_id, r.status, r.check_in_date, r.check_out_date
            FROM reservations r
            WHERE r.status IN ('checked_in', 'checked_out')
            AND r.updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sync_results = [];
        
        foreach ($reservations as $reservation) {
            // Check if room inventory exists for this room
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM room_inventory 
                WHERE room_id = ?
            ");
            $stmt->execute([$reservation['room_id']]);
            $has_inventory = $stmt->fetch()['count'] > 0;
            
            $sync_results[] = [
                'reservation_id' => $reservation['id'],
                'room_id' => $reservation['room_id'],
                'status' => $reservation['status'],
                'has_inventory' => $has_inventory
            ]; }
        echo json_encode([
            'success' => true,
            'message' => 'Sync completed',
            'results' => $sync_results
        ]);
        
    } catch (PDOException $e) {
        error_log('Error syncing booking inventory: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']); }
}
?>
