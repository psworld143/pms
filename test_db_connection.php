<?php
/**
 * Database Connection Test for VPS
 * Test database connection with current configuration
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>VPS Database Connection Test</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

echo "<h2>Current Database Configuration</h2>";
echo "<p>Check your <code>includes/database.local.php</code> file to ensure these values are correct for your VPS:</p>";

$configs = [
    'DB_HOST' => DB_HOST ?? 'Not defined',
    'DB_NAME' => DB_NAME ?? 'Not defined',
    'DB_USER' => DB_USER ?? 'Not defined',
    'DB_PASS' => DB_PASS ? '***SET***' : '***EMPTY***',
    'DB_PORT' => DB_PORT ?? 'Not defined'
];

echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
echo "<tr style='background:#f0f0f0;'><th>Setting</th><th>Value</th><th>Status</th></tr>";
foreach ($configs as $key => $value) {
    $status = ($key === 'DB_PASS' && $value === '***SET***') || ($key !== 'DB_PASS' && $value !== 'Not defined') ? '✅ OK' : '❌ Check';
    echo "<tr><td>$key</td><td>$value</td><td>$status</td></tr>";
}
echo "</table>";

echo "<h2>Testing Database Connection</h2>";

try {
    // Include database configuration
    require_once '../includes/database.php';

    echo "<p>✅ Database configuration loaded successfully</p>";

    // Test connection
    $pdo = getDatabaseConnection();
    echo "<p>✅ Database connection established</p>";

    // Test database exists and is accessible
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $dbInfo = $stmt->fetch();
    echo "<p>✅ Connected to database: <strong>" . $dbInfo['db_name'] . "</strong></p>";

    // Check if users table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (!empty($tables)) {
        echo "<p>✅ Users table exists</p>";

        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "<p>✅ Found $count users in database</p>";

        // Show sample users
        $stmt = $pdo->query("SELECT username, name, role FROM users WHERE username IN ('manager1', 'frontdesk1', 'housekeeping1') LIMIT 3");
        $users = $stmt->fetchAll();

        if ($users) {
            echo "<h3>Sample Users:</h3><ul>";
            foreach ($users as $user) {
                echo "<li>{$user['username']} ({$user['name']}) - {$user['role']}</li>";
            }
            echo "</ul>";
        }

    } else {
        echo "<p>❌ Users table does not exist</p>";
        echo "<p>You need to create the users table first. Run the database schema SQL.</p>";
    }

} catch (Exception $e) {
    echo "<div style='color:red; font-weight:bold;'>❌ Database Connection Failed</div>";
    echo "<p>Error: " . $e->getMessage() . "</p>";

    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Check your credentials in <code>includes/database.local.php</code></strong></li>";
    echo "<li>Verify your database exists in CyberPanel > Databases</li>";
    echo "<li>Ensure the database user has correct permissions</li>";
    echo "<li>Test the connection manually in CyberPanel > phpMyAdmin</li>";
    echo "<li>Check if MySQL service is running on your VPS</li>";
    echo "</ol>";

    echo "<h3>Common VPS Database Settings:</h3>";
    echo "<ul>";
    echo "<li><strong>Host:</strong> localhost (usually)</li>";
    echo "<li><strong>Port:</strong> 3306 (standard)</li>";
    echo "<li><strong>Database:</strong> Check CyberPanel > Databases > List Databases</li>";
    echo "<li><strong>Username:</strong> Check CyberPanel > Databases > Users</li>";
    echo "<li><strong>Password:</strong> Set when you created the database user</li>";
    echo "</ul>";
}

// Show database.local.php contents for reference
echo "<h2>Current database.local.php Contents</h2>";
$localFile = '../includes/database.local.php';
if (file_exists($localFile)) {
    $content = file_get_contents($localFile);
    echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "<p style='color:red;'>database.local.php file not found!</p>";
}

echo "<hr>";
echo "<p><a href='../'>← Back to PMS System</a></p>";
?>
