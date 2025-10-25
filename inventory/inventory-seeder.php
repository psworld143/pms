<?php
/**
 * Inventory Seeder Command Line Tool
 * Hotel PMS Training System - Inventory Module
 * 
 * Usage: php inventory-seeder.php [command] [options]
 * 
 * Commands:
 *   seed          - Seed inventory with sample data
 *   clear         - Clear all inventory data
 *   status        - Show current inventory status
 *   help          - Show this help message
 */

require_once __DIR__ . '/../includes/database.php';

// Get command line arguments
$command = $argv[1] ?? 'help';
$option = $argv[2] ?? '';

echo "🏨 Hotel Inventory Seeder Tool\n";
echo "==============================\n\n";

try {
    $pdo = getDatabaseConnection();
    echo "✅ Database connection established\n\n";

    switch ($command) {
        case 'seed':
            seedInventory();
            break;
            
        case 'clear':
            if ($option === '--confirm') {
                clearInventory();
            } else {
                echo "⚠️  Warning: This will delete ALL inventory data!\n";
                echo "Use: php inventory-seeder.php clear --confirm\n";
            }
            break;
            
        case 'status':
            showInventoryStatus();
            break;
            
        case 'help':
        default:
            showHelp();
            break;
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

function seedInventory() {
    global $pdo;
    
    echo "🌱 Seeding inventory with sample data...\n\n";
    
    // Sample data (same as in seed-inventory-items.php)
    $sample_items = [
        // Food & Beverage
        ['name' => 'Coffee Beans (Arabica)', 'category' => 'Food & Beverage', 'sku' => 'FB-COFFEE-001', 'description' => 'Premium Arabica coffee beans for hotel restaurant', 'unit' => 'Kilogram', 'quantity' => 50, 'minimum_stock' => 10, 'cost_price' => 450.00, 'supplier' => 'Premium Coffee Supply Co.'],
        ['name' => 'Tea Bags (Assorted)', 'category' => 'Food & Beverage', 'sku' => 'FB-TEA-002', 'description' => 'Assorted tea bags for room service', 'unit' => 'Box', 'quantity' => 25, 'minimum_stock' => 5, 'cost_price' => 120.00, 'supplier' => 'Tea Masters Inc.'],
        ['name' => 'Bottled Water (500ml)', 'category' => 'Food & Beverage', 'sku' => 'FB-WATER-003', 'description' => 'Premium bottled water for guest rooms', 'unit' => 'Bottle', 'quantity' => 200, 'minimum_stock' => 50, 'cost_price' => 15.00, 'supplier' => 'Pure Water Solutions'],
        
        // Amenities
        ['name' => 'Bath Towels (White)', 'category' => 'Amenities', 'sku' => 'AM-TOWEL-001', 'description' => 'Premium white bath towels', 'unit' => 'Piece', 'quantity' => 150, 'minimum_stock' => 30, 'cost_price' => 180.00, 'supplier' => 'Luxury Linens Ltd.'],
        ['name' => 'Hand Towels', 'category' => 'Amenities', 'sku' => 'AM-HANDTOWEL-002', 'description' => 'Soft hand towels for guest bathrooms', 'unit' => 'Piece', 'quantity' => 200, 'minimum_stock' => 40, 'cost_price' => 95.00, 'supplier' => 'Luxury Linens Ltd.'],
        ['name' => 'Shampoo (Hotel Size)', 'category' => 'Amenities', 'sku' => 'AM-SHAMPOO-004', 'description' => 'Premium hotel-size shampoo bottles', 'unit' => 'Bottle', 'quantity' => 300, 'minimum_stock' => 60, 'cost_price' => 25.00, 'supplier' => 'Spa Essentials Co.'],
        
        // Cleaning Supplies
        ['name' => 'All-Purpose Cleaner', 'category' => 'Cleaning Supplies', 'sku' => 'CS-CLEANER-001', 'description' => 'Multi-surface cleaning solution', 'unit' => 'Bottle', 'quantity' => 50, 'minimum_stock' => 10, 'cost_price' => 85.00, 'supplier' => 'CleanPro Solutions'],
        ['name' => 'Glass Cleaner', 'category' => 'Cleaning Supplies', 'sku' => 'CS-GLASS-002', 'description' => 'Streak-free glass cleaning solution', 'unit' => 'Bottle', 'quantity' => 30, 'minimum_stock' => 8, 'cost_price' => 75.00, 'supplier' => 'CleanPro Solutions'],
        
        // Office Supplies
        ['name' => 'A4 Paper (White)', 'category' => 'Office Supplies', 'sku' => 'OS-PAPER-001', 'description' => 'Premium white A4 copy paper', 'unit' => 'Ream', 'quantity' => 15, 'minimum_stock' => 3, 'cost_price' => 180.00, 'supplier' => 'Office Depot Pro'],
        ['name' => 'Ballpoint Pens (Blue)', 'category' => 'Office Supplies', 'sku' => 'OS-PENS-002', 'description' => 'Blue ballpoint pens for front desk', 'unit' => 'Box', 'quantity' => 10, 'minimum_stock' => 2, 'cost_price' => 120.00, 'supplier' => 'Office Depot Pro'],
        
        // Maintenance
        ['name' => 'Light Bulbs (LED)', 'category' => 'Maintenance', 'sku' => 'MT-LED-001', 'description' => 'Energy-efficient LED light bulbs', 'unit' => 'Piece', 'quantity' => 100, 'minimum_stock' => 20, 'cost_price' => 45.00, 'supplier' => 'Electrical Supply Co.'],
        ['name' => 'Air Filter (HVAC)', 'category' => 'Maintenance', 'sku' => 'MT-FILTER-002', 'description' => 'Replacement air filters for HVAC system', 'unit' => 'Piece', 'quantity' => 20, 'minimum_stock' => 5, 'cost_price' => 250.00, 'supplier' => 'HVAC Solutions Inc.']
    ];
    
    $seeded_count = 0;
    
    foreach ($sample_items as $item) {
        // Get or create category
        $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ?");
        $stmt->execute([$item['category']]);
        $category_result = $stmt->fetch();

        if (!$category_result) {
            $stmt = $pdo->prepare("INSERT INTO inventory_categories (name, description, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$item['category'], 'Auto-created category for seeded items']);
            $category_id = $pdo->lastInsertId();
        } else {
            $category_id = $category_result['id'];
        }

        // Check if item already exists
        $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE item_name = ?");
        $stmt->execute([$item['name']]);
        if ($stmt->fetch()) {
            echo "⚠️  Item already exists: {$item['name']}\n";
            continue;
        }

        // Create inventory item
        $stmt = $pdo->prepare("
            INSERT INTO inventory_items 
            (item_name, category_id, current_stock, minimum_stock, unit_price, description, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $item['name'],
            $category_id,
            $item['quantity'],
            $item['minimum_stock'],
            $item['cost_price'],
            $item['description'] . " | SKU: " . $item['sku'] . " | Unit: " . $item['unit'] . " | Supplier: " . $item['supplier']
        ]);

        $seeded_count++;
        echo "✅ Seeded: {$item['name']} - Qty: {$item['quantity']} {$item['unit']}\n";
    }
    
    echo "\n🎉 Seeding completed! {$seeded_count} items added.\n\n";
    showInventoryStatus();
}

function clearInventory() {
    global $pdo;
    
    echo "🗑️  Clearing all inventory data...\n";
    
    // Delete inventory items first (due to foreign key constraints)
    $stmt = $pdo->query("DELETE FROM inventory_items");
    $items_deleted = $stmt->rowCount();
    
    // Delete categories
    $stmt = $pdo->query("DELETE FROM inventory_categories");
    $categories_deleted = $stmt->rowCount();
    
    echo "✅ Deleted {$items_deleted} inventory items\n";
    echo "✅ Deleted {$categories_deleted} categories\n";
    echo "🎉 Inventory cleared successfully!\n";
}

function showInventoryStatus() {
    global $pdo;
    
    echo "📊 Current Inventory Status:\n";
    echo "============================\n";
    
    // Get category summary
    $stmt = $pdo->query("
        SELECT 
            c.name as category_name,
            COUNT(i.id) as item_count,
            SUM(i.current_stock * i.unit_price) as total_value
        FROM inventory_categories c
        LEFT JOIN inventory_items i ON c.id = i.category_id
        GROUP BY c.id, c.name
        ORDER BY c.name
    ");

    while ($row = $stmt->fetch()) {
        echo "📁 {$row['category_name']}: {$row['item_count']} items (₱" . number_format($row['total_value'], 2) . ")\n";
    }

    // Get totals
    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM inventory_items");
    $total_items = $stmt->fetch()['total_items'];

    $stmt = $pdo->query("SELECT SUM(current_stock * unit_price) as total_value FROM inventory_items");
    $total_value = $stmt->fetch()['total_value'];

    echo "\n💰 Total Inventory Value: ₱" . number_format($total_value, 2) . "\n";
    echo "📦 Total Items: {$total_items}\n";
    
    // Show low stock items
    $stmt = $pdo->query("
        SELECT i.item_name, c.name as category, i.current_stock, i.minimum_stock
        FROM inventory_items i
        JOIN inventory_categories c ON i.category_id = c.id
        WHERE i.current_stock <= i.minimum_stock
        ORDER BY i.current_stock ASC
    ");
    
    $low_stock = $stmt->fetchAll();
    if (!empty($low_stock)) {
        echo "\n⚠️  Low Stock Items:\n";
        foreach ($low_stock as $item) {
            echo "   • {$item['item_name']} ({$item['category']}): {$item['current_stock']}/{$item['minimum_stock']}\n";
        }
    }
}

function showHelp() {
    echo "Available Commands:\n";
    echo "==================\n";
    echo "seed          - Seed inventory with sample data\n";
    echo "clear         - Clear all inventory data (use --confirm flag)\n";
    echo "status        - Show current inventory status\n";
    echo "help          - Show this help message\n\n";
    echo "Examples:\n";
    echo "  php inventory-seeder.php seed\n";
    echo "  php inventory-seeder.php status\n";
    echo "  php inventory-seeder.php clear --confirm\n";
}
?>
