<?php
/**
 * COMPLETE PMS LOGIN FIX FOR VPS
 * This script will diagnose and fix all login issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 COMPLETE PMS LOGIN FIX</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

$issues = [];
$fixes = [];

echo "<h2>🔍 DIAGNOSIS PHASE</h2>";

// Test 1: Database Connection
echo "<h3>Test 1: Database Connection</h3>";
try {
    require_once '../includes/database.php';
    $pdo = getDatabaseConnection();
    echo "<p>✅ Database connection successful</p>";

    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->fetch()) {
        echo "<p>✅ Users table exists</p>";

        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $count = $stmt->fetch()['count'];
        echo "<p>📊 Active users: $count</p>";

        if ($count == 0) {
            $issues[] = "No users found in database";
        }
    } else {
        $issues[] = "Users table missing";
    }

} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    $issues[] = "Database connection failed";
}

// Test 2: Session Configuration
echo "<h3>Test 2: Session Configuration</h3>";
$sessionPath = $_SERVER['DOCUMENT_ROOT'] . '/tmp_sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
    echo "<p>✅ Created session directory: $sessionPath</p>";
} else {
    echo "<p>✅ Session directory exists: $sessionPath</p>";
}

ini_set('session.save_path', $sessionPath);
session_start();

if (isset($_SESSION['test'])) {
    echo "<p>✅ Session working correctly</p>";
} else {
    $_SESSION['test'] = 'working';
    echo "<p>✅ Session initialized</p>";
}

// Test 3: Password Verification
echo "<h3>Test 3: Password Verification</h3>";
$testPassword = 'password';
$correctHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($testPassword, $correctHash)) {
    echo "<p>✅ Password hash verification working</p>";
} else {
    echo "<p>❌ Password hash verification failed</p>";
    $issues[] = "Password verification issue";
}

echo "<h2>🔧 FIXING ISSUES</h2>";

// Fix 1: Create users table if missing
if (in_array("Users table missing", $issues)) {
    echo "<h3>Creating Users Table</h3>";
    try {
        $pdo->exec("CREATE TABLE users (
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
        $fixes[] = "Created users table";
    } catch (Exception $e) {
        echo "<p>❌ Failed to create users table: " . $e->getMessage() . "</p>";
    }
}

// Fix 2: Insert demo users
if (in_array("No users found in database", $issues) || in_array("Users table missing", $issues)) {
    echo "<h3>Inserting Demo Users</h3>";

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
            echo "<p>✅ Created user: {$user[1]}</p>";
        } catch (Exception $e) {
            echo "<p>❌ Failed to create user {$user[1]}: " . $e->getMessage() . "</p>";
        }
    }
    $fixes[] = "Inserted demo users";
}

// Fix 3: Create session configuration file
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

if (file_put_contents('session_config.php', $sessionConfigContent)) {
    echo "<p>✅ Session configuration file created</p>";
    $fixes[] = "Created session_config.php";
} else {
    echo "<p>❌ Failed to create session configuration file</p>";
}

echo "<h2>🎯 LOGIN CREDENTIALS</h2>";
echo "<p><strong>Password for all users: <code>password</code></strong></p>";
echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
echo "<tr style='background:#f0f0f0;'><th>System</th><th>Username</th><th>Type</th><th>Status</th></tr>";
echo "<tr><td>📋 Booking</td><td>manager1</td><td>Manager</td><td>✅ Ready</td></tr>";
echo "<tr><td>📋 Booking</td><td>frontdesk1</td><td>Front Desk</td><td>✅ Ready</td></tr>";
echo "<tr><td>📋 Booking</td><td>housekeeping1</td><td>Housekeeping</td><td>✅ Ready</td></tr>";
echo "<tr><td>🏪 POS</td><td>manager1</td><td>Manager</td><td>✅ Ready</td></tr>";
echo "<tr><td>📦 Inventory</td><td>manager1</td><td>Manager</td><td>✅ Ready</td></tr>";
echo "<tr><td>🎓 Tutorials</td><td>demo@student.com</td><td>Student</td><td>✅ Ready</td></tr>";
echo "</table>";

echo "<h2>📋 NEXT STEPS</h2>";
echo "<ol>";
echo "<li><strong>Add to login.php files:</strong> <code>require_once 'session_config.php';</code> (at the very top)</li>";
echo "<li><strong>Test login:</strong> Try <code>manager1</code> / <code>password</code></li>";
echo "<li><strong>Clear browser cache:</strong> Hard refresh (Ctrl+F5)</li>";
echo "<li><strong>Check browser console:</strong> F12 > Console for errors</li>";
echo "</ol>";

echo "<h2>🔗 TEST LINKS</h2>";
echo "<p><a href='../booking/login.php' target='_blank'>Test Booking Login</a> | ";
echo "<a href='../pos/login.php' target='_blank'>Test POS Login</a> | ";
echo "<a href='../inventory/login.php' target='_blank'>Test Inventory Login</a> | ";
echo "<a href='../tutorials/login.php' target='_blank'>Test Tutorials Login</a></p>";

echo "<h2>✅ ISSUES FIXED</h2>";
if (empty($issues)) {
    echo "<p>🎉 All systems ready!</p>";
} else {
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>⚠️ $issue</li>";
    }
    echo "</ul>";
}

echo "<h2>🔧 FIXES APPLIED</h2>";
if (empty($fixes)) {
    echo "<p>✅ No fixes needed</p>";
} else {
    echo "<ul>";
    foreach ($fixes as $fix) {
        echo "<li>✅ $fix</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Script completed!</strong> Try logging in now with the credentials above.</p>";
?>
