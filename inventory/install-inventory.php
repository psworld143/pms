<?php
/**
 * Inventory System Installation Script
 * Hotel PMS Training System for Students
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Inventory System Installation</h1>";
echo "<p>Installing inventory management system for Hotel PMS Training...</p>";

try {
    $inventory_db = new InventoryDatabase();
    $pdo = $inventory_db->getConnection();
    
    echo "<h2>Step 1: Creating Database Tables</h2>";
    
    // Read and execute the schema file
    $schema_file = __DIR__ . '/database/inventory_schema.sql';
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
                } catch (PDOException $e) {
                    echo "<p style='color: orange;'>⚠ Warning: " . $e->getMessage() . "</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Schema file not found: $schema_file</p>";
    }
    
    echo "<h2>Step 2: Verifying Installation</h2>";
    
    // Check if tables exist
    $tables = ['inventory_categories', 'inventory_items', 'inventory_transactions', 'inventory_requests', 'inventory_request_items', 'inventory_suppliers', 'inventory_training_scenarios', 'inventory_training_progress'];
    
    $all_tables_exist = true;
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✓ Table '$table' exists</p>";
            } else {
                echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
                $all_tables_exist = false;
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error checking table '$table': " . $e->getMessage() . "</p>";
            $all_tables_exist = false;
        }
    }
    
    echo "<h2>Step 3: Testing Database Functions</h2>";
    
    // Test inventory database functions
    if ($inventory_db->checkInventoryTables()) {
        echo "<p style='color: green;'>✓ Inventory tables check passed</p>";
    } else {
        echo "<p style='color: red;'>✗ Inventory tables check failed</p>";
    }
    
    // Test getting inventory items
    $items = $inventory_db->getInventoryItems();
    echo "<p style='color: green;'>✓ Retrieved " . count($items) . " inventory items</p>";
    
    // Test getting low stock items
    $low_stock = $inventory_db->getLowStockItems();
    echo "<p style='color: green;'>✓ Retrieved " . count($low_stock) . " low stock items</p>";
    
    echo "<h2>Step 4: Installation Summary</h2>";
    
    if ($all_tables_exist) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='margin: 0 0 10px 0;'>✅ Installation Successful!</h3>";
        echo "<p style='margin: 0;'>The inventory management system has been successfully installed and is ready for use.</p>";
        echo "</div>";
        
        echo "<h3>Next Steps:</h3>";
        echo "<ul>";
        echo "<li><a href='index.php'>Access the Inventory Dashboard</a></li>";
        echo "<li><a href='items.php'>Manage Inventory Items</a></li>";
        echo "<li><a href='training.php'>Start Training Scenarios</a></li>";
        echo "<li><a href='reports.php'>View Reports and Analytics</a></li>";
        echo "</ul>";
        
        echo "<h3>Features Available:</h3>";
        echo "<ul>";
        echo "<li>📦 Inventory Item Management</li>";
        echo "<li>📊 Transaction Tracking</li>";
        echo "<li>📋 Request Management</li>";
        echo "<li>🎓 Training Scenarios</li>";
        echo "<li>📈 Reports and Analytics</li>";
        echo "<li>⚠️ Low Stock Alerts</li>";
        echo "<li>🏷️ Category Management</li>";
        echo "<li>👥 Multi-user Support</li>";
        echo "</ul>";
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='margin: 0 0 10px 0;'>❌ Installation Failed!</h3>";
        echo "<p style='margin: 0;'>Some tables were not created successfully. Please check the error messages above and try again.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='margin: 0 0 10px 0;'>❌ Installation Error!</h3>";
    echo "<p style='margin: 0;'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Installation completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
