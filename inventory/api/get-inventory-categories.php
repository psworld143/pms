<?php
/**
 * Get Inventory Categories
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

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
    $categories = getInventoryCategories();
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory categories: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory categories
 */
function getInventoryCategories() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                id,
                name,
                description,
                '#10B981' as color,
                'fas fa-box' as icon,
                1 as active,
                created_at,
                created_at as updated_at
            FROM inventory_categories
            ORDER BY name ASC
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting inventory categories: " . $e->getMessage());
        return [];
    }
}
?>
