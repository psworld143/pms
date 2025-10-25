<?php
/**
 * Run Database Migration
 * Hotel PMS Training System - Inventory Module
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$dbname = 'pms_hotel';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Database Migration Script</h1>";
    echo "<p>Starting migration...</p>";
    
    // Read the migration file
    $migration_sql = file_get_contents('database/corrected_schema_fix.sql');
    
    if (!$migration_sql) {
        throw new Exception("Could not read migration file");
    }
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $migration_sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $success_count++;
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
        } catch (Exception $e) {
            $error_count++;
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
            echo "<p style='color: gray;'>Statement: " . substr($statement, 0, 100) . "...</p>";
        }
    }
    
    echo "<h2>Migration Complete!</h2>";
    echo "<p style='color: green;'>Successful statements: $success_count</p>";
    echo "<p style='color: red;'>Failed statements: $error_count</p>";
    
    if ($error_count == 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ All migrations completed successfully!</p>";
        echo "<p><a href='test_inventory_system.php'>Test the inventory system</a></p>";
        echo "<p><a href='items.php'>Go to inventory items</a></p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Some migrations failed, but the system should still work.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Migration Failed</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection settings.</p>";
}
?>
