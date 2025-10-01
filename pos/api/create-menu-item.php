<?php
session_start();
require_once '../config/database.php';
require_once '../includes/pos-functions.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    $required_fields = ['name', 'category', 'price'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Validate price
    if (!is_numeric($input['price']) || floatval($input['price']) <= 0) {
        throw new Exception('Price must be a positive number');
    }
    
    // Validate cost if provided
    if (isset($input['cost']) && (!is_numeric($input['cost']) || floatval($input['cost']) < 0)) {
        throw new Exception('Cost must be a non-negative number');
    }
    
    // Create menu item
    $result = createMenuItem($input);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error creating menu item: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Create menu item
 */
function createMenuItem($data) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Insert menu item
        $stmt = $pdo->prepare("
            INSERT INTO pos_menu_items (
                name, description, category, price, cost, 
                image, active, sort_order, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['category'],
            floatval($data['price']),
            isset($data['cost']) ? floatval($data['cost']) : 0.00,
            $data['image'] ?? null,
            isset($data['active']) ? (bool)$data['active'] : true,
            isset($data['sort_order']) ? intval($data['sort_order']) : 0
        ]);
        
        $menu_item_id = $pdo->lastInsertId();
        
        // Log activity
        logPOSActivity($_SESSION['pos_user_id'], 'menu_item_created', "Created menu item: {$data['name']}");
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Menu item created successfully',
            'menu_item_id' => $menu_item_id
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating menu item: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Log POS activity
 */
function logPOSActivity($user_id, $action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $action, $details]);
    } catch (Exception $e) {
        error_log("Error logging POS activity: " . $e->getMessage());
    }
}
?>
