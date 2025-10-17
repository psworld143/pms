<?php
/**
 * Database Connection Test Script for VPS
 * Updated for Hostinger CyberPanel configuration
 */

// Include database configuration
require_once 'includes/database.local.php';

echo "<h2>VPS Database Connection Test</h2>";
echo "<pre>";

try {
    // Test connection with VPS credentials
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "✅ SUCCESS: Connected to database '" . DB_NAME . "' on " . DB_HOST . ":" . DB_PORT . "\n";

    // Test if we can query the database
    $result = $pdo->query('SELECT 1 as test')->fetch();
    if ($result && $result['test'] == 1) {
        echo "✅ SUCCESS: Query execution works\n";

        // Check for existing tables
        try {
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            echo "✅ SUCCESS: Found " . count($tables) . " tables in database\n";

            if (count($tables) > 0) {
                echo "Tables: " . implode(', ', $tables) . "\n";
            } else {
                echo "⚠️  WARNING: No tables found. You may need to run the database setup script.\n";
            }
        } catch (Exception $e) {
            echo "⚠️  WARNING: Could not check tables: " . $e->getMessage() . "\n";
        }

    } else {
        echo "❌ ERROR: Query execution failed\n";
    }

} catch(PDOException $e) {
    echo "❌ ERROR: Database connection failed\n";
    echo "Error: " . $e->getMessage() . "\n\n";

    echo "🔧 TROUBLESHOOTING:\n";
    echo "1. Verify your database credentials in includes/database.local.php\n";
    echo "2. Check if MySQL server is running on your VPS\n";
    echo "3. Ensure the database user has proper permissions\n";
    echo "4. Check VPS firewall settings\n";
    echo "5. Verify PDO MySQL extension is enabled in PHP\n\n";

    echo "📋 YOUR CURRENT CONFIGURATION:\n";
    echo "Host: " . DB_HOST . "\n";
    echo "Port: " . DB_PORT . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "User: " . DB_USER . "\n";
    echo "Password: " . (strlen(DB_PASS) > 0 ? '***configured***' : 'NOT SET') . "\n";
}

echo "</pre>";
echo "<p><a href='index.php'>← Back to PMS System</a></p>";
?>
