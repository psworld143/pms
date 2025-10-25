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
        // Check if inventory_categories table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_categories'");
        $table_exists = $stmt->rowCount() > 0;
        
        if (!$table_exists) {
            // Return demo data if table doesn't exist
            return [
                ['id' => 1, 'name' => 'Bathroom Amenities'],
                ['id' => 2, 'name' => 'Bedding'],
                ['id' => 3, 'name' => 'Cleaning Supplies'],
                ['id' => 4, 'name' => 'Electronics'],
                ['id' => 5, 'name' => 'Food & Beverage'],
                ['id' => 6, 'name' => 'Furniture'],
                ['id' => 7, 'name' => 'Kitchen Supplies'],
                ['id' => 8, 'name' => 'Maintenance'],
                ['id' => 9, 'name' => 'Office Supplies'],
                ['id' => 10, 'name' => 'Safety Equipment']
            ];
        }
        
        $stmt = $pdo->query("SELECT id, name FROM inventory_categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting inventory categories: " . $e->getMessage());
        // Return demo data on error
        return [
            ['id' => 1, 'name' => 'Bathroom Amenities'],
            ['id' => 2, 'name' => 'Bedding'],
            ['id' => 3, 'name' => 'Cleaning Supplies'],
            ['id' => 4, 'name' => 'Electronics'],
            ['id' => 5, 'name' => 'Food & Beverage'],
            ['id' => 6, 'name' => 'Furniture'],
            ['id' => 7, 'name' => 'Kitchen Supplies'],
            ['id' => 8, 'name' => 'Maintenance'],
            ['id' => 9, 'name' => 'Office Supplies'],
            ['id' => 10, 'name' => 'Safety Equipment']
        ];
    }
}
?>