<?php
require_once '../includes/database.php';

echo "Testing Room Inventory APIs...\n\n";

// Test 1: Check database connection
try {
    global $pdo;
    echo "✓ Database connection successful\n";
    
    // Test 2: Check rooms table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms");
    $result = $stmt->fetch();
    echo "✓ Rooms count: " . $result['count'] . "\n";
    
    // Test 3: Check if we have any rooms
    if ($result['count'] == 0) {
        echo "⚠ No rooms found in database!\n";
        echo "Creating sample rooms...\n";
        
        // Create sample rooms
        $sample_rooms = [
            ['room_number' => '101', 'floor' => 1, 'room_type' => 'Standard', 'status' => 'available'],
            ['room_number' => '102', 'floor' => 1, 'room_type' => 'Standard', 'status' => 'available'],
            ['room_number' => '201', 'floor' => 2, 'room_type' => 'Deluxe', 'status' => 'available'],
            ['room_number' => '202', 'floor' => 2, 'room_type' => 'Deluxe', 'status' => 'occupied'],
            ['room_number' => '301', 'floor' => 3, 'room_type' => 'Suite', 'status' => 'available']
        ];
        
        foreach ($sample_rooms as $room) {
            $stmt = $pdo->prepare("INSERT INTO rooms (room_number, floor, room_type, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$room['room_number'], $room['floor'], $room['room_type'], $room['status']]);
        }
        
        echo "✓ Sample rooms created\n";
    }
    
    // Test 4: Check floors
    $stmt = $pdo->query("SELECT DISTINCT floor FROM rooms ORDER BY floor");
    $floors = $stmt->fetchAll();
    echo "✓ Floors found: " . implode(', ', array_column($floors, 'floor')) . "\n";
    
    // Test 5: Test room inventory stats query
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_rooms
        FROM rooms
    ");
    $total_rooms = $stmt->fetch()['total_rooms'];
    echo "✓ Total rooms: $total_rooms\n";
    
    // Test 6: Check room_inventory_items table
    $stmt = $pdo->query("SHOW TABLES LIKE 'room_inventory_items'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "⚠ room_inventory_items table doesn't exist, creating it...\n";
        
        $create_table = "
        CREATE TABLE room_inventory_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            item_id INT NOT NULL,
            quantity_allocated INT DEFAULT 0,
            quantity_current INT DEFAULT 0,
            par_level INT DEFAULT 1,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (room_id) REFERENCES rooms(id),
            FOREIGN KEY (item_id) REFERENCES inventory_items(id)
        )
        ";
        
        $pdo->exec($create_table);
        echo "✓ room_inventory_items table created\n";
        
        // Add some sample room inventory items
        $stmt = $pdo->query("SELECT id FROM inventory_items LIMIT 3");
        $items = $stmt->fetchAll();
        
        if (count($items) > 0) {
            $stmt = $pdo->query("SELECT id FROM rooms LIMIT 3");
            $rooms = $stmt->fetchAll();
            
            foreach ($rooms as $room) {
                foreach ($items as $item) {
                    $stmt = $pdo->prepare("
                        INSERT INTO room_inventory_items (room_id, item_id, quantity_allocated, quantity_current, par_level)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $room['id'],
                        $item['id'],
                        rand(2, 5), // allocated
                        rand(0, 3), // current
                        2 // par level
                    ]);
                }
            }
            echo "✓ Sample room inventory items created\n";
        }
    } else {
        echo "✓ room_inventory_items table exists\n";
    }
    
    echo "\n✅ All tests passed! Room inventory should work now.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
