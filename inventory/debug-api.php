<?php
// Debug API to see what's happening
session_start();

echo "<h1>API Debug</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>";

// Test database connection
try {
    require_once '../includes/database.php';
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Database query successful: " . $result['count'] . " items found</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test the API directly
echo "<h2>Testing API Directly</h2>";
try {
    ob_start();
    require_once 'api/get-inventory-items.php';
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    if ($data && isset($data['success'])) {
        echo "<p style='color: green;'>✅ API working: " . count($data['inventory_items']) . " items</p>";
    } else {
        echo "<p style='color: red;'>❌ API error: " . substr($output, 0, 200) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ API exception: " . $e->getMessage() . "</p>";
}
?>
