<?php
/**
 * Debug API - Test database connection and queries
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Simulate a logged-in manager user for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';

echo "<h2>Debug API Test</h2>";

try {
    global $pdo;
    
    echo "<h3>Database Connection Test:</h3>";
    if ($pdo) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Test basic query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total inventory items: " . $result['count'] . "</p>";
        
        // Test the exact queries from the API
        echo "<h3>Testing API Queries:</h3>";
        
        // Total items
        $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM inventory_items");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total items: " . $result['total_items'] . "</p>";
        
        // In stock
        $stmt = $pdo->query("SELECT COUNT(*) as in_stock FROM inventory_items WHERE current_stock > minimum_stock");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>In stock: " . $result['in_stock'] . "</p>";
        
        // Low stock
        $stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM inventory_items WHERE current_stock <= minimum_stock AND current_stock > 0");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Low stock: " . $result['low_stock'] . "</p>";
        
        // Out of stock
        $stmt = $pdo->query("SELECT COUNT(*) as out_of_stock FROM inventory_items WHERE current_stock = 0");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Out of stock: " . $result['out_of_stock'] . "</p>";
        
        // POS products
        $stmt = $pdo->query("SELECT COUNT(*) as pos_products FROM inventory_items WHERE is_pos_product = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>POS products: " . $result['pos_products'] . "</p>";
        
        // Test sample data
        echo "<h3>Sample Inventory Items:</h3>";
        $stmt = $pdo->query("SELECT id, item_name, current_stock, minimum_stock, is_pos_product FROM inventory_items LIMIT 5");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Current Stock</th><th>Min Stock</th><th>POS Product</th></tr>";
        foreach ($items as $item) {
            echo "<tr>";
            echo "<td>" . $item['id'] . "</td>";
            echo "<td>" . $item['item_name'] . "</td>";
            echo "<td>" . $item['current_stock'] . "</td>";
            echo "<td>" . $item['minimum_stock'] . "</td>";
            echo "<td>" . ($item['is_pos_product'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='items.php'>← Back to Items Page</a></p>";
?>
