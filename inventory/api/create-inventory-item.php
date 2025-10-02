<?php
/**
 * Create Inventory Item
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
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $minimum_stock = $_POST['minimum_stock'] ?? 0;
    $cost_price = $_POST['cost_price'] ?? 0;
    $supplier = $_POST['supplier'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Validate required fields
    if (empty($name) || empty($category) || empty($unit)) {
        echo json_encode(['success' => false, 'message' => 'Name, category, and unit are required']);
        exit();
    }
    
    $result = createInventoryItem($name, $category, $sku, $unit, $quantity, $minimum_stock, $cost_price, $supplier, $description);
    
    echo json_encode([
        'success' => true,
        'message' => 'Inventory item created successfully',
        'item_id' => $result['item_id']
    ]);
    
} catch (Exception $e) {
    error_log("Error creating inventory item: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Create inventory item
 */
function createInventoryItem($name, $category, $sku, $unit, $quantity, $minimum_stock, $cost_price, $supplier, $description) {
    global $pdo;
    
    try {
        // Get category ID
        $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ? AND active = 1");
        $stmt->execute([$category]);
        $category_result = $stmt->fetch();
        
        if (!$category_result) {
            // Create category if it doesn't exist
            $stmt = $pdo->prepare("
                INSERT INTO inventory_categories (name, description, active, created_at) 
                VALUES (?, ?, 1, NOW())
            ");
            $stmt->execute([$category, 'Auto-created category']);
            $category_id = $pdo->lastInsertId();
        } else {
            $category_id = $category_result['id'];
        }
        
        // Generate SKU if not provided
        if (empty($sku)) {
            $sku = 'ITM-' . strtoupper(substr($name, 0, 3)) . '-' . date('Ymd') . '-' . rand(100, 999);
        }
        
        // Create inventory item
        $stmt = $pdo->prepare("
            INSERT INTO inventory_items 
            (name, sku, description, category_id, quantity, minimum_stock, maximum_stock, unit_price, cost_price, supplier, location, unit, status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())
        ");
        
        $stmt->execute([
            $name,
            $sku,
            $description,
            $category_id,
            $quantity,
            $minimum_stock,
            $quantity * 2, // Set max stock to 2x current quantity
            $cost_price,
            $cost_price,
            $supplier,
            'Main Storage',
            $unit,
            $_SESSION['user_id']
        ]);
        
        $item_id = $pdo->lastInsertId();
        
        // Create initial stock transaction
        if ($quantity > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO inventory_transactions 
                (item_id, transaction_type, quantity, unit_price, total_value, reason, created_by, created_at) 
                VALUES (?, 'in', ?, ?, ?, 'Initial stock', ?, NOW())
            ");
            $stmt->execute([
                $item_id,
                $quantity,
                $cost_price,
                $quantity * $cost_price,
                $_SESSION['user_id']
            ]);
        }
        
        return [
            'item_id' => $item_id
        ];
        
    } catch (PDOException $e) {
        error_log("Error creating inventory item: " . $e->getMessage());
        throw new Exception("Database error while creating inventory item");
    }
}
?>
