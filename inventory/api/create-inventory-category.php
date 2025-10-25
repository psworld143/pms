<?php
/**
 * Create Inventory Category
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
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit();
    }
    
    $result = createInventoryCategory($name, $description);
    
    echo json_encode([
        'success' => true,
        'message' => 'Category created successfully',
        'category_id' => $result['category_id']
    ]);
    
} catch (Exception $e) {
    error_log("Error creating inventory category: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Create inventory category
 */
function createInventoryCategory($name, $description) {
    global $pdo;
    
    try {
        // Check if category already exists
        $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ?");
        $stmt->execute([$name]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            throw new Exception('Category already exists');
        }
        
        // Create category
        $stmt = $pdo->prepare("
            INSERT INTO inventory_categories (name, description, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$name, $description]);
        
        $category_id = $pdo->lastInsertId();
        
        return ['category_id' => $category_id];
        
    } catch (PDOException $e) {
        error_log("Error creating inventory category: " . $e->getMessage());
        throw $e;
    }
}
?>
