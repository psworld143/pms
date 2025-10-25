<?php
/**
 * Test Database Connection
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Test database connection
    $pdo = new PDO("mysql:host=localhost;dbname=pms_hotel", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test basic query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
    $count = $stmt->fetch()['count'];
    
    // Test inventory_categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_categories");
    $cat_count = $stmt->fetch()['count'];
    
    // Test inventory_transactions
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_transactions");
    $trans_count = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'inventory_items_count' => $count,
        'inventory_categories_count' => $cat_count,
        'inventory_transactions_count' => $trans_count
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
