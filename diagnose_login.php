<?php
/**
 * VPS Login Diagnostic Script
 * This script will help identify why login isn't working on your VPS
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>VPS Login Diagnostic Tool</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .warning{color:orange;}</style>";

try {
    // Test 1: Check if database.php file exists and is readable
    echo "<h2>Test 1: Database Configuration</h2>";
    $dbConfigPath = '../includes/database.php';

    if (file_exists($dbConfigPath)) {
        echo "✅ Database config file exists<br>";
        echo "📍 Path: " . realpath($dbConfigPath) . "<br>";

        // Show contents of database.php for debugging
        $content = file_get_contents($dbConfigPath);
        echo "<details><summary>📄 Database config contents</summary><pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre></details>";
    } else {
        echo "❌ Database config file NOT found at: $dbConfigPath<br>";
        echo "🔍 Searched in: " . realpath('../includes/') . "<br>";
    }

    // Test 2: Try to connect to database
    echo "<h2>Test 2: Database Connection</h2>";

    if (file_exists($dbConfigPath)) {
        require_once $dbConfigPath;

        try {
            // Check if PDO is available
            if (class_exists('PDO')) {
                echo "✅ PDO is available<br>";

                // Try to get database connection
                $pdo = getDatabaseConnection();
                echo "✅ Database connection successful<br>";

                // Test database query
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                $result = $stmt->fetch();
                echo "✅ Users table accessible<br>";
                echo "📊 Total users in database: " . $result['count'] . "<br>";

                // Show sample users
                $stmt = $pdo->query("SELECT id, username, name, role FROM users WHERE username IN ('manager1', 'frontdesk1', 'housekeeping1', 'sarah.johnson', 'demo_student') LIMIT 5");
                $users = $stmt->fetchAll();

                if ($users) {
                    echo "<h3>Sample Users Found:</h3><ul>";
                    foreach ($users as $user) {
                        echo "<li>ID: {$user['id']} | Username: {$user['username']} | Name: {$user['name']} | Role: {$user['role']}</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "⚠️ No demo users found in database<br>";
                }

            } else {
                echo "❌ PDO not available<br>";
            }

        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
        }
    }

    // Test 3: Test password hashing
    echo "<h2>Test 3: Password Hashing</h2>";
    $testPassword = 'password';
    $testHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    if (password_verify($testPassword, $testHash)) {
        echo "✅ Password hash verification works<br>";
        echo "🔐 Test password: '$testPassword' matches hash: " . substr($testHash, 0, 20) . "...<br>";
    } else {
        echo "❌ Password hash verification failed<br>";
        echo "🔐 Test password: '$testPassword' does NOT match hash<br>";
        echo "🔄 Generating new hash for 'password': " . password_hash($testPassword, PASSWORD_DEFAULT) . "<br>";
    }

    // Test 4: Session configuration
    echo "<h2>Test 4: Session Configuration</h2>";
    // Fix session issues before starting
$sessionPath = __DIR__ . '/tmp_sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}
ini_set('session.save_path', $sessionPath);
    $_SESSION['test_session'] = 'working';

    if (isset($_SESSION['test_session'])) {
        echo "✅ Session is working<br>";
        echo "📝 Session ID: " . session_id() . "<br>";
        echo "🍪 Session cookie set: " . (isset($_COOKIE[session_name()]) ? 'Yes' : 'No') . "<br>";
    } else {
        echo "❌ Session not working<br>";
    }

    // Test 5: PHP configuration
    echo "<h2>Test 5: PHP Configuration</h2>";
    echo "🐘 PHP Version: " . phpversion() . "<br>";
    echo "📂 Current working directory: " . getcwd() . "<br>";
    echo "🌐 Server software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
    echo "🔗 Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "<br>";

    // Test 6: File permissions
    echo "<h2>Test 6: File Permissions</h2>";
    $testFiles = [
        '../includes/database.php',
        'index.php',
        '../index.php'
    ];

    foreach ($testFiles as $file) {
        if (file_exists($file)) {
            $perms = substr(sprintf('%o', fileperms($file)), -4);
            $readable = is_readable($file) ? 'Readable' : 'Not Readable';
            $writable = is_writable($file) ? 'Writable' : 'Not Writable';
            echo "📄 $file: Permissions: $perms | $readable | $writable<br>";
        } else {
            echo "❌ $file: File not found<br>";
        }
    }

} catch (Exception $e) {
    echo "<div class='error'>Fatal Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h2>💡 Troubleshooting Tips:</h2>";
echo "<ul>";
echo "<li>If database connection fails, check your database.local.php file</li>";
echo "<li>If no users found, make sure you ran the SQL INSERT statements</li>";
echo "<li>If password verification fails, the hash might be incorrect</li>";
echo "<li>If sessions don't work, check PHP session configuration</li>";
echo "<li>Check browser developer tools (F12) for JavaScript errors</li>";
echo "<li>Clear browser cookies and cache if sessions aren't working</li>";
echo "</ul>";

echo "<p><a href='../booking/login.php'>Test Booking System Login</a> | ";
echo "<a href='../pos/login.php'>Test POS System Login</a> | ";
echo "<a href='../inventory/login.php'>Test Inventory System Login</a> | ";
echo "<a href='../tutorials/login.php'>Test Tutorials Login</a></p>";
?>
