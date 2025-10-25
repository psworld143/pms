<?php
/**
 * Test Database Connection
 * Simple test to check if database connection and basic queries work
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

try {
    // Test 1: Check if PDO connection exists
    if (!isset($pdo)) {
        throw new Exception('PDO connection not available');
    }
    
    // Test 2: Check if supply_requests table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'supply_requests'");
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        throw new Exception('supply_requests table does not exist');
    }
    
    // Test 3: Check table structure
    $stmt = $pdo->query("DESCRIBE supply_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $column_names = array_column($columns, 'Field');
    
    // Test 4: Check if inventory_items table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_items'");
    $inventory_table_exists = $stmt->rowCount() > 0;
    
    // Test 5: Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $users_table_exists = $stmt->rowCount() > 0;
    
    // Test 6: Try a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM supply_requests");
    $count_result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test 7: Check session data
    $session_data = [
        'user_id' => $_SESSION['user_id'] ?? 'not_set',
        'user_role' => $_SESSION['user_role'] ?? 'not_set',
        'user_name' => $_SESSION['user_name'] ?? 'not_set'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection test successful',
        'tests' => [
            'pdo_connection' => 'OK',
            'supply_requests_table' => $table_exists ? 'EXISTS' : 'MISSING',
            'inventory_items_table' => $inventory_table_exists ? 'EXISTS' : 'MISSING',
            'users_table' => $users_table_exists ? 'EXISTS' : 'MISSING',
            'supply_requests_count' => $count_result['count'],
            'supply_requests_columns' => $column_names,
            'session_data' => $session_data
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection test failed: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>
