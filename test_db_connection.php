<?php
/**
 * Database Connection Test
 * Visit this file in your browser to test if database connection is working
 * Example: http://pms.seait.edu.ph/test_db_connection.php
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 4px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .btn { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px 0 0; }
        .btn:hover { background: #45a049; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔌 Database Connection Test</h1>";

// Test 1: Check if database.local.php exists
echo "<h2>Step 1: Configuration File Check</h2>";
$localConfigPath = __DIR__ . '/includes/database.local.php';
if (file_exists($localConfigPath)) {
    echo "<div class='success'>✅ Configuration file exists: <code>includes/database.local.php</code></div>";
} else {
    echo "<div class='error'>❌ Configuration file NOT found: <code>includes/database.local.php</code>
          <br><br><strong>Action Required:</strong> Create this file with your database credentials.</div>";
}

// Test 2: Try to include database.php
echo "<h2>Step 2: Load Database Configuration</h2>";
try {
    require_once __DIR__ . '/includes/database.php';
    echo "<div class='success'>✅ Database configuration loaded successfully</div>";
    
    // Display configuration (hide password)
    echo "<div class='info'><strong>Database Configuration:</strong><br>";
    echo "• Host: <code>" . DB_HOST . "</code><br>";
    echo "• Database: <code>" . DB_NAME . "</code><br>";
    echo "• Username: <code>" . DB_USER . "</code><br>";
    echo "• Password: <code>" . (empty(DB_PASS) ? 'EMPTY (NOT SET!)' : '••••••••') . "</code><br>";
    echo "• Port: <code>" . (defined('DB_PORT') ? DB_PORT : '3306') . "</code>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to load database configuration<br>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "</div></body></html>";
    exit;
}

// Test 3: Check if PDO is available
echo "<h2>Step 3: Check PDO MySQL Driver</h2>";
if (class_exists('PDO')) {
    $drivers = PDO::getAvailableDrivers();
    if (in_array('mysql', $drivers)) {
        echo "<div class='success'>✅ PDO MySQL driver is available</div>";
    } else {
        echo "<div class='error'>❌ PDO MySQL driver is NOT available. Available drivers: " . implode(', ', $drivers) . "</div>";
    }
} else {
    echo "<div class='error'>❌ PDO is not available. Please enable PDO extension in php.ini</div>";
}

// Test 4: Test database connection
echo "<h2>Step 4: Database Connection Test</h2>";
if (isset($pdo) && $pdo instanceof PDO) {
    echo "<div class='success'>✅ Database connection established successfully!</div>";
    
    // Test 5: Get server info
    echo "<h2>Step 5: Database Server Information</h2>";
    try {
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        $currentDb = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $currentUser = $pdo->query('SELECT CURRENT_USER()')->fetchColumn();
        $charset = $pdo->query("SHOW VARIABLES LIKE 'character_set_database'")->fetch();
        $collation = $pdo->query("SHOW VARIABLES LIKE 'collation_database'")->fetch();
        
        echo "<table>
                <tr><th>Property</th><th>Value</th></tr>
                <tr><td>MySQL Version</td><td>" . htmlspecialchars($version) . "</td></tr>
                <tr><td>Current Database</td><td>" . htmlspecialchars($currentDb) . "</td></tr>
                <tr><td>Current User</td><td>" . htmlspecialchars($currentUser) . "</td></tr>
                <tr><td>Character Set</td><td>" . htmlspecialchars($charset['Value']) . "</td></tr>
                <tr><td>Collation</td><td>" . htmlspecialchars($collation['Value']) . "</td></tr>
              </table>";
    } catch (PDOException $e) {
        echo "<div class='warning'>⚠️ Could not retrieve server information: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    // Test 6: Check tables
    echo "<h2>Step 6: Database Tables</h2>";
    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            echo "<div class='success'>✅ Found " . count($tables) . " tables in database</div>";
            echo "<div class='info'><strong>Tables:</strong><br>";
            echo "<ul style='columns: 2; margin: 10px 0;'>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul></div>";
        } else {
            echo "<div class='warning'>⚠️ No tables found in database. You may need to import your SQL schema.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Could not retrieve tables: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    // Final status
    echo "<h2>✅ Overall Status: PASSED</h2>";
    echo "<div class='success'>
            <strong>Your database connection is working correctly!</strong><br><br>
            You can now use your PMS system:
            <br><br>
            <a href='/booking/' class='btn'>Go to Booking System</a>
            <a href='/inventory/' class='btn'>Go to Inventory</a>
            <a href='/pos/' class='btn'>Go to POS</a>
          </div>";
    
} else {
    echo "<div class='error'>❌ Database connection FAILED</div>";
    echo "<h2>Troubleshooting Steps:</h2>";
    echo "<div class='warning'>
            <ol>
                <li><strong>Check your credentials in <code>includes/database.local.php</code></strong>
                    <ul>
                        <li>Make sure DB_HOST, DB_NAME, DB_USER, and DB_PASS are correct</li>
                        <li>Get these from your CyberPanel/cPanel MySQL section</li>
                    </ul>
                </li>
                <li><strong>Verify MySQL is running on your server</strong>
                    <ul>
                        <li>Contact your hosting provider if needed</li>
                    </ul>
                </li>
                <li><strong>Check database user permissions</strong>
                    <ul>
                        <li>User must have ALL PRIVILEGES on the database</li>
                        <li>Check in CyberPanel > Databases > MySQL Databases</li>
                    </ul>
                </li>
                <li><strong>Check firewall settings</strong>
                    <ul>
                        <li>Ensure MySQL port (3306) is accessible</li>
                    </ul>
                </li>
            </ol>
          </div>";
}

echo "    </div>
</body>
</html>";
?>
