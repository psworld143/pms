<?php
/**
 * Seed Inventory Items to Database
 * Hotel PMS Training System - Inventory Module
 * 
 * Usage: php seed-inventory-items.php
 */

require_once __DIR__ . '/../includes/database.php';

// Sample inventory items data
$sample_items = [
    // Food & Beverage
    [
        'name' => 'Coffee Beans (Arabica)',
        'category' => 'Food & Beverage',
        'sku' => 'FB-COFFEE-001',
        'description' => 'Premium Arabica coffee beans for hotel restaurant',
        'unit' => 'Kilogram',
        'quantity' => 50,
        'minimum_stock' => 10,
        'cost_price' => 450.00,
        'supplier' => 'Premium Coffee Supply Co.'
    ],
    [
        'name' => 'Tea Bags (Assorted)',
        'category' => 'Food & Beverage',
        'sku' => 'FB-TEA-002',
        'description' => 'Assorted tea bags for room service',
        'unit' => 'Box',
        'quantity' => 25,
        'minimum_stock' => 5,
        'cost_price' => 120.00,
        'supplier' => 'Tea Masters Inc.'
    ],
    [
        'name' => 'Bottled Water (500ml)',
        'category' => 'Food & Beverage',
        'sku' => 'FB-WATER-003',
        'description' => 'Premium bottled water for guest rooms',
        'unit' => 'Bottle',
        'quantity' => 200,
        'minimum_stock' => 50,
        'cost_price' => 15.00,
        'supplier' => 'Pure Water Solutions'
    ],
    [
        'name' => 'Breakfast Cereal',
        'category' => 'Food & Beverage',
        'sku' => 'FB-CEREAL-004',
        'description' => 'Assorted breakfast cereals',
        'unit' => 'Box',
        'quantity' => 30,
        'minimum_stock' => 8,
        'cost_price' => 85.00,
        'supplier' => 'Morning Delights Co.'
    ],
    [
        'name' => 'Fresh Milk (1L)',
        'category' => 'Food & Beverage',
        'sku' => 'FB-MILK-005',
        'description' => 'Fresh whole milk for breakfast service',
        'unit' => 'Liter',
        'quantity' => 40,
        'minimum_stock' => 10,
        'cost_price' => 65.00,
        'supplier' => 'Dairy Fresh Farms'
    ],

    // Amenities
    [
        'name' => 'Bath Towels (White)',
        'category' => 'Amenities',
        'sku' => 'AM-TOWEL-001',
        'description' => 'Premium white bath towels',
        'unit' => 'Piece',
        'quantity' => 150,
        'minimum_stock' => 30,
        'cost_price' => 180.00,
        'supplier' => 'Luxury Linens Ltd.'
    ],
    [
        'name' => 'Hand Towels',
        'category' => 'Amenities',
        'sku' => 'AM-HANDTOWEL-002',
        'description' => 'Soft hand towels for guest bathrooms',
        'unit' => 'Piece',
        'quantity' => 200,
        'minimum_stock' => 40,
        'cost_price' => 95.00,
        'supplier' => 'Luxury Linens Ltd.'
    ],
    [
        'name' => 'Bathrobes (Cotton)',
        'category' => 'Amenities',
        'sku' => 'AM-BATHROBE-003',
        'description' => 'Premium cotton bathrobes',
        'unit' => 'Piece',
        'quantity' => 80,
        'minimum_stock' => 20,
        'cost_price' => 450.00,
        'supplier' => 'Luxury Linens Ltd.'
    ],
    [
        'name' => 'Shampoo (Hotel Size)',
        'category' => 'Amenities',
        'sku' => 'AM-SHAMPOO-004',
        'description' => 'Premium hotel-size shampoo bottles',
        'unit' => 'Bottle',
        'quantity' => 300,
        'minimum_stock' => 60,
        'cost_price' => 25.00,
        'supplier' => 'Spa Essentials Co.'
    ],
    [
        'name' => 'Body Lotion',
        'category' => 'Amenities',
        'sku' => 'AM-LOTION-005',
        'description' => 'Moisturizing body lotion',
        'unit' => 'Bottle',
        'quantity' => 250,
        'minimum_stock' => 50,
        'cost_price' => 22.00,
        'supplier' => 'Spa Essentials Co.'
    ],
    [
        'name' => 'Soap Bars (Luxury)',
        'category' => 'Amenities',
        'sku' => 'AM-SOAP-006',
        'description' => 'Luxury soap bars for guest bathrooms',
        'unit' => 'Piece',
        'quantity' => 400,
        'minimum_stock' => 80,
        'cost_price' => 18.00,
        'supplier' => 'Spa Essentials Co.'
    ],

    // Cleaning Supplies
    [
        'name' => 'All-Purpose Cleaner',
        'category' => 'Cleaning Supplies',
        'sku' => 'CS-CLEANER-001',
        'description' => 'Multi-surface cleaning solution',
        'unit' => 'Bottle',
        'quantity' => 50,
        'minimum_stock' => 10,
        'cost_price' => 85.00,
        'supplier' => 'CleanPro Solutions'
    ],
    [
        'name' => 'Glass Cleaner',
        'category' => 'Cleaning Supplies',
        'sku' => 'CS-GLASS-002',
        'description' => 'Streak-free glass cleaning solution',
        'unit' => 'Bottle',
        'quantity' => 30,
        'minimum_stock' => 8,
        'cost_price' => 75.00,
        'supplier' => 'CleanPro Solutions'
    ],
    [
        'name' => 'Disinfectant Spray',
        'category' => 'Cleaning Supplies',
        'sku' => 'CS-DISINFECT-003',
        'description' => 'Hospital-grade disinfectant spray',
        'unit' => 'Bottle',
        'quantity' => 40,
        'minimum_stock' => 10,
        'cost_price' => 120.00,
        'supplier' => 'CleanPro Solutions'
    ],
    [
        'name' => 'Microfiber Cloths',
        'category' => 'Cleaning Supplies',
        'sku' => 'CS-CLOTHS-004',
        'description' => 'Reusable microfiber cleaning cloths',
        'unit' => 'Pack',
        'quantity' => 25,
        'minimum_stock' => 5,
        'cost_price' => 150.00,
        'supplier' => 'CleanPro Solutions'
    ],
    [
        'name' => 'Vacuum Bags',
        'category' => 'Cleaning Supplies',
        'sku' => 'CS-VACBAG-005',
        'description' => 'Replacement vacuum cleaner bags',
        'unit' => 'Pack',
        'quantity' => 20,
        'minimum_stock' => 5,
        'cost_price' => 200.00,
        'supplier' => 'CleanPro Solutions'
    ],

    // Office Supplies
    [
        'name' => 'A4 Paper (White)',
        'category' => 'Office Supplies',
        'sku' => 'OS-PAPER-001',
        'description' => 'Premium white A4 copy paper',
        'unit' => 'Ream',
        'quantity' => 15,
        'minimum_stock' => 3,
        'cost_price' => 180.00,
        'supplier' => 'Office Depot Pro'
    ],
    [
        'name' => 'Ballpoint Pens (Blue)',
        'category' => 'Office Supplies',
        'sku' => 'OS-PENS-002',
        'description' => 'Blue ballpoint pens for front desk',
        'unit' => 'Box',
        'quantity' => 10,
        'minimum_stock' => 2,
        'cost_price' => 120.00,
        'supplier' => 'Office Depot Pro'
    ],
    [
        'name' => 'Stapler with Staples',
        'category' => 'Office Supplies',
        'sku' => 'OS-STAPLER-003',
        'description' => 'Heavy-duty stapler with staple refills',
        'unit' => 'Piece',
        'quantity' => 5,
        'minimum_stock' => 1,
        'cost_price' => 350.00,
        'supplier' => 'Office Depot Pro'
    ],
    [
        'name' => 'File Folders (Manila)',
        'category' => 'Office Supplies',
        'sku' => 'OS-FOLDERS-004',
        'description' => 'Manila file folders for guest records',
        'unit' => 'Box',
        'quantity' => 8,
        'minimum_stock' => 2,
        'cost_price' => 95.00,
        'supplier' => 'Office Depot Pro'
    ],

    // Maintenance
    [
        'name' => 'Light Bulbs (LED)',
        'category' => 'Maintenance',
        'sku' => 'MT-LED-001',
        'description' => 'Energy-efficient LED light bulbs',
        'unit' => 'Piece',
        'quantity' => 100,
        'minimum_stock' => 20,
        'cost_price' => 45.00,
        'supplier' => 'Electrical Supply Co.'
    ],
    [
        'name' => 'Air Filter (HVAC)',
        'category' => 'Maintenance',
        'sku' => 'MT-FILTER-002',
        'description' => 'Replacement air filters for HVAC system',
        'unit' => 'Piece',
        'quantity' => 20,
        'minimum_stock' => 5,
        'cost_price' => 250.00,
        'supplier' => 'HVAC Solutions Inc.'
    ],
    [
        'name' => 'Paint (White)',
        'category' => 'Maintenance',
        'sku' => 'MT-PAINT-003',
        'description' => 'Interior white paint for touch-ups',
        'unit' => 'Gallon',
        'quantity' => 8,
        'minimum_stock' => 2,
        'cost_price' => 450.00,
        'supplier' => 'Paint & Decor Co.'
    ],
    [
        'name' => 'Door Handles (Brass)',
        'category' => 'Maintenance',
        'sku' => 'MT-HANDLES-004',
        'description' => 'Brass door handles for guest rooms',
        'unit' => 'Piece',
        'quantity' => 25,
        'minimum_stock' => 5,
        'cost_price' => 180.00,
        'supplier' => 'Hardware Solutions'
    ]
];

echo "🌱 Starting Inventory Items Seeding Process...\n\n";

try {
    // Get database connection
    $pdo = getDatabaseConnection();
    echo "✅ Database connection established\n";

    $seeded_count = 0;
    $errors = [];

    foreach ($sample_items as $item) {
        try {
            // Get or create category
            $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ?");
            $stmt->execute([$item['category']]);
            $category_result = $stmt->fetch();

            if (!$category_result) {
                // Create category if it doesn't exist
                $stmt = $pdo->prepare("
                    INSERT INTO inventory_categories (name, description, created_at) 
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$item['category'], 'Auto-created category for seeded items']);
                $category_id = $pdo->lastInsertId();
                echo "📁 Created category: {$item['category']}\n";
            } else {
                $category_id = $category_result['id'];
            }

            // Check if item already exists (using item_name since there's no SKU column)
            $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE item_name = ?");
            $stmt->execute([$item['name']]);
            if ($stmt->fetch()) {
                echo "⚠️  Item already exists: {$item['name']}\n";
                continue;
            }

            // Create inventory item (using actual schema columns)
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

            $item_id = $pdo->lastInsertId();

            $seeded_count++;
            echo "✅ Seeded: {$item['name']} (SKU: {$item['sku']}) - Qty: {$item['quantity']} {$item['unit']}\n";

        } catch (PDOException $e) {
            $errors[] = "Error seeding {$item['name']}: " . $e->getMessage();
            echo "❌ Error seeding {$item['name']}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n🎉 Seeding Process Completed!\n";
    echo "📊 Summary:\n";
    echo "   • Items seeded: {$seeded_count}\n";
    echo "   • Errors: " . count($errors) . "\n";

    if (!empty($errors)) {
        echo "\n❌ Errors encountered:\n";
        foreach ($errors as $error) {
            echo "   • {$error}\n";
        }
    }

    // Display inventory summary
    echo "\n📈 Current Inventory Summary:\n";
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
        echo "   • {$row['category_name']}: {$row['item_count']} items (₱" . number_format($row['total_value'], 2) . ")\n";
    }

    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM inventory_items");
    $total_items = $stmt->fetch()['total_items'];

    $stmt = $pdo->query("SELECT SUM(current_stock * unit_price) as total_value FROM inventory_items");
    $total_value = $stmt->fetch()['total_value'];

    echo "\n💰 Total Inventory Value: ₱" . number_format($total_value, 2) . "\n";
    echo "📦 Total Items: {$total_items}\n";

} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Seeding process finished successfully!\n";
?>
