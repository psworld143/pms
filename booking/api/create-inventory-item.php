<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Create Inventory Item API
 */

session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and has housekeeping access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['housekeeping', 'manager'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit();
    }
    
    $name = $input['name'] ?? null;
    $category_id = $input['category_id'] ?? null;
    $current_stock = $input['current_stock'] ?? 0;
    $minimum_stock = $input['minimum_stock'] ?? 0;
    $unit_price = $input['unit_price'] ?? 0;
    $description = $input['description'] ?? '';
    
    if (!$name || !$category_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit();
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO inventory_items 
        (item_name, category_id, current_stock, minimum_stock, unit_price, description, created_at, last_updated) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $name,
        $category_id,
        $current_stock,
        $minimum_stock,
        $unit_price,
        $description
    ]);
    
    if ($stmt->rowCount() > 0) {
        $item_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Inventory item created successfully',
            'item_id' => $item_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create inventory item'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Error creating inventory item: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('Error creating inventory item: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
