<?php
/**
 * Quick Fix for Room Inventory Database
 * This script ensures all necessary data exists
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    global $pdo;
    $results = [];
    
    // Step 1: Check if we have rooms
    $roomCount = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    if ($roomCount == 0) {
        // Create sample rooms (using existing booking system approach)
        $rooms = [
            ['room_number' => '101', 'floor' => 1, 'room_type' => 'Standard', 'status' => 'available', 'capacity' => 2],
            ['room_number' => '102', 'floor' => 1, 'room_type' => 'Standard', 'status' => 'occupied', 'capacity' => 2],
            ['room_number' => '103', 'floor' => 1, 'room_type' => 'Standard', 'status' => 'available', 'capacity' => 2],
            ['room_number' => '201', 'floor' => 2, 'room_type' => 'Deluxe', 'status' => 'available', 'capacity' => 2],
            ['room_number' => '202', 'floor' => 2, 'room_type' => 'Deluxe', 'status' => 'occupied', 'capacity' => 2],
            ['room_number' => '203', 'floor' => 2, 'room_type' => 'Standard', 'status' => 'available', 'capacity' => 2],
            ['room_number' => '301', 'floor' => 3, 'room_type' => 'Deluxe', 'status' => 'available', 'capacity' => 2],
            ['room_number' => '302', 'floor' => 3, 'room_type' => 'Standard', 'status' => 'occupied', 'capacity' => 2]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, floor, room_type, status, capacity) VALUES (?, ?, ?, ?, ?)");
        foreach ($rooms as $room) {
            $stmt->execute([$room['room_number'], $room['floor'], $room['room_type'], $room['status'], $room['capacity']]);
        }
        $results['rooms_created'] = count($rooms);
    } else {
        $results['rooms_existing'] = $roomCount;
        // Update existing rooms to have proper capacity (using booking system approach)
        try {
            $pdo->query("UPDATE rooms SET capacity = 2 WHERE capacity IS NULL OR capacity = 0");
            $results['capacity_updated'] = 'Updated capacity for existing rooms';
        } catch (Exception $e) {
            $results['capacity_error'] = 'Could not update capacity column';
        }
    }
    
    // Step 2: Check if we have inventory items
    $itemCount = $pdo->query("SELECT COUNT(*) FROM inventory_items")->fetchColumn();
    if ($itemCount == 0) {
        // Create sample inventory items
        $items = [
            ['item_name' => 'Bath Towel', 'description' => 'White cotton bath towel', 'current_stock' => 100, 'minimum_stock' => 20, 'unit_price' => 15.00],
            ['item_name' => 'Hand Towel', 'description' => 'White cotton hand towel', 'current_stock' => 100, 'minimum_stock' => 20, 'unit_price' => 8.00],
            ['item_name' => 'Face Towel', 'description' => 'White cotton face towel', 'current_stock' => 100, 'minimum_stock' => 20, 'unit_price' => 5.00],
            ['item_name' => 'Bath Soap', 'description' => 'Luxury hotel bath soap', 'current_stock' => 200, 'minimum_stock' => 50, 'unit_price' => 3.00],
            ['item_name' => 'Shampoo', 'description' => 'Hotel shampoo 50ml', 'current_stock' => 200, 'minimum_stock' => 50, 'unit_price' => 2.50]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO inventory_items (item_name, description, current_stock, minimum_stock, unit_price) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$item['item_name'], $item['description'], $item['current_stock'], $item['minimum_stock'], $item['unit_price']]);
        }
        $results['items_created'] = count($items);
    } else {
        $results['items_existing'] = $itemCount;
    }
    
    // Step 3: Check if we have room inventory data
    $roomInventoryCount = $pdo->query("SELECT COUNT(*) FROM room_inventory")->fetchColumn();
    if ($roomInventoryCount == 0) {
        // Get room IDs
        $rooms = $pdo->query("SELECT id FROM rooms LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        $items = $pdo->query("SELECT id FROM inventory_items LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($rooms) && !empty($items)) {
            $stmt = $pdo->prepare("INSERT INTO room_inventory (room_id, item_id, quantity_allocated, quantity_current, par_level) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($rooms as $roomId) {
                foreach ($items as $itemId) {
                    $stmt->execute([$roomId, $itemId, 4, 3, 2]); // allocated=4, current=3, par=2
                }
            }
            $results['room_inventory_created'] = count($rooms) * count($items);
        }
    } else {
        $results['room_inventory_existing'] = $roomInventoryCount;
    }
    
    // Step 4: Assign rooms to current user for housekeeping
    $pdo->query("UPDATE rooms SET assigned_housekeeping = " . $_SESSION['user_id'] . " WHERE room_number IN ('201', '202', '203')");
    $results['rooms_assigned'] = 3;
    
    echo json_encode([
        'success' => true,
        'message' => 'Database quick fix completed',
        'results' => $results
    ]);
    
} catch (Exception $e) {
    error_log("Error in quick fix: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
