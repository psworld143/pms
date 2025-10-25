<?php
/**
 * Login Test Script
 * Test the actual login process for each system
 */

require_once 'includes/database.php';

echo "<h1>Login System Test</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

$testCredentials = [
    'Booking System' => [
        ['username' => 'manager1', 'password' => 'password'],
        ['username' => 'frontdesk1', 'password' => 'password'],
        ['username' => 'housekeeping1', 'password' => 'password'],
        ['username' => 'sarah.johnson', 'password' => 'password'],
    ],
    'Tutorials System' => [
        ['email' => 'demo@student.com', 'password' => 'password'],
        ['email' => 'student1@demo.com', 'password' => 'password'],
    ]
];

foreach ($testCredentials as $systemName => $credentials) {
    echo "<h2>$systemName Login Tests</h2>";

    foreach ($credentials as $cred) {
        try {
            if (isset($cred['email'])) {
                // Tutorials system uses email
                $stmt = $pdo->prepare("SELECT id, name, username, password, role FROM users WHERE email = ? AND role = 'student' AND is_active = 1");
                $stmt->execute([$cred['email']]);
                $field = 'email';
                $value = $cred['email'];
            } else {
                // Other systems use username
                $stmt = $pdo->prepare("SELECT id, name, username, password, role FROM users WHERE username = ? AND is_active = 1");
                $stmt->execute([$cred['username']]);
                $field = 'username';
                $value = $cred['username'];
            }

            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($cred['password'], $user['password'])) {
                    echo "<span class='success'>✅ $field: $value - LOGIN SUCCESS (Role: {$user['role']})</span><br>";
                } else {
                    echo "<span class='error'>❌ $field: $value - PASSWORD FAILED</span><br>";
                    echo "&nbsp;&nbsp;&nbsp;User exists but password doesn't match<br>";
                    echo "&nbsp;&nbsp;&nbsp;Stored hash: " . substr($user['password'], 0, 20) . "...<br>";
                    echo "&nbsp;&nbsp;&nbsp;Expected hash: " . password_hash($cred['password'], PASSWORD_DEFAULT) . "<br>";
                }
            } else {
                echo "<span class='error'>❌ $field: $value - USER NOT FOUND</span><br>";
            }

        } catch (Exception $e) {
            echo "<span class='error'>❌ $field: $value - ERROR: " . $e->getMessage() . "</span><br>";
        }
    }
    echo "<br>";
}

// Test database connection and user count
echo "<h2>Database Status</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $count = $stmt->fetch()['count'];
    echo "📊 Active users in database: $count<br>";

    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role");
    $roles = $stmt->fetchAll();

    echo "👥 Users by role:<br>";
    foreach ($roles as $role) {
        echo "&nbsp;&nbsp;&nbsp;{$role['role']}: {$role['count']}<br>";
    }

} catch (Exception $e) {
    echo "<span class='error'>❌ Cannot query users table: " . $e->getMessage() . "</span><br>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Run <a href='diagnose_login.php'>diagnose_login.php</a> to check system configuration</li>";
echo "<li>Run <a href='verify_users.php'>verify_users.php</a> to check user data</li>";
echo "<li>Check browser console (F12) for JavaScript errors during login</li>";
echo "<li>Clear browser cookies if sessions aren't working</li>";
echo "</ol>";
?>
