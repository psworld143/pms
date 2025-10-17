<?php
/**
 * Seed Room Inventory Data
 * This API creates sample room inventory data for testing
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is manager
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SESSION['user_role'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Only managers can seed data']);
    exit();
}

try {
    global $pdo;
    
    // Step 1: Ensure we have inventory items
    $itemsCount = $pdo->query("SELECT COUNT(*) FROM inventory_items")->fetchColumn();
    if ($itemsCount == 0) {
        // Check what columns exist in inventory_items table
        $columns = [];
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items");
            $column_data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $columns = array_flip($column_data);
        } catch (Exception $e) {
            error_log("Error getting column information: " . $e->getMessage());
        }
        
        // Insert sample inventory items (using only existing columns)
        $sampleItems = [
            ['item_name' => 'Bath Towel', 'description' => 'White cotton bath towel', 'current_stock' => 100, 'minimum_stock' => 20, 'unit_price' => 15.00],
            ['item_name' => 'Hand Towel', 'description' => 'White cotton hand towel', 'current_stock' => 100, 'minimum_stock' => 20, 'unit_price' => 8.00],
            ['item_name' => 'Face Towel', 'description' => 'White cotton face towel', 'current_stock' => 100, 'minimum_stock' => 20, 'unit_price' => 5.00],
            ['item_name' => 'Bath Soap', 'description' => 'Luxury hotel bath soap', 'current_stock' => 200, 'minimum_stock' => 50, 'unit_price' => 3.00],
            ['item_name' => 'Shampoo', 'description' => 'Hotel shampoo 50ml', 'current_stock' => 200, 'minimum_stock' => 50, 'unit_price' => 2.50],
            ['item_name' => 'Conditioner', 'description' => 'Hotel conditioner 50ml', 'current_stock' => 200, 'minimum_stock' => 50, 'unit_price' => 2.50],
            ['item_name' => 'Hair Dryer', 'description' => 'Professional hair dryer', 'current_stock' => 50, 'minimum_stock' => 10, 'unit_price' => 45.00],
            ['item_name' => 'Iron', 'description' => 'Hotel iron with board', 'current_stock' => 30, 'minimum_stock' => 5, 'unit_price' => 25.00],
            ['item_name' => 'Coffee Maker', 'description' => 'Single serve coffee maker', 'current_stock' => 25, 'minimum_stock' => 5, 'unit_price' => 80.00],
            ['item_name' => 'Television Remote', 'description' => 'TV remote control', 'current_stock' => 50, 'minimum_stock' => 10, 'unit_price' => 15.00]
        ];
        
        // Build dynamic INSERT based on available columns
        $fields = ['item_name', 'description', 'current_stock', 'minimum_stock', 'unit_price'];
        $values = ['?', '?', '?', '?', '?'];
        
        // Add optional fields if they exist
        if (isset($columns['sku'])) {
            $fields[] = 'sku';
            $values[] = '?';
        }
        if (isset($columns['unit'])) {
            $fields[] = 'unit';
            $values[] = '?';
        }
        if (isset($columns['status'])) {
            $fields[] = 'status';
            $values[] = '?';
        }
        if (isset($columns['category_id'])) {
            $fields[] = 'category_id';
            $values[] = '?';
        }
        
        $sql = "INSERT INTO inventory_items (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        $stmt = $pdo->prepare($sql);
        
        foreach ($sampleItems as $item) {
            $params = [
                $item['item_name'],
                $item['description'],
                $item['current_stock'],
                $item['minimum_stock'],
                $item['unit_price']
            ];
            
            // Add optional parameters if columns exist
            if (isset($columns['sku'])) {
                $params[] = 'BT' . str_pad(array_search($item, $sampleItems) + 1, 3, '0', STR_PAD_LEFT);
            }
            if (isset($columns['unit'])) {
                $params[] = 'pcs';
            }
            if (isset($columns['status'])) {
                $params[] = 'active';
            }
            if (isset($columns['category_id'])) {
                $params[] = 1; // Default category
            }
            
            $stmt->execute($params);
        }
    }
    
    // Step 2: Get rooms
    $rooms = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rooms)) {
        echo json_encode(['success' => false, 'message' => 'No rooms found. Please create rooms first.']);
        exit();
    }
    
    // Step 3: Get inventory items
    $items = $pdo->query("SELECT id FROM inventory_items WHERE status = 'active' LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'No inventory items found.']);
        exit();
    }
    
    // Step 4: Clear existing room inventory data
    $pdo->query("DELETE FROM room_inventory");
    
    // Step 5: Insert sample room inventory data
    $stmt = $pdo->prepare("
        INSERT INTO room_inventory (room_id, item_id, quantity_allocated, quantity_current, par_level) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $assignedCount = 0;
    $roomInventoryData = [
        // Standard room inventory template
        [1, 4, 2, 1], // Bath Towel: allocated=4, current=3, par=2
        [2, 2, 1],    // Hand Towel: allocated=2, current=2, par=1
        [3, 2, 1],    // Face Towel: allocated=2, current=1, par=1
        [4, 2, 1],    // Bath Soap: allocated=2, current=2, par=1
        [5, 2, 1],    // Shampoo: allocated=2, current=1, par=1
        [6, 2, 1],    // Conditioner: allocated=2, current=2, par=1
        [7, 1, 1],    // Hair Dryer: allocated=1, current=0, par=1
        [8, 1, 1],    // Iron: allocated=1, current=1, par=1
        [9, 1, 1],    // Coffee Maker: allocated=1, current=1, par=1
        [10, 1, 1]    // TV Remote: allocated=1, current=1, par=1
    ];
    
    foreach ($rooms as $room) {
        foreach ($roomInventoryData as $data) {
            if ($data[0] <= count($items)) { // Make sure we have enough items
                $itemId = $items[$data[0] - 1]; // Convert to 0-based index
                $allocated = $data[1];
                $current = $data[2];
                $par = $data[3];
                
                $stmt->execute([$room['id'], $itemId, $allocated, $current, $par]);
                $assignedCount++;
            }
        }
    }
    
    // Step 6: Create sample supply requests
    $requestStmt = $pdo->prepare("
        INSERT INTO supply_requests (item_id, quantity_requested, room_number, reason, notes, requested_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $sampleRequests = [
        [$items[0], 1, '202', 'missing', 'Bath towel missing after guest checkout', $_SESSION['user_id'], 'pending'],
        [$items[2], 1, '202', 'missing', 'Face towel missing after guest checkout', $_SESSION['user_id'], 'pending'],
        [$items[6], 1, '202', 'missing', 'Hair dryer not found in room', $_SESSION['user_id'], 'pending'],
        [$items[4], 1, '202', 'low_stock', 'Shampoo running low', $_SESSION['user_id'], 'approved']
    ];
    
    foreach ($sampleRequests as $request) {
        $requestStmt->execute($request);
    }
    
    // Step 7: Assign some rooms to housekeeping user
    $pdo->query("UPDATE rooms SET assigned_housekeeping = " . $_SESSION['user_id'] . " WHERE room_number IN ('201', '202', '203', '204', '205')");
    
    echo json_encode([
        'success' => true,
        'message' => "Room inventory data seeded successfully!",
        'data' => [
            'rooms_processed' => count($rooms),
            'items_assigned' => $assignedCount,
            'requests_created' => count($sampleRequests)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error seeding room inventory: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error seeding data: ' . $e->getMessage()
    ]);
}
?>
