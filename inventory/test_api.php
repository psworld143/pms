<?php
/**
 * Test API endpoints
 * This will help us debug the API calls
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Simulate a logged-in manager user for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';

echo "<h2>API Test Results</h2>";

// Test get-inventory-stats.php
echo "<h3>Testing get-inventory-stats.php:</h3>";
$url = 'http://localhost/pms/inventory/api/get-inventory-stats.php';
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test get-inventory-items.php
echo "<h3>Testing get-inventory-items.php:</h3>";
$url = 'http://localhost/pms/inventory/api/get-inventory-items.php';
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test get-pos-products.php
echo "<h3>Testing get-pos-products.php:</h3>";
$url = 'http://localhost/pms/inventory/api/get-pos-products.php';
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<hr>";
echo "<p><a href='items.php'>← Back to Items Page</a></p>";
?>
