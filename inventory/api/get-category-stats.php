<?php
/**
 * Get Category Statistics
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $category_id = $_GET['category_id'] ?? '';
    
    if (empty($category_id)) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        exit();
    }
    
    $stats = getCategoryStats($category_id);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error getting category stats: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get category statistics
 */
function getCategoryStats($category_id) {
    global $pdo;
    
    try {
        // Get item count for this category
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as item_count
            FROM inventory_items 
            WHERE category_id = ? AND status = 'active'
        ");
        $stmt->execute([$category_id]);
        $item_count = $stmt->fetch()['item_count'] ?? 0;
        
        // Get total value for this category
        $stmt = $pdo->prepare("
            SELECT SUM(quantity * unit_price) as total_value
            FROM inventory_items 
            WHERE category_id = ? AND status = 'active'
        ");
        $stmt->execute([$category_id]);
        $result = $stmt->fetch();
        $total_value = $result['total_value'] ?? 0;
        
        return [
            'item_count' => (int)$item_count,
            'total_value' => (float)$total_value
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting category stats: " . $e->getMessage());
        return [
            'item_count' => 0,
            'total_value' => 0
        ];
    }
}
?>
