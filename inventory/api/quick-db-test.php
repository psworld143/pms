<?php
/**
 * Quick Database Test for Room Inventory
 * This API checks if all required tables and data exist
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

try {
    $results = [];
    
    // Test 1: Check if user is logged in
    $results['session'] = [
        'user_id' => $_SESSION['user_id'] ?? 'not_set',
        'user_role' => $_SESSION['user_role'] ?? 'not_set',
        'user_name' => $_SESSION['user_name'] ?? 'not_set'
    ];
    
    // Test 2: Check database connection
    $results['database'] = [
        'pdo_available' => isset($pdo) ? 'yes' : 'no',
        'connection_test' => 'success'
    ];
    
    // Test 3: Check tables exist
    $tables_to_check = ['rooms', 'room_inventory', 'inventory_items', 'supply_requests', 'room_inventory_transactions'];
    $results['tables'] = [];
    
    foreach ($tables_to_check as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $results['tables'][$table] = $stmt->rowCount() > 0 ? 'exists' : 'missing';
    }
    
    // Test 4: Check room data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms");
    $results['room_count'] = $stmt->fetch()['count'];
    
    // Test 5: Check room inventory data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM room_inventory");
    $results['room_inventory_count'] = $stmt->fetch()['count'];
    
    // Test 6: Check floors
    $stmt = $pdo->query("SELECT DISTINCT floor FROM rooms ORDER BY floor");
    $results['floors'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Test 7: Check supply requests
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM supply_requests");
    $results['supply_requests_count'] = $stmt->fetch()['count'];
    
    // Test 8: Check inventory items
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
    $results['inventory_items_count'] = $stmt->fetch()['count'];
    
    // Test 9: Sample room data
    $stmt = $pdo->query("SELECT id, room_number, floor, status FROM rooms LIMIT 5");
    $results['sample_rooms'] = $stmt->fetchAll();
    
    // Test 10: Sample room inventory data
    $stmt = $pdo->query("SELECT ri.*, r.room_number FROM room_inventory ri JOIN rooms r ON ri.room_id = r.id LIMIT 5");
    $results['sample_room_inventory'] = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database test completed',
        'results' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Test failed: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>
