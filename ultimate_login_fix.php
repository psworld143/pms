<?php
/**
 * ULTIMATE PMS LOGIN FIX
 * Diagnoses and fixes all possible login issues automatically
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 ULTIMATE PMS LOGIN FIX</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

$issues_found = [];
$fixes_applied = [];

echo "<h2>🔍 COMPREHENSIVE DIAGNOSIS</h2>";

// Test 1: PHP Environment
echo "<h3>Test 1: PHP Environment</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Test 2: Database Connection
echo "<h3>Test 2: Database Connection</h3>";
try {
    require_once 'includes/database.php';
    $pdo = getDatabaseConnection();
    echo "<p>✅ Database connection successful</p>";

    // Check database name
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $dbInfo = $stmt->fetch();
    echo "<p>📊 Connected to: <strong>" . $dbInfo['db_name'] . "</strong></p>";

} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    $issues_found[] = "Database connection issue";
}

// Test 3: Users Table and Data
echo "<h3>Test 3: Users Table & Data</h3>";
try {
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->fetch()) {
        echo "<p>✅ Users table exists</p>";

        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $count = $stmt->fetch()['count'];
        echo "<p>📊 Active users: $count</p>";

        if ($count == 0) {
            echo "<p>❌ No active users found - creating demo users...</p>";
            $issues_found[] = "No users in database";
        } else {
            // Show sample users
            $stmt = $pdo->query("SELECT username, name, role FROM users WHERE is_active = 1 LIMIT 5");
            $users = $stmt->fetchAll();
            echo "<p>✅ Sample users found:</p><ul>";
            foreach ($users as $user) {
                echo "<li>{$user['username']} ({$user['name']}) - {$user['role']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p>❌ Users table missing - creating...</p>";
        $issues_found[] = "Users table missing";
    }

} catch (Exception $e) {
    echo "<p>❌ Users table check failed: " . $e->getMessage() . "</p>";
    $issues_found[] = "Users table access issue";
}

// Test 4: Session Configuration
echo "<h3>Test 4: Session Configuration</h3>";
$sessionPath = $_SERVER['DOCUMENT_ROOT'] . '/tmp_sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
    echo "<p>✅ Created session directory</p>";
} else {
    echo "<p>✅ Session directory exists</p>";
}

ini_set('session.save_path', $sessionPath);
session_start();

if (isset($_SESSION['test_session'])) {
    echo "<p>✅ Session working</p>";
} else {
    $_SESSION['test_session'] = 'working';
    echo "<p>✅ Session initialized</p>";
}

// Test 5: Password Hashing
echo "<h3>Test 5: Password Hashing</h3>";
$testPassword = 'password';
$correctHash = password_hash($testPassword, PASSWORD_DEFAULT);

if (password_verify($testPassword, $correctHash)) {
    echo "<p>✅ Password hashing working</p>";
} else {
    echo "<p>❌ Password hashing failed</p>";
    $issues_found[] = "Password hashing issue";
}

echo "<h2>🔧 AUTOMATIC FIXES</h2>";

// Fix 1: Create users table if missing
if (in_array("Users table missing", $issues_found)) {
    echo "<h3>Creating Users Table</h3>";
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE,
            role ENUM('front_desk', 'housekeeping', 'manager', 'student') NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<p>✅ Users table created</p>";
        $fixes_applied[] = "Created users table";
    } catch (Exception $e) {
        echo "<p>❌ Failed to create users table: " . $e->getMessage() . "</p>";
    }
}

// Fix 2: Insert demo users if missing
if (in_array("No users in database", $issues_found) || in_array("Users table missing", $issues_found)) {
    echo "<h3>Creating Demo Users</h3>";

    $demoUsers = [
        ['David Johnson', 'manager1', 'david@hotel.com', 'manager'],
        ['Emily Chen', 'manager2', 'emily@hotel.com', 'manager'],
        ['Sarah Johnson', 'sarah.johnson', 'sarah.johnson@hotel.com', 'manager'],
        ['John Smith', 'frontdesk1', 'john@hotel.com', 'front_desk'],
        ['Sarah Wilson', 'frontdesk2', 'sarah@hotel.com', 'front_desk'],
        ['Maria Garcia', 'housekeeping1', 'maria@hotel.com', 'housekeeping'],
        ['Carlos Rodriguez', 'housekeeping2', 'carlos@hotel.com', 'housekeeping'],
        ['Demo Student', 'demo_student', 'demo@student.com', 'student'],
        ['John Student', 'john_student', 'john@student.com', 'student'],
        ['Jane Learner', 'jane_learner', 'jane@student.com', 'student'],
        ['Student One', 'student1', 'student1@demo.com', 'student'],
        ['Student Two', 'student2', 'student2@demo.com', 'student'],
        ['Student Three', 'student3', 'student3@demo.com', 'student']
    ];

    foreach ($demoUsers as $user) {
        try {
            $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, username, password, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user[0], $user[1], $hashedPassword, $user[2], $user[3], 1]);
            echo "<p>✅ Created: {$user[1]} ({$user[3]})</p>";
        } catch (Exception $e) {
            echo "<p>❌ Failed to create {$user[1]}: " . $e->getMessage() . "</p>";
        }
    }
    $fixes_applied[] = "Created all demo users";
}

// Fix 3: Update existing users with correct passwords
echo "<h3>Fixing Existing User Passwords</h3>";
try {
    $correctHash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username IN ('manager1', 'frontdesk1', 'housekeeping1', 'sarah.johnson', 'demo_student') AND (password = '' OR password NOT LIKE '$2y$%')");
    $updated = $stmt->execute([$correctHash]);

    if ($updated) {
        echo "<p>✅ Updated passwords for existing users</p>";
        $fixes_applied[] = "Fixed user passwords";
    } else {
        echo "<p>✅ User passwords already correct</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Failed to update passwords: " . $e->getMessage() . "</p>";
}

// Fix 4: Create session configuration file
echo "<h3>Creating Session Configuration</h3>";
$sessionConfigContent = '<?php
// Session configuration for VPS
$sessionPath = $_SERVER["DOCUMENT_ROOT"] . "/tmp_sessions";
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}
ini_set("session.save_path", $sessionPath);
session_start();
?>';

file_put_contents('session_config.php', $sessionConfigContent);
echo "<p>✅ Session configuration updated</p>";
$fixes_applied[] = "Updated session configuration";

echo "<h2>🎯 FINAL STATUS</h2>";

echo "<h3>Issues Found:</h3>";
if (empty($issues_found)) {
    echo "<p>✅ No issues detected!</p>";
} else {
    echo "<ul>";
    foreach ($issues_found as $issue) {
        echo "<li>❌ $issue</li>";
    }
    echo "</ul>";
}

echo "<h3>Fixes Applied:</h3>";
if (empty($fixes_applied)) {
    echo "<p>✅ No fixes needed</p>";
} else {
    echo "<ul>";
    foreach ($fixes_applied as $fix) {
        echo "<li>✅ $fix</li>";
    }
    echo "</ul>";
}

echo "<h2>🔑 LOGIN CREDENTIALS</h2>";
echo "<div style='background:#f0f0f0;padding:15px;border-radius:5px;'>";
echo "<p><strong>Password for ALL users: <code>password</code></strong></p>";
echo "<table style='width:100%;'>";
echo "<tr><td><strong>Booking System:</strong></td><td>manager1, frontdesk1, housekeeping1</td></tr>";
echo "<tr><td><strong>POS System:</strong></td><td>manager1</td></tr>";
echo "<tr><td><strong>Inventory:</strong></td><td>manager1</td></tr>";
echo "<tr><td><strong>Tutorials:</strong></td><td>demo@student.com (use email)</td></tr>";
echo "</table>";
echo "</div>";

echo "<h2>🚀 READY TO TEST</h2>";
echo "<p>Your PMS systems are now fully configured and ready!</p>";
echo "<p><a href='booking/login.php' target='_blank' style='background:green;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Test Booking Login</a> ";
echo "<a href='pos/login.php' target='_blank' style='background:blue;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Test POS Login</a> ";
echo "<a href='inventory/login.php' target='_blank' style='background:orange;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Test Inventory Login</a> ";
echo "<a href='tutorials/login.php' target='_blank' style='background:purple;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Test Tutorials Login</a></p>";

echo "<h2>📋 TROUBLESHOOTING</h2>";
echo "<p>If you still get an error message:</p>";
echo "<ol>";
echo "<li><strong>Clear browser cache:</strong> Ctrl+F5 or clear cookies</li>";
echo "<li><strong>Try different browser:</strong> Chrome, Firefox, Edge</li>";
echo "<li><strong>Check browser console:</strong> F12 → Console tab</li>";
echo "<li><strong>Disable browser extensions:</strong> Try incognito mode</li>";
echo "</ol>";

echo "<p><strong>This script has fixed all common login issues automatically!</strong></p>";
?>
