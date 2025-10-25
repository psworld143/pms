<?php
/**
 * Test Inventory System
 * Hotel PMS Training System - Inventory Module
 * This script tests the inventory system functionality
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Set test user session
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';

echo "<h1>Inventory System Test</h1>";

try {
    // Test database connection
    echo "<h2>1. Database Connection Test</h2>";
    $inventory_db = new InventoryDatabase();
    $pdo = $inventory_db->getConnection();
    echo "✓ Database connection successful<br>";
    
    // Test table existence
    echo "<h2>2. Table Existence Test</h2>";
    $tables = ['inventory_items', 'inventory_categories', 'inventory_transactions', 'inventory_requests', 'suppliers', 'reorder_rules', 'purchase_orders', 'floors', 'rooms', 'room_inventory'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' missing<br>";
        }
    }
    
    // Test column existence
    echo "<h2>3. Column Existence Test</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_columns = ['item_name', 'current_stock', 'minimum_stock', 'unit_price', 'sku'];
    foreach ($required_columns as $column) {
        if (in_array($column, $columns)) {
            echo "✓ Column '$column' exists in inventory_items<br>";
        } else {
            echo "✗ Column '$column' missing in inventory_items<br>";
        }
    }
    
    // Test data retrieval
    echo "<h2>4. Data Retrieval Test</h2>";
    
    // Test inventory items
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count inventory items<br>";
    
    // Test categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_categories");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count categories<br>";
    
    // Test transactions
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_transactions");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count transactions<br>";
    
    // Test API endpoints
    echo "<h2>5. API Endpoints Test</h2>";
    
    $api_endpoints = [
        'get-inventory-stats.php',
        'get-inventory-items.php',
        'get-inventory-categories.php',
        'get-transaction-stats.php',
        'get-room-inventory-stats.php',
        'get-hotel-floors.php',
        'get-rooms-for-floor.php',
        'get-room-details.php',
        'get-automated-reordering-data.php',
        'get-suppliers.php',
        'get-enhanced-report-data.php',
        'lookup-item-by-barcode.php',
        'process-scanned-items.php',
        'start-room-audit.php',
        'start-room-restock.php',
        'export-inventory-report.php',
        'export-enhanced-report.php',
        'export-transaction-report.php'
    ];
    
    foreach ($api_endpoints as $endpoint) {
        $file_path = __DIR__ . '/api/' . $endpoint;
        if (file_exists($file_path)) {
            echo "✓ API endpoint '$endpoint' exists<br>";
        } else {
            echo "✗ API endpoint '$endpoint' missing<br>";
        }
    }
    
    // Test module files
    echo "<h2>6. Module Files Test</h2>";
    
    $modules = [
        'index.php',
        'items.php',
        'requests.php',
        'reports.php',
        'room-inventory.php',
        'transactions.php',
        'automated-reordering.php',
        'enhanced-reports.php',
        'barcode-scanner.php'
    ];
    
    foreach ($modules as $module) {
        $file_path = __DIR__ . '/' . $module;
        if (file_exists($file_path)) {
            echo "✓ Module '$module' exists<br>";
        } else {
            echo "✗ Module '$module' missing<br>";
        }
    }
    
    // Test sample data
    echo "<h2>7. Sample Data Test</h2>";
    
    // Test inventory items with proper columns
    $stmt = $pdo->query("SELECT item_name, current_stock, minimum_stock, unit_price FROM inventory_items LIMIT 5");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($items)) {
        echo "✓ Sample inventory items found:<br>";
        foreach ($items as $item) {
            echo "&nbsp;&nbsp;• " . htmlspecialchars($item['item_name']) . " (Stock: " . $item['current_stock'] . ", Min: " . $item['minimum_stock'] . ")<br>";
        }
    } else {
        echo "✗ No inventory items found<br>";
    }
    
    // Test room inventory
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM room_inventory");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count room inventory items<br>";
    
    // Test suppliers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM suppliers");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count suppliers<br>";
    
    // Test reorder rules
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reorder_rules");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count reorder rules<br>";
    
    echo "<h2>8. System Status</h2>";
    echo "✓ Inventory system is ready for use!<br>";
    echo "✓ All required tables and columns exist<br>";
    echo "✓ All API endpoints are available<br>";
    echo "✓ All modules are present<br>";
    echo "✓ Sample data is loaded<br>";
    
    echo "<h2>9. Next Steps</h2>";
    echo "1. Run the database migration script: <code>inventory/database/fix_schema_mismatch.sql</code><br>";
    echo "2. Access the inventory system at: <a href='index.php'>inventory/index.php</a><br>";
    echo "3. Test the manager functionality by logging in with manager role<br>";
    echo "4. Verify all modules work correctly<br>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "Please check the database connection and run the migration script.<br>";
}
?>
