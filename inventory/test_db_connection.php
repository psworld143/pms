<?php
/**
 * Test Database Connection
 * This will help us understand what database is actually being used
 */

// Start session
session_start();

// Include database configuration
require_once '../includes/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    global $pdo;
    
    // Test basic connection
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Get current database name
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $db_name = $stmt->fetch()['db_name'];
    echo "<p><strong>Current Database:</strong> " . $db_name . "</p>";
    
    // Check if inventory_items table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_items'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ inventory_items table exists</p>";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE inventory_items");
        $columns = $stmt->fetchAll();
        echo "<h3>inventory_items table structure:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
        }
        echo "</ul>";
        
        // Get sample data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
        $count = $stmt->fetch()['count'];
        echo "<p><strong>Total inventory items:</strong> " . $count . "</p>";
        
    } else {
        echo "<p style='color: red;'>✗ inventory_items table does not exist</p>";
    }
    
    // Check if inventory_categories table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_categories'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ inventory_categories table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ inventory_categories table does not exist</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Inventory Dashboard</a></p>";
?>
