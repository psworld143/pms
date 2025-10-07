<?php
/**
 * Quick Database Configuration Test
 * This will show us exactly what constants are defined
 */

// Include database configuration
require_once 'includes/database.php';

echo "<h2>Database Configuration Test</h2>";
echo "<pre>";

echo "=== Constants Check ===\n";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "\n";
echo "DB_PASS: " . (defined('DB_PASS') ? (strlen(DB_PASS) > 0 ? 'SET' : 'EMPTY') : 'NOT DEFINED') . "\n";
echo "DB_PORT: " . (defined('DB_PORT') ? DB_PORT : 'NOT DEFINED') . "\n";

echo "\n=== File Check ===\n";
$localFile = __DIR__ . '/includes/database.local.php';
echo "database.local.php exists: " . (file_exists($localFile) ? 'YES' : 'NO') . "\n";
if (file_exists($localFile)) {
    echo "File path: $localFile\n";
    echo "File readable: " . (is_readable($localFile) ? 'YES' : 'NO') . "\n";
    echo "File size: " . filesize($localFile) . " bytes\n";
}

echo "</pre>";
echo "<p><a href='index.php'>← Back to PMS System</a></p>";
?>
